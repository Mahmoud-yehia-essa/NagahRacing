<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppSetting;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppApiController extends Controller
{
    protected WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * التحقق من مفتاح الـ API الصادر من لوحة التحكم
     */
    protected function authenticateApiKey(Request $request): ?WhatsAppSetting
    {
        $providedKey = $request->input('apikey')
            ?? $request->input('api_key')
            ?? $request->header('x-api-key')
            ?? $request->bearerToken();

        if (!$this->whatsappService->isValidApiKey($providedKey)) {
            return null;
        }

        return $this->whatsappService->getSettings();
    }

    /**
     * إرسال رسالة واتساب عبر الـ API (يدعم GET و POST تماماً مثل TextMeBot)
     *
     * GET  /api/whatsapp/send?apikey={KEY}&phone={PHONE}&text={TEXT}
     * POST /api/whatsapp/send
     */
    public function send(Request $request)
    {
        $settings = $this->authenticateApiKey($request);

        if (!$settings) {
            return response()->json([
                'success' => false,
                'status'  => 'unauthorized',
                'message' => 'مفتاح API غير صالح أو غير مصرح به (Invalid or missing API key).',
            ], 401);
        }

        $phone = $request->input('phone')
            ?? $request->input('recipient')
            ?? $request->input('to');

        $message = $request->input('text')
            ?? $request->input('message')
            ?? $request->input('body');

        if (empty($phone)) {
            return response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'رقم المستلم مطلوب (phone / recipient is required).',
            ], 422);
        }

        if (empty($message)) {
            return response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'نص الرسالة مطلوب (text / message is required).',
            ], 422);
        }

        $result = $this->whatsappService->sendMessage((string) $phone, (string) $message, 'api');

        if ($result['success']) {
            return response()->json([
                'success'    => true,
                'status'     => 'sent',
                'message'    => 'تم إرسال الرسالة بنجاح عبر الواتساب.',
                'recipient'  => $result['recipient'] ?? $phone,
                'message_id' => $result['message_id'] ?? null,
                'timestamp'  => now()->toIso8601String(),
            ], 200);
        }

        return response()->json([
            'success'   => false,
            'status'    => 'failed',
            'message'   => $result['message'],
            'recipient' => $phone,
            'timestamp' => now()->toIso8601String(),
        ], 400);
    }

    /**
     * إرسال كود التحقق OTP لتطبيق نجاح ريسنج عبر بوابة الواتساب
     *
     * POST /api/whatsapp/send-otp
     */
    public function sendOtp(Request $request)
    {
        $phone = $request->input('phone') ?? $request->input('recipient');
        $code = $request->input('code') ?? $request->input('otp') ?? $request->input('message');

        if (empty($phone)) {
            return response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'رقم الهاتف مطلوب.',
            ], 422);
        }

        $cleanCode = trim((string) $code);
        if ($cleanCode === '' || strlen($cleanCode) > 12) {
            return response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'رمز التحقق غير صالح.',
            ], 422);
        }

        $formattedMessage = "رمز التحقق (OTP) الخاص بك في تطبيق نجاح ريسنج هو: {$cleanCode}\nلا تشارك هذا الرمز مع أي شخص.";

        $result = $this->whatsappService->sendMessage((string) $phone, $formattedMessage, 'otp');

        if ($result['success']) {
            return response()->json([
                'success'    => true,
                'status'     => 'sent',
                'message'    => 'تم إرسال رمز التحقق بنجاح عبر الواتساب.',
                'recipient'  => $result['recipient'] ?? $phone,
                'message_id' => $result['message_id'] ?? null,
                'timestamp'  => now()->toIso8601String(),
            ], 200);
        }

        return response()->json([
            'success'   => false,
            'status'    => 'failed',
            'message'   => $result['message'] ?? 'تعذر إرسال رمز التحقق.',
            'recipient' => $phone,
            'timestamp' => now()->toIso8601String(),
        ], 400);
    }

    /**
     * الاستعلام عن حالة اتصال الواتساب برمجياً عبر الـ API
     *
     * GET /api/whatsapp/status?apikey={KEY}
     */
    public function status(Request $request)
    {
        $settings = $this->authenticateApiKey($request);

        if (!$settings) {
            return response()->json([
                'success' => false,
                'status'  => 'unauthorized',
                'message' => 'مفتاح API غير صالح أو مفقود.',
            ], 401);
        }

        $liveStatus = $this->whatsappService->getStatus();

        return response()->json([
            'success'         => true,
            'is_connected'    => (bool) ($liveStatus['is_connected'] ?? false),
            'status'          => $liveStatus['status'] ?? 'unknown',
            'connected_phone' => $liveStatus['phone'] ?? $settings->connected_phone,
            'connected_name'  => $liveStatus['name'] ?? $settings->connected_name,
            'engine'          => $settings->engine,
            'message'         => $liveStatus['message'] ?? '',
            'timestamp'       => now()->toIso8601String(),
        ]);
    }
}
