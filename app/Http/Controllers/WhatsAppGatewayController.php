<?php

namespace App\Http\Controllers;

use App\Models\AppVersion;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppSetting;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppGatewayController extends Controller
{
    protected const SESSION_KEY = 'nagah_whatsapp_gateway_api_key';

    protected WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * التحقق من مفتاح الـ API وجلب حساب المشترك المطابق له بالضبط
     */
    protected function resolveAuthorizedAccount(Request $request): ?WhatsAppSetting
    {
        $candidateKey = $request->header('x-api-key')
            ?? $request->query('apikey')
            ?? $request->input('apikey')
            ?? $request->session()->get(self::SESSION_KEY);

        $account = $this->whatsappService->findAccountByApiKey($candidateKey);
        if (!$account) {
            return null;
        }

        if ($request->session()->get(self::SESSION_KEY) !== $account->api_key) {
            $request->session()->put(self::SESSION_KEY, $account->api_key);
        }

        return $account;
    }

    protected function unauthorizedResponse(Request $request)
    {
        $request->session()->forget(self::SESSION_KEY);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'status'  => 'unauthorized',
                'message' => 'انتهت صلاحية الجلسة أو تم تغيير مفتاح الـ API من لوحة التحكم. يرجى إدخال المفتاح الصحيح.',
            ], 401);
        }

        return redirect()->route('whatsapp.gateway.index')->with([
            'gate_error' => 'مفتاح الـ API غير صالح أو تم تجديده من لوحة التحكم. يرجى إدخال المفتاح الصحيح.',
        ]);
    }

    protected function subscriptionBlockedResponse(Request $request, WhatsAppSetting $account)
    {
        $msg = $account->isExpired()
            ? 'انتهت صلاحية اشتراكك في خدمة الواتساب. يرجى التواصل مع الإدارة لتجديد الاشتراك.'
            : 'تم إيقاف اشتراكك مؤقتاً من قبل الإدارة. يرجى التواصل مع الإدارة.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'status'  => $account->isExpired() ? 'expired' : 'suspended',
                'message' => $msg,
            ], 403);
        }

        return redirect()->back()->with([
            'message'    => $msg,
            'alert-type' => 'error',
        ]);
    }

    /**
     * الصفحة المستقلة لبوابة الواتساب (خارج لوحة التحكم) — معزولة بالكامل لكل مشترك
     */
    public function index(Request $request)
    {
        if ($request->filled('apikey')) {
            $account = $this->whatsappService->findAccountByApiKey($request->query('apikey'));
            if ($account) {
                $request->session()->put(self::SESSION_KEY, $account->api_key);
                return redirect()->route('whatsapp.gateway.index');
            }

            return view('whatsapp_gateway.gate', [
                'gateError' => 'مفتاح الـ API المدخل غير صحيح. يرجى الحصول على المفتاح الصحيح من لوحة التحكم.',
            ]);
        }

        $settings = $this->resolveAuthorizedAccount($request);
        if (!$settings) {
            return view('whatsapp_gateway.gate', [
                'gateError' => session('gate_error'),
            ]);
        }

        $subscription = $settings->getSubscriptionSummary();
        $status = $this->whatsappService->getStatus($settings);

        $messages = WhatsAppMessage::where('whatsapp_setting_id', $settings->id)
            ->latest()
            ->paginate(15);

        $totalSent = WhatsAppMessage::where('whatsapp_setting_id', $settings->id)
            ->where('status', 'sent')
            ->count();
        $totalFailed = WhatsAppMessage::where('whatsapp_setting_id', $settings->id)
            ->where('status', 'failed')
            ->count();
        $todaySent = WhatsAppMessage::where('whatsapp_setting_id', $settings->id)
            ->where('status', 'sent')
            ->whereDate('created_at', today())
            ->count();

        $adminContact = AppVersion::getOwnerInactivePayload();

        return view('whatsapp_gateway.index', compact(
            'settings',
            'subscription',
            'status',
            'messages',
            'totalSent',
            'totalFailed',
            'todaySent',
            'adminContact'
        ));
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string',
        ], [
            'api_key.required' => 'يرجى إدخال مفتاح الـ API الصادر من لوحة التحكم.',
        ]);

        $account = $this->whatsappService->findAccountByApiKey($request->input('api_key'));
        if (!$account) {
            return redirect()->route('whatsapp.gateway.index')->with([
                'gate_error' => 'مفتاح الـ API غير صحيح. تأكد من نسخه بدقة من الإدارة.',
            ]);
        }

        $request->session()->put(self::SESSION_KEY, $account->api_key);

        return redirect()->route('whatsapp.gateway.index');
    }

    public function lockGate(Request $request)
    {
        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('whatsapp.gateway.index')->with([
            'gate_success' => 'تم قفل بوابة الواتساب بنجاح.',
        ]);
    }

    public function ajaxStatus(Request $request)
    {
        $account = $this->resolveAuthorizedAccount($request);
        if (!$account) {
            return $this->unauthorizedResponse($request);
        }

        $status = $this->whatsappService->getStatus($account);
        $status['subscription'] = $account->getSubscriptionSummary();

        return response()->json($status);
    }

    public function startService(Request $request)
    {
        $account = $this->resolveAuthorizedAccount($request);
        if (!$account) {
            return $this->unauthorizedResponse($request);
        }
        if (!$account->isUsable()) {
            return $this->subscriptionBlockedResponse($request, $account);
        }

        $result = $this->whatsappService->startLocalService($account);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->back()->with([
            'message'    => $result['message'],
            'alert-type' => $result['success'] ? 'success' : 'error',
            'active_tab' => 'connect',
        ]);
    }

    public function requestPairingCode(Request $request)
    {
        $account = $this->resolveAuthorizedAccount($request);
        if (!$account) {
            return $this->unauthorizedResponse($request);
        }
        if (!$account->isUsable()) {
            return $this->subscriptionBlockedResponse($request, $account);
        }

        $request->validate([
            'phone' => 'required|string',
        ], [
            'phone.required' => 'يرجى إدخال رقم الهاتف مع كود الدولة.',
        ]);

        return response()->json($this->whatsappService->requestPairingCode($request->phone, $account));
    }

    public function sendMessage(Request $request)
    {
        $account = $this->resolveAuthorizedAccount($request);
        if (!$account) {
            return $this->unauthorizedResponse($request);
        }
        if (!$account->isUsable()) {
            return $this->subscriptionBlockedResponse($request, $account);
        }

        $request->validate([
            'phone'   => 'required|string',
            'message' => 'required|string',
        ], [
            'phone.required'   => 'يرجى إدخال رقم هاتف المستلم مع رمز الدولة.',
            'message.required' => 'يرجى كتابة نص الرسالة.',
        ]);

        $result = $this->whatsappService->sendMessage($request->phone, $request->message, 'gateway', $account);

        if ($result['success']) {
            return redirect()->back()->with([
                'message'    => $result['message'],
                'alert-type' => 'success',
                'active_tab' => 'send_message',
            ]);
        }

        return redirect()->back()->with([
            'message'    => 'خطأ أثناء الإرسال: ' . $result['message'],
            'alert-type' => 'error',
            'active_tab' => 'send_message',
        ])->withInput();
    }

    public function disconnect(Request $request)
    {
        $account = $this->resolveAuthorizedAccount($request);
        if (!$account) {
            return $this->unauthorizedResponse($request);
        }

        $result = $this->whatsappService->disconnect($account);

        return redirect()->back()->with([
            'message'    => $result['message'],
            'alert-type' => 'success',
            'active_tab' => 'connect',
        ]);
    }

    public function updateSettings(Request $request)
    {
        $account = $this->resolveAuthorizedAccount($request);
        if (!$account) {
            return $this->unauthorizedResponse($request);
        }
        if (!$account->is_primary_otp) {
            return redirect()->back()->with([
                'message'    => 'إعدادات المحرك متاحة للحساب الرئيسي فقط.',
                'alert-type' => 'warning',
            ]);
        }

        $request->validate([
            'engine'            => 'required|in:local,textmebot',
            'local_server_url'  => 'nullable|url',
            'textmebot_api_key' => 'nullable|string',
            'textmebot_phone'   => 'nullable|string',
        ]);

        $account->update([
            'engine'            => $request->engine,
            'local_server_url'  => $request->local_server_url ?: WhatsAppService::DEFAULT_LOCAL_URL,
            'textmebot_api_key' => $request->textmebot_api_key,
            'textmebot_phone'   => $request->textmebot_phone,
        ]);

        return redirect()->back()->with([
            'message'    => 'تم حفظ إعدادات محرك الواتساب بنجاح.',
            'alert-type' => 'success',
            'active_tab' => 'settings',
        ]);
    }

    public function clearLogs(Request $request)
    {
        $account = $this->resolveAuthorizedAccount($request);
        if (!$account) {
            return $this->unauthorizedResponse($request);
        }

        WhatsAppMessage::where('whatsapp_setting_id', $account->id)->delete();

        return redirect()->back()->with([
            'message'    => 'تم مسح سجل رسائل حسابك بنجاح.',
            'alert-type' => 'success',
            'active_tab' => 'logs',
        ]);
    }
}
