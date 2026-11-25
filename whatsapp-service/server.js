const express = require('express');
const cors = require('cors');
const qrcode = require('qrcode');
const fs = require('fs');
const path = require('path');
const pino = require('pino');
const {
    default: makeWASocket,
    useMultiFileAuthState,
    DisconnectReason,
    fetchLatestBaileysVersion,
    makeCacheableSignalKeyStore,
    delay
} = require('@whiskeysockets/baileys');

// Prevent any unhandled error from crashing the Node.js process
process.on('uncaughtException', (err) => {
    console.error('[Nagah WhatsApp Uncaught Exception]', err.message || err);
});

process.on('unhandledRejection', (reason) => {
    console.error('[Nagah WhatsApp Unhandled Rejection]', reason);
});

const app = express();
app.use(cors());
app.use(express.json());

const PORT = process.env.PORT || 3002;
const AUTH_ROOT = path.join(__dirname, 'auth_info');
const SESSIONS_ROOT = path.join(AUTH_ROOT, 'sessions');

if (!fs.existsSync(AUTH_ROOT)) {
    fs.mkdirSync(AUTH_ROOT, { recursive: true });
}
if (!fs.existsSync(SESSIONS_ROOT)) {
    fs.mkdirSync(SESSIONS_ROOT, { recursive: true });
}

const logger = pino({ level: 'silent' });

/**
 * Multi-Session Store: Map<sessionId, SessionState>
 * - 'default' uses AUTH_ROOT directly (100% backwards-compatible with any existing session)
 * - Every other session uses AUTH_ROOT/sessions/<sessionId>
 */
const sessions = new Map();

function sanitizeSessionId(rawId) {
    const cleaned = String(rawId || 'default').trim().replace(/[^a-zA-Z0-9_-]/g, '');
    return cleaned || 'default';
}

function getSessionAuthDir(sessionId) {
    const id = sanitizeSessionId(sessionId);
    if (id === 'default') {
        return AUTH_ROOT;
    }
    return path.join(SESSIONS_ROOT, id);
}

function createEmptySessionState(sessionId) {
    return {
        id: sessionId,
        authDir: getSessionAuthDir(sessionId),
        sock: null,
        currentQr: null,
        currentPairingCode: null,
        connectionStatus: 'initializing', // initializing, waiting_for_scan, reconnecting, connected, disconnected
        connectedPhone: null,
        connectedName: null,
        isInitializing: false,
        reconnectAttempts: 0,
        reconnectTimer: null,
    };
}

function getOrCreateSessionState(rawId) {
    const id = sanitizeSessionId(rawId);
    if (!sessions.has(id)) {
        const state = createEmptySessionState(id);
        checkExistingCreds(state);
        sessions.set(id, state);
    }
    return sessions.get(id);
}

function checkExistingCreds(session) {
    try {
        const credsPath = path.join(session.authDir, 'creds.json');
        if (fs.existsSync(credsPath)) {
            const raw = fs.readFileSync(credsPath, 'utf8');
            const parsed = JSON.parse(raw);
            if (parsed && parsed.me && parsed.me.id) {
                const rawId = parsed.me.id;
                session.connectedPhone = rawId.split(':')[0] || rawId.split('@')[0];
                session.connectedName = parsed.me.name || 'Nagah Racing WhatsApp';
                return true;
            }
        }
    } catch (e) {
        console.error(`[Nagah WhatsApp][${session.id}] Error reading creds.json:`, e.message);
    }
    return false;
}

function clearAuthFiles(session) {
    try {
        if (session.id === 'default') {
            // Preserve the 'sessions' subdirectory when clearing default auth_info
            if (fs.existsSync(AUTH_ROOT)) {
                for (const entry of fs.readdirSync(AUTH_ROOT)) {
                    if (entry === 'sessions') continue;
                    fs.rmSync(path.join(AUTH_ROOT, entry), { recursive: true, force: true });
                }
            }
        } else {
            if (fs.existsSync(session.authDir)) {
                fs.rmSync(session.authDir, { recursive: true, force: true });
            }
            fs.mkdirSync(session.authDir, { recursive: true });
        }
    } catch (e) {
        console.error(`[Nagah WhatsApp][${session.id}] Error clearing auth files:`, e.message);
    }
}

async function initSession(rawId, forceNew = false) {
    const session = getOrCreateSessionState(rawId);

    if (session.isInitializing) {
        console.log(`[Nagah WhatsApp][${session.id}] Already initializing, skipping duplicate call`);
        return session;
    }
    session.isInitializing = true;

    if (session.reconnectTimer) {
        clearTimeout(session.reconnectTimer);
        session.reconnectTimer = null;
    }

    try {
        if (!fs.existsSync(session.authDir)) {
            fs.mkdirSync(session.authDir, { recursive: true });
        }

        if (session.sock) {
            try {
                session.sock.ev.removeAllListeners();
                session.sock.ws?.close();
            } catch (e) {}
            session.sock = null;
        }

        if (forceNew) {
            console.log(`[Nagah WhatsApp][${session.id}] Force new session requested. Clearing auth folder...`);
            clearAuthFiles(session);
            session.connectedPhone = null;
            session.connectedName = null;
            session.currentQr = null;
            session.currentPairingCode = null;
        }

        const hasSavedCreds = checkExistingCreds(session);
        session.connectionStatus = hasSavedCreds ? 'reconnecting' : 'initializing';

        const { state, saveCreds } = await useMultiFileAuthState(session.authDir);
        const { version } = await fetchLatestBaileysVersion();

        const sock = makeWASocket({
            version,
            logger,
            printQRInTerminal: false,
            auth: {
                creds: state.creds,
                keys: makeCacheableSignalKeyStore(state.keys, logger),
            },
            browser: ['Nagah Racing Gateway', 'Chrome', '120.0.0'],
            connectTimeoutMs: 60000,
            defaultQueryTimeoutMs: 60000,
            keepAliveIntervalMs: 25000,
            emitOwnEvents: false,
            markOnlineOnConnect: true,
            retryRequestDelayMs: 3000,
        });

        session.sock = sock;
        sock.ev.on('creds.update', saveCreds);

        sock.ev.on('connection.update', async (update) => {
            // Guard against stale sockets if session was restarted or deleted
            if (session.sock !== sock) return;

            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                try {
                    session.currentQr = await qrcode.toDataURL(qr);
                    session.connectionStatus = 'waiting_for_scan';
                    console.log(`[Nagah WhatsApp][${session.id}] New QR code ready for scan`);
                } catch (err) {
                    console.error(`[Nagah WhatsApp][${session.id}] QR generate error:`, err);
                }
            }

            if (connection === 'open') {
                session.reconnectAttempts = 0;
                session.connectionStatus = 'connected';
                session.currentQr = null;
                session.currentPairingCode = null;

                const rawUserId = sock.user?.id || '';
                session.connectedPhone = rawUserId.split(':')[0] || rawUserId.split('@')[0] || session.connectedPhone || 'Unknown';
                session.connectedName = sock.user?.name || session.connectedName || 'Nagah Racing';

                console.log(`[Nagah WhatsApp][${session.id}] Connected as ${session.connectedName} (+${session.connectedPhone})`);
            }

            if (connection === 'close') {
                const statusCode = lastDisconnect?.error?.output?.statusCode;
                const errorMsg = lastDisconnect?.error?.message || 'Connection lost';
                const isLoggedOut = statusCode === DisconnectReason.loggedOut;

                console.log(`[Nagah WhatsApp][${session.id}] Connection closed (Code: ${statusCode}, Reason: ${errorMsg})`);

                if (!sessions.has(session.id)) {
                    // Session was explicitly deleted
                    return;
                }

                if (isLoggedOut) {
                    console.log(`[Nagah WhatsApp][${session.id}] Logged out from phone. Resetting session...`);
                    session.connectionStatus = 'disconnected';
                    session.connectedPhone = null;
                    session.connectedName = null;
                    session.currentQr = null;
                    session.currentPairingCode = null;
                    clearAuthFiles(session);

                    session.isInitializing = false;
                    session.reconnectTimer = setTimeout(() => initSession(session.id, false), 2000);
                } else {
                    session.reconnectAttempts++;
                    session.connectionStatus = 'reconnecting';
                    console.log(`[Nagah WhatsApp][${session.id}] Reconnecting attempt #${session.reconnectAttempts}...`);

                    session.isInitializing = false;
                    const delayMs = statusCode === DisconnectReason.restartRequired ? 1000 : 3500;
                    session.reconnectTimer = setTimeout(() => initSession(session.id, false), delayMs);
                }
            }
        });
    } catch (err) {
        console.error(`[Nagah WhatsApp][${session.id}] Initialization error:`, err.message || err);
        session.connectionStatus = 'disconnected';
    } finally {
        session.isInitializing = false;
    }

    return session;
}

function buildStatusPayload(session) {
    const hasCreds = Boolean(session.connectedPhone) || fs.existsSync(path.join(session.authDir, 'creds.json'));

    return {
        success: true,
        online: true,
        session_id: session.id,
        status: session.connectionStatus,
        is_connected: session.connectionStatus === 'connected',
        has_session: hasCreds,
        phone: session.connectedPhone,
        name: session.connectedName,
        qr: session.currentQr,
        pairing_code: session.currentPairingCode,
        reconnect_attempts: session.reconnectAttempts,
        timestamp: new Date().toISOString()
    };
}

// ============================================================
// Reusable Handlers (Work for both /sessions/:sessionId/* and legacy /*)
// ============================================================

async function handleStatus(req, res, sessionId) {
    const id = sanitizeSessionId(sessionId);
    const exists = sessions.has(id);
    const session = getOrCreateSessionState(id);

    // Lazily initialize a new session when first polled so its QR code is generated
    if (!exists || (!session.sock && !session.isInitializing && session.connectionStatus !== 'reconnecting')) {
        initSession(id, false);
    }

    res.json(buildStatusPayload(session));
}

async function handlePairingCode(req, res, sessionId) {
    try {
        let phone = req.body.phone;
        if (!phone) {
            return res.status(400).json({ success: false, message: 'Phone number is required' });
        }

        phone = phone.toString().replace(/[^0-9]/g, '');
        const id = sanitizeSessionId(sessionId);
        const session = getOrCreateSessionState(id);

        if (!session.sock) {
            await initSession(id, false);
            await delay(2000);
        }

        if (session.connectionStatus === 'connected') {
            return res.status(400).json({ success: false, message: 'WhatsApp is already connected' });
        }

        const code = await session.sock.requestPairingCode(phone);
        session.currentPairingCode = code;

        res.json({
            success: true,
            session_id: id,
            pairing_code: code,
            message: 'Pairing code generated successfully'
        });
    } catch (err) {
        console.error(`[Nagah WhatsApp][${sessionId}] Pairing code error:`, err);
        res.status(500).json({ success: false, message: err.message || 'Failed to generate pairing code' });
    }
}

async function handleSend(req, res, sessionId) {
    try {
        const { phone, message } = req.body;
        const id = sanitizeSessionId(sessionId);

        if (!phone || !message) {
            return res.status(400).json({
                success: false,
                message: 'Both phone and message fields are required'
            });
        }

        const session = getOrCreateSessionState(id);

        if (session.connectionStatus !== 'connected' || !session.sock) {
            // Try initializing if credentials exist on disk
            if (!session.sock && checkExistingCreds(session)) {
                initSession(id, false);
            }
            return res.status(503).json({
                success: false,
                session_id: id,
                status: session.connectionStatus,
                message: 'WhatsApp session is not connected yet. Current status: ' + session.connectionStatus
            });
        }

        let cleanPhone = phone.toString().replace(/[^0-9]/g, '');
        if (cleanPhone.startsWith('00')) {
            cleanPhone = cleanPhone.substring(2);
        }

        const jid = `${cleanPhone}@s.whatsapp.net`;
        const result = await session.sock.sendMessage(jid, { text: message.trim() });

        res.json({
            success: true,
            session_id: id,
            message: 'Message sent successfully',
            message_id: result?.key?.id || null,
            recipient: cleanPhone
        });
    } catch (err) {
        console.error(`[Nagah WhatsApp][${sessionId}] Send error:`, err);
        res.status(500).json({
            success: false,
            message: err.message || 'Failed to send WhatsApp message'
        });
    }
}

async function handleLogout(req, res, sessionId) {
    try {
        const id = sanitizeSessionId(sessionId);
        const session = getOrCreateSessionState(id);

        if (session.reconnectTimer) {
            clearTimeout(session.reconnectTimer);
            session.reconnectTimer = null;
        }

        if (session.sock) {
            try {
                await session.sock.logout();
            } catch (e) {}
            try {
                session.sock.ev.removeAllListeners();
                session.sock.ws?.close();
            } catch (e) {}
            session.sock = null;
        }

        clearAuthFiles(session);
        session.connectionStatus = 'disconnected';
        session.connectedPhone = null;
        session.connectedName = null;
        session.currentQr = null;
        session.currentPairingCode = null;
        session.reconnectAttempts = 0;

        session.reconnectTimer = setTimeout(() => initSession(id, true), 1500);

        res.json({
            success: true,
            session_id: id,
            message: 'WhatsApp session logged out and reset successfully'
        });
    } catch (err) {
        res.status(500).json({ success: false, message: err.message });
    }
}

async function handleDeleteSession(req, res, sessionId) {
    try {
        const id = sanitizeSessionId(sessionId);
        const session = sessions.get(id);

        if (session) {
            if (session.reconnectTimer) {
                clearTimeout(session.reconnectTimer);
                session.reconnectTimer = null;
            }
            if (session.sock) {
                try {
                    await session.sock.logout();
                } catch (e) {}
                try {
                    session.sock.ev.removeAllListeners();
                    session.sock.ws?.close();
                } catch (e) {}
                session.sock = null;
            }
            sessions.delete(id);
        }

        const dir = getSessionAuthDir(id);
        if (id !== 'default' && fs.existsSync(dir)) {
            fs.rmSync(dir, { recursive: true, force: true });
        }

        res.json({
            success: true,
            session_id: id,
            message: 'WhatsApp session deleted permanently'
        });
    } catch (err) {
        res.status(500).json({ success: false, message: err.message });
    }
}

// ============================================================
// Multi-Session Routes
// ============================================================
app.get('/sessions', (req, res) => {
    const list = [];
    for (const session of sessions.values()) {
        list.push({
            session_id: session.id,
            status: session.connectionStatus,
            is_connected: session.connectionStatus === 'connected',
            phone: session.connectedPhone,
            name: session.connectedName,
        });
    }
    res.json({ success: true, count: list.length, sessions: list });
});

app.get('/sessions/:sessionId/status', (req, res) => handleStatus(req, res, req.params.sessionId));
app.post('/sessions/:sessionId/pairing-code', (req, res) => handlePairingCode(req, res, req.params.sessionId));
app.post('/sessions/:sessionId/send', (req, res) => handleSend(req, res, req.params.sessionId));
app.post('/sessions/:sessionId/logout', (req, res) => handleLogout(req, res, req.params.sessionId));
app.post('/sessions/:sessionId/restart', async (req, res) => {
    try {
        await initSession(req.params.sessionId, false);
        res.json({ success: true, message: 'Session restarted' });
    } catch (err) {
        res.status(500).json({ success: false, message: err.message });
    }
});
app.delete('/sessions/:sessionId', (req, res) => handleDeleteSession(req, res, req.params.sessionId));

// ============================================================
// Legacy Single-Session Routes (Mapped to 'default' session)
// ============================================================
app.get('/status', (req, res) => handleStatus(req, res, 'default'));
app.post('/pairing-code', (req, res) => handlePairingCode(req, res, 'default'));
app.post('/send', (req, res) => handleSend(req, res, 'default'));
app.post('/logout', (req, res) => handleLogout(req, res, 'default'));
app.post('/restart', async (req, res) => {
    try {
        await initSession('default', false);
        res.json({ success: true, message: 'WhatsApp connection restarted' });
    } catch (err) {
        res.status(500).json({ success: false, message: err.message });
    }
});

// Boot all previously saved sessions automatically on server start
function bootSavedSessions() {
    // 1. Always boot 'default' session
    initSession('default', false);

    // 2. Boot any additional saved subscriber sessions inside auth_info/sessions/*
    try {
        if (fs.existsSync(SESSIONS_ROOT)) {
            const dirs = fs.readdirSync(SESSIONS_ROOT, { withFileTypes: true });
            for (const d of dirs) {
                if (!d.isDirectory()) continue;
                const credsFile = path.join(SESSIONS_ROOT, d.name, 'creds.json');
                if (fs.existsSync(credsFile)) {
                    console.log(`[Nagah WhatsApp] Auto-booting saved session: ${d.name}`);
                    initSession(d.name, false);
                }
            }
        }
    } catch (e) {
        console.error('[Nagah WhatsApp] Error scanning saved sessions:', e.message);
    }
}

// Start Express HTTP Server
app.listen(PORT, '127.0.0.1', () => {
    console.log(`[Nagah WhatsApp Multi-Session Service] Server running on http://127.0.0.1:${PORT}`);
    bootSavedSessions();
});
