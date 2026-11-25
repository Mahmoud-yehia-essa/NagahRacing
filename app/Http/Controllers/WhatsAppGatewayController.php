<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppMessage;
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
     * التحقق من صلاحية الدخول للصفحة المستقلة باستخدام مفتاح الـ API الصادر من لوحة التحكم
     */
    protected function isAuthorized(Request $request): bool
    {
        $candidateKey = $request->header('x-api-key')
            ?? $request->query('apikey')
            ?? $request->input('apikey')
            ?? $request->session()->get(self::SESSION_KEY);

        if (!$this->whatsappService->isValidApiKey($candidateKey)) {
            return false;
        }

        // تخزين المفتاح الصحيح في الجلسة للاستخدام المستمر في الصفحة المستقلة
        if ($request->session()->get(self::SESSION_KEY) !== trim((string) $candidateKey)) {
            $request->session()->put(self::SESSION_KEY, trim((string) $candidateKey));
        }

        return true;
    }

    /**
     * رد موحّد لرفض الوصول غير المصرح به
     */
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

    /**
     * الصفحة المستقلة لبوابة الواتساب (خارج لوحة التحكم)
     */
    public function index(Request $request)
    {
        // السماح بالدخول المباشر عبر ?apikey=... ثم تنظيف الرابط لإخفاء المفتاح من شريط العنوان
        if ($request->filled('apikey')) {
            if ($this->whatsappService->isValidApiKey($request->query('apikey'))) {
                $request->session()->put(self::SESSION_KEY, trim((string) $request->query('apikey')));
                return redirect()->route('whatsapp.gateway.index');
            }

            return view('whatsapp_gateway.gate', [
                'gateError' => 'مفتاح الـ API المدخل غير صحيح. يرجى الحصول على المفتاح الصحيح من لوحة التحكم.',
            ]);
        }

        if (!$this->isAuthorized($request)) {
            return view('whatsapp_gateway.gate', [
                'gateError' => session('gate_error'),
            ]);
        }

        $settings = $this->whatsappService->getSettings();
        $status = $this->whatsappService->getStatus();

        $messages = WhatsAppMessage::latest()->paginate(15);

        $totalSent = WhatsAppMessage::where('status', 'sent')->count();
        $totalFailed = WhatsAppMessage::where('status', 'failed')->count();
        $todaySent = WhatsAppMessage::where('status', 'sent')->whereDate('created_at', today())->count();

        return view('whatsapp_gateway.index', compact(
            'settings',
            'status',
            'messages',
            'totalSent',
            'totalFailed',
            'todaySent'
        ));
    }

    /**
     * التحقق من مفتاح الـ API المدخل في بوابة الدخول للصفحة المستقلة
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string',
        ], [
            'api_key.required' => 'يرجى إدخال مفتاح الـ API الصادر من لوحة التحكم.',
        ]);

        if (!$this->whatsappService->isValidApiKey($request->input('api_key'))) {
            return redirect()->route('whatsapp.gateway.index')->with([
                'gate_error' => 'مفتاح الـ API غير صحيح. تأكد من نسخه بدقة من لوحة التحكم.',
            ]);
        }

        $request->session()->put(self::SESSION_KEY, trim((string) $request->input('api_key')));

        return redirect()->route('whatsapp.gateway.index');
    }

    /**
     * قفل الصفحة المستقلة (تسجيل الخروج من بوابة الواتساب)
     */
    public function lockGate(Request $request)
    {
        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('whatsapp.gateway.index')->with([
            'gate_success' => 'تم قفل بوابة الواتساب بنجاح.',
        ]);
    }

    /**
     * فحص الحالة الحية بشكل متكرر عبر AJAX
     */
    public function ajaxStatus(Request $request)
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorizedResponse($request);
        }

        return response()->json($this->whatsappService->getStatus());
    }

    /**
     * تشغيل خادم الواتساب يدوياً
     */
    public function startService(Request $request)
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorizedResponse($request);
        }

        $result = $this->whatsappService->startLocalService();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->back()->with([
            'message'    => $result['message'],
            'alert-type' => $result['success'] ? 'success' : 'error',
            'active_tab' => 'connect',
        ]);
    }

    /**
     * طلب كود الاقتران السريع (Pairing Code)
     */
    public function requestPairingCode(Request $request)
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorizedResponse($request);
        }

        $request->validate([
            'phone' => 'required|string',
        ], [
            'phone.required' => 'يرجى إدخال رقم الهاتف مع كود الدولة.',
        ]);

        return response()->json($this->whatsappService->requestPairingCode($request->phone));
    }

    /**
     * إرسال رسالة تجريبية من الصفحة المستقلة
     */
    public function sendMessage(Request $request)
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorizedResponse($request);
        }

        $request->validate([
            'phone'   => 'required|string',
            'message' => 'required|string',
        ], [
            'phone.required'   => 'يرجى إدخال رقم هاتف المستلم مع رمز الدولة.',
            'message.required' => 'يرجى كتابة نص الرسالة.',
        ]);

        $result = $this->whatsappService->sendMessage($request->phone, $request->message, 'gateway');

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

    /**
     * قطع الاتصال وفك الارتباط
     */
    public function disconnect(Request $request)
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorizedResponse($request);
        }

        $result = $this->whatsappService->disconnect();

        return redirect()->back()->with([
            'message'    => $result['message'],
            'alert-type' => 'success',
            'active_tab' => 'connect',
        ]);
    }

    /**
     * تحديث إعدادات المحرك
     */
    public function updateSettings(Request $request)
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorizedResponse($request);
        }

        $request->validate([
            'engine'            => 'required|in:local,textmebot',
            'local_server_url'  => 'nullable|url',
            'textmebot_api_key' => 'nullable|string',
            'textmebot_phone'   => 'nullable|string',
        ]);

        $settings = $this->whatsappService->getSettings();
        $settings->update([
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

    /**
     * مسح سجل الرسائل القديمة
     */
    public function clearLogs(Request $request)
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorizedResponse($request);
        }

        WhatsAppMessage::truncate();

        return redirect()->back()->with([
            'message'    => 'تم مسح سجل رسائل الواتساب بالكامل.',
            'alert-type' => 'success',
            'active_tab' => 'logs',
        ]);
    }
}
