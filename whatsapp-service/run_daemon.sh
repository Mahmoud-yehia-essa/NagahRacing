#!/bin/bash
# ========================================================
# Self-Healing WhatsApp Daemon for Nagah Racing
# Automatically keeps WhatsApp running and restarts if killed
# ========================================================

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" >/dev/null 2>&1 && pwd)"
LOG="$DIR/../storage/logs/whatsapp.log"
PORT="${PORT:-3002}"

cd "$DIR"

echo "[$(date)] Starting Nagah Racing WhatsApp Baileys Gateway Daemon on port $PORT..." >> "$LOG"

while true; do
    echo "[$(date)] Launching node server.js on port $PORT..." >> "$LOG"
    PORT="$PORT" node server.js >> "$LOG" 2>&1
    EXIT_CODE=$?
    echo "[$(date)] node server.js exited with code $EXIT_CODE. Restarting in 3 seconds..." >> "$LOG"
    sleep 3
done
