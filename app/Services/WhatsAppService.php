<?php

namespace App\Services;

use App\Models\WhatsAppMessage;
use App\Models\WhatsAppSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppService
{
    public const DEFAULT_LOCAL_URL = 'http://127.0.0.1:3002';

    /**
     * جلب الحساب الرئيسي الافتراضي (لإرسال OTP لتطبيق نجاح ريسنج)
     */
    public function getSettings(): WhatsAppSetting
    {
        return WhatsAppSetting::getPrimaryForOtp();
    }

    /**
     * البحث عن حساب المشترك باستخدام مفتاح الـ API
     */
    public function findAccountByApiKey(?string $providedKey): ?WhatsAppSetting
    {
        return WhatsAppSetting::findByApiKey($providedKey);
    }

    /**
     * التحقق من صحة مفتاح الـ API
     */
    public function isValidApiKey(?string $providedKey): bool
    {
        return $this->findAccountByApiKey($providedKey) !== null;
    }

    protected function resolveLocalBaseUrl(WhatsAppSetting $account): string
    {
        return rtrim($account->local_server_url ?: self::DEFAULT_LOCAL_URL, '/');
    }

    protected function resolveLocalPort(WhatsAppSetting $account): int
    {
        $parsed = parse_url($this->resolveLocalBaseUrl($account));
        return (int) ($parsed['port'] ?? 3002);
    }

    protected function resolveNodeBinary(): string
    {
        $candidates = array_filter([
            env('NODE_BINARY'),
            '/opt/homebrew/bin/node',
            '/usr/local/bin/node',
            '/usr/bin/node',
        ]);

        foreach ($candidates as $path) {
            if (is_string($path) && is_executable($path)) {
                return $path;
            }
        }

        return 'node';
    }

    /**
     * فحص الحالة الحية لجلسة محددة وجلب رمز QR الخاص بها فقط
     */
    public function getStatus(?WhatsAppSetting $account = null): array
    {
        $account = $account ?: $this->getSettings();

        if (!$account->isUsable()) {
            $summary = $account->getSubscriptionSummary();
            return [
                'online'               => true,
                'is_connected'         => false,
                'has_session'          => !empty($account->connected_phone),
                'status'               => $summary['status_key'],
                'phone'                => $account->connected_phone,
                'name'                 => $account->connected_name,
                'qr'                   => null,
                'pairing_code'         => null,
                'engine'               => $account->engine,
                'subscription_blocked' => true,
                'subscription'         => $summary,
                'message'              => $summary['status_key'] === 'expired'
                    ? 'انتهت صلاحية اشتراكك في خدمة الواتساب. يرجى تجديد الاشتراك مع الإدارة.'
                    : 'تم إيقاف اشتراكك مؤقتاً من قبل الإدارة. يرجى التواصل مع الإدارة.',
            ];
        }

        if ($account->engine === 'textmebot') {
            return $this->getTextMeBotStatus($account);
        }

        return $this->getLocalStatus($account);
    }

    /**
     * فحص حالة جلسة محددة في السيرفر الداخلي (Baileys Multi-Session)
     */
    protected function getLocalStatus(WhatsAppSetting $account): array
    {
        $baseUrl = $this->resolveLocalBaseUrl($account);
        $port = $this->resolveLocalPort($account);
        $sessionId = urlencode($account->getSessionKey());

        try {
            $response = Http::timeout(2)->get("{$baseUrl}/sessions/{$sessionId}/status");

            if ($response->successful()) {
                $data = $response->json();
                $isConnected = (bool) ($data['is_connected'] ?? false);
                $phone = $data['phone'] ?? null;
                $name = $data['name'] ?? null;

                if ($account->is_connected !== $isConnected || ($phone && $account->connected_phone !== $phone)) {
                    $account->update([
                        'is_connected'      => $isConnected,
                        'connected_phone'   => $phone ?: $account->connected_phone,
                        'connected_name'    => $name ?: $account->connected_name,
                        'last_connected_at' => $isConnected ? now() : $account->last_connected_at,
                    ]);
                }

                return [
                    'online'               => true,
                    'is_connected'         => $isConnected,
                    'has_session'          => (bool) ($data['has_session'] ?? false),
                    'status'               => $data['status'] ?? 'unknown',
                    'phone'                => $phone ?: $account->connected_phone,
                    'name'                 => $name ?: $account->connected_name,
                    'qr'                   => $data['qr'] ?? null,
                    'pairing_code'         => $data['pairing_code'] ?? null,
                    'reconnect_attempts'   => $data['reconnect_attempts'] ?? 0,
                    'engine'               => 'local',
                    'port'                 => $port,
                    'session_id'           => $account->getSessionKey(),
                    'subscription_blocked' => false,
                    'message'              => $isConnected
                        ? 'متصل بنجاح وجاهز للإرسال'
                        : (($data['status'] ?? '') === 'reconnecting'
                            ? 'جاري استعادة الاتصال بالواتساب تلقائياً...'
                            : 'بانتظار مسح رمز QR من تطبيق الواتساب'),
                ];
            }
        } catch (Throwable $e) {
            $this->attemptAutoStartService($account);
        }

        return [
            'online'               => false,
            'is_connected'         => false,
            'has_session'          => !empty($account->connected_phone),
            'status'               => 'service_offline',
            'phone'                => $account->connected_phone,
            'name'                 => $account->connected_name,
            'qr'                   => null,
            'pairing_code'         => null,
            'engine'               => 'local',
            'port'                 => $port,
            'session_id'           => $account->getSessionKey(),
            'subscription_blocked' => false,
            'message'              => "محرك الواتساب الداخلي متوقف حالياً على المنفذ {$port}. يمكنك تشغيله فوراً بالضغط على زر تشغيل السيرفر أدناه.",
        ];
    }

    public function attemptAutoStartService(?WhatsAppSetting $account = null): void
    {
        if (Cache::has('nr_wa_auto_starting')) {
            return;
        }
        Cache::put('nr_wa_auto_starting', true, 10);

        try {
            $account = $account ?: $this->getSettings();
            $serviceDir = base_path('whatsapp-service');
            $logPath = storage_path('logs/whatsapp.log');
            $port = $this->resolveLocalPort($account);
            $nodeBin = $this->resolveNodeBinary();

            if (is_dir($serviceDir) && file_exists($serviceDir . '/server.js')) {
                $cmd = "cd " . escapeshellarg($serviceDir)
                    . " && (PORT=" . (int) $port . " nohup " . escapeshellarg($nodeBin) . " server.js >> " . escapeshellarg($logPath) . " 2>&1 &)";
                @exec($cmd);
            }
        } catch (Throwable $e) {
        }
    }

    public function startLocalService(?WhatsAppSetting $account = null): array
    {
        $account = $account ?: $this->getSettings();
        $baseUrl = $this->resolveLocalBaseUrl($account);
        $port = $this->resolveLocalPort($account);
        $serviceDir = base_path('whatsapp-service');
        $logPath = storage_path('logs/whatsapp.log');
        $nodeBin = $this->resolveNodeBinary();

        try {
            $res = Http::timeout(1)->get("{$baseUrl}/sessions");
            if ($res->successful()) {
                return ['success' => true, 'message' => "خادم الواتساب الداخلي يعمل بالفعل على المنفذ {$port}."];
            }
        } catch (Throwable $e) {
        }

        if (!is_dir($serviceDir) || !file_exists($serviceDir . '/server.js')) {
            return ['success' => false, 'message' => 'مجلد whatsapp-service غير موجود على السيرفر.'];
        }

        $cmd = "cd " . escapeshellarg($serviceDir)
            . " && (PORT=" . (int) $port . " nohup " . escapeshellarg($nodeBin) . " server.js >> " . escapeshellarg($logPath) . " 2>&1 &)";
        @exec($cmd);

        usleep(1500000);

        try {
            $res = Http::timeout(2)->get("{$baseUrl}/sessions");
            if ($res->successful()) {
                return ['success' => true, 'message' => 'تم تشغيل خادم الواتساب بنجاح وجاري الاتصال!'];
            }
        } catch (Throwable $e) {
        }

        return [
            'success' => true,
            'message' => 'تم إرسال أمر تشغيل خادم الواتساب في الخلفية بنجاح، وسيبدأ خلال ثوانٍ.',
        ];
    }

    protected function getTextMeBotStatus(WhatsAppSetting $account): array
    {
        $apiKey = $account->textmebot_api_key;

        if (empty($apiKey)) {
            return [
                'online'               => false,
                'is_connected'         => false,
                'status'               => 'missing_api_key',
                'phone'                => $account->textmebot_phone,
                'name'                 => 'TextMeBot Gateway',
                'qr'                   => null,
                'engine'               => 'textmebot',
                'subscription_blocked' => false,
                'message'              => 'يرجى إدخال مفتاح API الخاص بـ TextMeBot أولاً',
            ];
        }

        try {
            $response = Http::timeout(5)->get("https://api.textmebot.com/status.php", [
                'apikey' => $apiKey,
                'json'   => 'yes',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $isConnected = ($data['status'] ?? '') === 'connected' || ($data['connected'] ?? false) === true;

                return [
                    'online'               => true,
                    'is_connected'         => $isConnected,
                    'status'               => $data['status'] ?? ($isConnected ? 'connected' : 'waiting'),
                    'phone'                => $account->textmebot_phone,
                    'name'                 => 'TextMeBot Connected',
                    'qr'                   => $data['qr'] ?? null,
                    'pairing_code'         => $data['pairing_code'] ?? null,
                    'engine'               => 'textmebot',
                    'subscription_blocked' => false,
                    'message'              => $data['message'] ?? ($isConnected ? 'متصل بنجاح عبر TextMeBot' : 'بانتظار الربط في TextMeBot'),
                ];
            }
        } catch (Throwable $e) {
            Log::warning('TextMeBot status error: ' . $e->getMessage());
        }

        return [
            'online'               => false,
            'is_connected'         => false,
            'status'               => 'error',
            'phone'                => $account->textmebot_phone,
            'name'                 => 'TextMeBot Gateway',
            'qr'                   => null,
            'engine'               => 'textmebot',
            'subscription_blocked' => false,
            'message'              => 'تعذر التحقق من حالة TextMeBot، يرجى مراجعة الاتصال والمفتاح.',
        ];
    }

    /**
     * طلب كود الاقتران عبر رقم الهاتف لجلسة محددة
     */
    public function requestPairingCode(string $phone, ?WhatsAppSetting $account = null): array
    {
        $account = $account ?: $this->getSettings();

        if (!$account->isUsable()) {
            return [
                'success' => false,
                'message' => $account->isExpired()
                    ? 'انتهت صلاحية الاشتراك. يرجى تجديد الاشتراك أولاً.'
                    : 'الاشتراك موقوف من قبل الإدارة.',
            ];
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleanPhone, '00')) {
            $cleanPhone = substr($cleanPhone, 2);
        }

        if ($account->engine === 'local') {
            $baseUrl = $this->resolveLocalBaseUrl($account);
            $sessionId = urlencode($account->getSessionKey());
            try {
                $response = Http::timeout(8)->post("{$baseUrl}/sessions/{$sessionId}/pairing-code", [
                    'phone' => $cleanPhone,
                ]);

                if ($response->successful()) {
                    return $response->json();
                }
                return ['success' => false, 'message' => $response->json('message') ?? 'فشل طلب كود الاقتران'];
            } catch (Throwable $e) {
                return ['success' => false, 'message' => 'تعذر الاتصال بمحرك الواتساب: ' . $e->getMessage()];
            }
        }

        return ['success' => false, 'message' => 'خاصية كود الاقتران متاحة للمحرك الداخلي المباشر فقط'];
    }

    /**
     * إرسال رسالة واتساب من خلال جلسة المشترك المحددة وتسجيلها لحسابه فقط
     */
    public function sendMessage(string $phone, string $message, string $senderType = 'api', ?WhatsAppSetting $account = null): array
    {
        $account = $account ?: $this->getSettings();

        if (!$account->isUsable()) {
            return [
                'success'    => false,
                'error_code' => $account->isExpired() ? 'subscription_expired' : 'subscription_suspended',
                'message'    => $account->isExpired()
                    ? 'انتهت صلاحية اشتراكك في خدمة الواتساب. يرجى تجديد الاشتراك مع الإدارة.'
                    : 'تم إيقاف اشتراكك مؤقتاً من قبل الإدارة.',
            ];
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleanPhone, '00')) {
            $cleanPhone = substr($cleanPhone, 2);
        }

        if (strlen($cleanPhone) < 7) {
            return [
                'success' => false,
                'message' => 'رقم الهاتف غير صالح. يجب أن يحتوي على كود الدولة وألا يقل عن 7 أرقام.',
            ];
        }

        if (empty(trim($message))) {
            return [
                'success' => false,
                'message' => 'نص الرسالة مطلوب ولا يمكن أن يكون فارغاً.',
            ];
        }

        $logRecord = WhatsAppMessage::create([
            'whatsapp_setting_id' => $account->id,
            'recipient_phone'     => $cleanPhone,
            'message'             => $message,
            'status'              => 'pending',
            'engine_used'         => $account->engine,
            'sender_type'         => $senderType,
        ]);

        if ($account->engine === 'textmebot') {
            return $this->sendViaTextMeBot($account, $cleanPhone, $message, $logRecord);
        }

        return $this->sendViaLocal($account, $cleanPhone, $message, $logRecord);
    }

    protected function sendViaLocal(WhatsAppSetting $account, string $phone, string $message, WhatsAppMessage $logRecord): array
    {
        $baseUrl = $this->resolveLocalBaseUrl($account);
        $sessionId = urlencode($account->getSessionKey());

        try {
            $response = Http::timeout(10)->post("{$baseUrl}/sessions/{$sessionId}/send", [
                'phone'   => $phone,
                'message' => $message,
            ]);

            $data = $response->json();

            if ($response->successful() && ($data['success'] ?? false) === true) {
                $logRecord->update([
                    'status'        => 'sent',
                    'message_id'    => $data['message_id'] ?? null,
                    'response_data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                ]);

                return [
                    'success'    => true,
                    'message'    => 'تم إرسال الرسالة بنجاح عبر الواتساب.',
                    'message_id' => $data['message_id'] ?? null,
                    'recipient'  => $phone,
                ];
            }

            $errorMessage = $data['message'] ?? 'فشل إرسال الرسالة من خلال السيرفر الداخلي.';
            $logRecord->update([
                'status'        => 'failed',
                'error_message' => $errorMessage,
                'response_data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            ]);

            return [
                'success' => false,
                'message' => $errorMessage,
            ];
        } catch (Throwable $e) {
            $errorMessage = 'تعذر الاتصال بالسيرفر الداخلي: ' . $e->getMessage();
            $logRecord->update([
                'status'        => 'failed',
                'error_message' => $errorMessage,
            ]);

            return [
                'success' => false,
                'message' => $errorMessage,
            ];
        }
    }

    protected function sendViaTextMeBot(WhatsAppSetting $account, string $phone, string $message, WhatsAppMessage $logRecord): array
    {
        $apiKey = $account->textmebot_api_key;

        if (empty($apiKey)) {
            $err = 'مفتاح TextMeBot API غير مضبوط في الإعدادات.';
            $logRecord->update(['status' => 'failed', 'error_message' => $err]);
            return ['success' => false, 'message' => $err];
        }

        try {
            $response = Http::timeout(12)->get("https://api.textmebot.com/send.php", [
                'recipient' => $phone,
                'apikey'    => $apiKey,
                'text'      => $message,
                'json'      => 'yes',
            ]);

            $body = $response->body();
            $isJson = str_starts_with(trim($body), '{');
            $data = $isJson ? $response->json() : ['raw_response' => $body];

            if ($response->successful()) {
                $logRecord->update([
                    'status'        => 'sent',
                    'message_id'    => $data['message_id'] ?? $data['id'] ?? null,
                    'response_data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                ]);

                return [
                    'success'   => true,
                    'message'   => 'تم إرسال الرسالة بنجاح عبر بوابة TextMeBot.',
                    'recipient' => $phone,
                ];
            }

            $err = $data['message'] ?? $body ?: 'خطأ في استجابة TextMeBot';
            $logRecord->update([
                'status'        => 'failed',
                'error_message' => $err,
                'response_data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            ]);

            return ['success' => false, 'message' => $err];
        } catch (Throwable $e) {
            $err = 'تعذر الاتصال بـ TextMeBot: ' . $e->getMessage();
            $logRecord->update(['status' => 'failed', 'error_message' => $err]);
            return ['success' => false, 'message' => $err];
        }
    }

    /**
     * قطع الاتصال لجلسة محددة فقط
     */
    public function disconnect(?WhatsAppSetting $account = null): array
    {
        $account = $account ?: $this->getSettings();

        if ($account->engine === 'local') {
            $baseUrl = $this->resolveLocalBaseUrl($account);
            $sessionId = urlencode($account->getSessionKey());
            try {
                Http::timeout(5)->post("{$baseUrl}/sessions/{$sessionId}/logout");
            } catch (Throwable $e) {
                Log::warning('Logout request failed: ' . $e->getMessage());
            }
        }

        $account->update([
            'is_connected'    => false,
            'connected_phone' => null,
            'connected_name'  => null,
        ]);

        return [
            'success' => true,
            'message' => 'تم قطع الاتصال بنجاح وإعادة ضبط الجلسة.',
        ];
    }

    /**
     * حذف جلسة مشترك من محرك Node.js بالكامل عند حذف الاشتراك
     */
    public function deleteSession(WhatsAppSetting $account): void
    {
        if ($account->engine === 'local') {
            $baseUrl = $this->resolveLocalBaseUrl($account);
            $sessionId = urlencode($account->getSessionKey());
            try {
                Http::timeout(5)->delete("{$baseUrl}/sessions/{$sessionId}");
            } catch (Throwable $e) {
                Log::warning('Delete session request failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * إعادة توليد مفتاح API جديد لحساب محدد
     */
    public function regenerateApiKey(?WhatsAppSetting $account = null): string
    {
        $account = $account ?: $this->getSettings();
        $newKey = WhatsAppSetting::generateUniqueApiKey();
        $account->update(['api_key' => $newKey]);
        return $newKey;
    }
}
