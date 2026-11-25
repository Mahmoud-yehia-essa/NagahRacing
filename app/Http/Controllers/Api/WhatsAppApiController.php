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
     * العثور على اشتراك/حساب المشترك بواسطة مفتاح الـ API الصادر من لوحة التحكم
     */
    protected function resolveAccountByApiKey(Request $request): ?WhatsAppSetting
    {
        $providedKey = $request->input('apikey')
            ?? $request->input('api_key')
            ?? $request->header('x-api-key')
            ?? $request->bearerToken();

        return $this->whatsappService->findAccountByApiKey($providedKey);
    }

    /**
     * إرسال رسالة واتساب عبر الـ API الخاص بكل مشترك (يدعم GET و POST مثل TextMeBot)
     *
     * GET  /api/whatsapp/send?apikey={KEY}&phone={PHONE}&text={TEXT}
     * POST /api/whatsapp/send
     */
    public function send(Request $request)
    {
        $account = $this->resolveAccountByApiKey($request);

        if (!$account) {
            return response()->json([
                'success' => false,
                'status'  => 'unauthorized',
                'message' => 'مفتاح API غير صالح أو غير مصرح به (Invalid or missing API key).',
            ], 401);
        }

        if (!$account->isUsable()) {
            $summary = $account->getSubscriptionSummary();
            return response()->json([
                'success'      => false,
                'status'       => $account->isExpired() ? 'subscription_expired' : 'subscription_suspended',
                'message'      => $account->isExpired()
                    ? 'انتهت صلاحية اشتراكك في خدمة الواتساب. يرجى تجديد الاشتراك مع الإدارة.'
                    : 'تم إيقاف اشتراكك مؤقتاً من قبل الإدارة.',
                'subscription' => $summary,
            ], 403);
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

        $result = $this->whatsappService->sendMessage((string) $phone, (string) $message, 'api', $account);

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
            'status'    => $result['error_code'] ?? 'failed',
            'message'   => $result['message'],
            'recipient' => $phone,
            'timestamp' => now()->toIso8601String(),
        ], 400);
    }

    /**
     * إرسال كود التحقق OTP لتطبيق نجاح ريسنج عبر الحساب الرئيسي للـ OTP
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

        $primaryAccount = WhatsAppSetting::getPrimaryForOtp();
        $formattedMessage = "رمز التحقق (OTP) الخاص بك في تطبيق نجاح ريسنج هو: {$cleanCode}\nلا تشارك هذا الرمز مع أي شخص.";

        $result = $this->whatsappService->sendMessage((string) $phone, $formattedMessage, 'otp', $primaryAccount);

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
     * الاستعلام عن حالة اتصال الواتساب وتفاصيل الاشتراك برمجياً عبر الـ API
     *
     * GET /api/whatsapp/status?apikey={KEY}
     */
    public function status(Request $request)
    {
        $account = $this->resolveAccountByApiKey($request);

        if (!$account) {
            return response()->json([
                'success' => false,
                'status'  => 'unauthorized',
                'message' => 'مفتاح API غير صالح أو مفقود.',
            ], 401);
        }

        $liveStatus = $this->whatsappService->getStatus($account);
        $summary = $account->getSubscriptionSummary();

        return response()->json([
            'success'         => true,
            'is_connected'    => (bool) ($liveStatus['is_connected'] ?? false),
            'status'          => $liveStatus['status'] ?? 'unknown',
            'connected_phone' => $liveStatus['phone'] ?? $account->connected_phone,
            'connected_name'  => $liveStatus['name'] ?? $account->connected_name,
            'engine'          => $account->engine,
            'subscription'    => $summary,
            'message'         => $liveStatus['message'] ?? '',
            'timestamp'       => now()->toIso8601String(),
        ]);
    }
}
