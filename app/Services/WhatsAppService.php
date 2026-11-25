<?php

namespace App\Services;

use App\Models\WhatsAppMessage;
use App\Models\WhatsAppSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class WhatsAppService
{
    public const DEFAULT_LOCAL_URL = 'http://127.0.0.1:3002';

    /**
     * جلب إعدادات الواتساب الحالية
     */
    public function getSettings(): WhatsAppSetting
    {
        return WhatsAppSetting::getSettings();
    }

    /**
     * استخراج رابط الخادم المحلي والمنفذ
     */
    protected function resolveLocalBaseUrl(WhatsAppSetting $settings): string
    {
        return rtrim($settings->local_server_url ?: self::DEFAULT_LOCAL_URL, '/');
    }

    protected function resolveLocalPort(WhatsAppSetting $settings): int
    {
        $parsed = parse_url($this->resolveLocalBaseUrl($settings));
        return (int) ($parsed['port'] ?? 3002);
    }

    /**
     * إيجاد مسار node بشكل آمن (يعمل تحت MAMP و Linux و cPanel)
     */
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
     * فحص الحالة الحية وجلب رمز QR أو بيانات الاتصال
     */
    public function getStatus(): array
    {
        $settings = $this->getSettings();

        if ($settings->engine === 'textmebot') {
            return $this->getTextMeBotStatus($settings);
        }

        return $this->getLocalStatus($settings);
    }

    /**
     * فحص حالة السيرفر الداخلي (Baileys)
     */
    protected function getLocalStatus(WhatsAppSetting $settings): array
    {
        $baseUrl = $this->resolveLocalBaseUrl($settings);
        $port = $this->resolveLocalPort($settings);

        try {
            $response = Http::timeout(2)->get("{$baseUrl}/status");

            if ($response->successful()) {
                $data = $response->json();
                $isConnected = (bool) ($data['is_connected'] ?? false);
                $phone = $data['phone'] ?? null;
                $name = $data['name'] ?? null;

                if ($settings->is_connected !== $isConnected || ($phone && $settings->connected_phone !== $phone)) {
                    $settings->update([
                        'is_connected'      => $isConnected,
                        'connected_phone'   => $phone ?: $settings->connected_phone,
                        'connected_name'    => $name ?: $settings->connected_name,
                        'last_connected_at' => $isConnected ? now() : $settings->last_connected_at,
                    ]);
                }

                return [
                    'online'             => true,
                    'is_connected'       => $isConnected,
                    'has_session'        => (bool) ($data['has_session'] ?? false),
                    'status'             => $data['status'] ?? 'unknown',
                    'phone'              => $phone ?: $settings->connected_phone,
                    'name'               => $name ?: $settings->connected_name,
                    'qr'                 => $data['qr'] ?? null,
                    'pairing_code'       => $data['pairing_code'] ?? null,
                    'reconnect_attempts' => $data['reconnect_attempts'] ?? 0,
                    'engine'             => 'local',
                    'port'               => $port,
                    'message'            => $isConnected
                        ? 'متصل بنجاح وجاهز لإرسال رسائل OTP'
                        : (($data['status'] ?? '') === 'reconnecting'
                            ? 'جاري استعادة الاتصال بالواتساب تلقائياً...'
                            : 'بانتظار مسح رمز QR من تطبيق الواتساب'),
                ];
            }
        } catch (Throwable $e) {
            $this->attemptAutoStartService($settings);
        }

        return [
            'online'       => false,
            'is_connected' => false,
            'has_session'  => !empty($settings->connected_phone),
            'status'       => 'service_offline',
            'phone'        => $settings->connected_phone,
            'name'         => $settings->connected_name,
            'qr'           => null,
            'pairing_code' => null,
            'engine'       => 'local',
            'port'         => $port,
            'message'      => "محرك الواتساب الداخلي متوقف حالياً على المنفذ {$port}. يمكنك تشغيله فوراً بالضغط على زر تشغيل السيرفر أدناه.",
        ];
    }

    /**
     * محاولة تشغيل خادم الواتساب تلقائياً في الخلفية
     */
    public function attemptAutoStartService(?WhatsAppSetting $settings = null): void
    {
        if (Cache::has('nr_wa_auto_starting')) {
            return;
        }
        Cache::put('nr_wa_auto_starting', true, 10);

        try {
            $settings = $settings ?: $this->getSettings();
            $serviceDir = base_path('whatsapp-service');
            $logPath = storage_path('logs/whatsapp.log');
            $port = $this->resolveLocalPort($settings);
            $nodeBin = $this->resolveNodeBinary();

            if (is_dir($serviceDir) && file_exists($serviceDir . '/server.js')) {
                $cmd = "cd " . escapeshellarg($serviceDir)
                    . " && (PORT=" . (int) $port . " nohup " . escapeshellarg($nodeBin) . " server.js >> " . escapeshellarg($logPath) . " 2>&1 &)";
                @exec($cmd);
            }
        } catch (Throwable $e) {
        }
    }

    /**
     * تشغيل خادم الواتساب يدوياً والتحقق من عمله
     */
    public function startLocalService(): array
    {
        $settings = $this->getSettings();
        $baseUrl = $this->resolveLocalBaseUrl($settings);
        $port = $this->resolveLocalPort($settings);
        $serviceDir = base_path('whatsapp-service');
        $logPath = storage_path('logs/whatsapp.log');
        $nodeBin = $this->resolveNodeBinary();

        try {
            $res = Http::timeout(1)->get("{$baseUrl}/status");
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

        usleep(1500000); // الانتظار 1.5 ثانية للتشغيل

        try {
            $res = Http::timeout(2)->get("{$baseUrl}/status");
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

    /**
     * فحص حالة بوابة TextMeBot
     */
    protected function getTextMeBotStatus(WhatsAppSetting $settings): array
    {
        $apiKey = $settings->textmebot_api_key;

        if (empty($apiKey)) {
            return [
                'online'       => false,
                'is_connected' => false,
                'status'       => 'missing_api_key',
                'phone'        => $settings->textmebot_phone,
                'name'         => 'TextMeBot Gateway',
                'qr'           => null,
                'engine'       => 'textmebot',
                'message'      => 'يرجى إدخال مفتاح API الخاص بـ TextMeBot أولاً',
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
                    'online'       => true,
                    'is_connected' => $isConnected,
                    'status'       => $data['status'] ?? ($isConnected ? 'connected' : 'waiting'),
                    'phone'        => $settings->textmebot_phone,
                    'name'         => 'TextMeBot Connected',
                    'qr'           => $data['qr'] ?? null,
                    'pairing_code' => $data['pairing_code'] ?? null,
                    'engine'       => 'textmebot',
                    'message'      => $data['message'] ?? ($isConnected ? 'متصل بنجاح عبر TextMeBot' : 'بانتظار الربط في TextMeBot'),
                ];
            }
        } catch (Throwable $e) {
            Log::warning('TextMeBot status error: ' . $e->getMessage());
        }

        return [
            'online'       => false,
            'is_connected' => false,
            'status'       => 'error',
            'phone'        => $settings->textmebot_phone,
            'name'         => 'TextMeBot Gateway',
            'qr'           => null,
            'engine'       => 'textmebot',
            'message'      => 'تعذر التحقق من حالة TextMeBot، يرجى مراجعة الاتصال والمفتاح.',
        ];
    }

    /**
     * طلب كود الاقتران عبر رقم الهاتف (Pairing Code)
     */
    public function requestPairingCode(string $phone): array
    {
        $settings = $this->getSettings();
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($cleanPhone, '00')) {
            $cleanPhone = substr($cleanPhone, 2);
        }

        if ($settings->engine === 'local') {
            $baseUrl = $this->resolveLocalBaseUrl($settings);
            try {
                $response = Http::timeout(8)->post("{$baseUrl}/pairing-code", [
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
     * إرسال رسالة واتساب وتسجيلها في قاعدة البيانات
     */
    public function sendMessage(string $phone, string $message, string $senderType = 'api'): array
    {
        $settings = $this->getSettings();

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
            'recipient_phone' => $cleanPhone,
            'message'         => $message,
            'status'          => 'pending',
            'engine_used'     => $settings->engine,
            'sender_type'     => $senderType,
        ]);

        if ($settings->engine === 'textmebot') {
            return $this->sendViaTextMeBot($settings, $cleanPhone, $message, $logRecord);
        }

        return $this->sendViaLocal($settings, $cleanPhone, $message, $logRecord);
    }

    /**
     * إرسال عبر السيرفر الداخلي
     */
    protected function sendViaLocal(WhatsAppSetting $settings, string $phone, string $message, WhatsAppMessage $logRecord): array
    {
        $baseUrl = $this->resolveLocalBaseUrl($settings);

        try {
            $response = Http::timeout(10)->post("{$baseUrl}/send", [
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

    /**
     * إرسال عبر TextMeBot API
     */
    protected function sendViaTextMeBot(WhatsAppSetting $settings, string $phone, string $message, WhatsAppMessage $logRecord): array
    {
        $apiKey = $settings->textmebot_api_key;

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
     * قطع الاتصال وفك الارتباط
     */
    public function disconnect(): array
    {
        $settings = $this->getSettings();

        if ($settings->engine === 'local') {
            $baseUrl = $this->resolveLocalBaseUrl($settings);
            try {
                Http::timeout(5)->post("{$baseUrl}/logout");
            } catch (Throwable $e) {
                Log::warning('Logout request failed: ' . $e->getMessage());
            }
        }

        $settings->update([
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
     * إعادة توليد مفتاح API جديد
     */
    public function regenerateApiKey(): string
    {
        $settings = $this->getSettings();
        $newKey = 'nr_wa_' . Str::random(36);
        $settings->update(['api_key' => $newKey]);
        return $newKey;
    }

    /**
     * التحقق الآمن من تطابق مفتاح الـ API
     */
    public function isValidApiKey(?string $providedKey): bool
    {
        if (empty($providedKey)) {
            return false;
        }
        $settings = $this->getSettings();
        if (empty($settings->api_key)) {
            return false;
        }
        return hash_equals((string) $settings->api_key, trim((string) $providedKey));
    }
}
