<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppMessage;
use App\Models\WhatsAppSetting;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminWhatsAppApiController extends Controller
{
    protected WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * صفحة إدارة اشتراكات ومفاتيح API لبوابة الواتساب داخل لوحة التحكم
     */
    public function index()
    {
        // ضمان وجود الحساب الرئيسي لـ OTP دائماً
        WhatsAppSetting::getPrimaryForOtp();

        $accounts = WhatsAppSetting::orderByDesc('is_primary_otp')
            ->latest()
            ->get();

        $gatewayUrl = route('whatsapp.gateway.index');

        $totalAccounts = $accounts->count();
        $activeAccounts = $accounts->filter(fn (WhatsAppSetting $a) => $a->isUsable())->count();
        $connectedAccounts = $accounts->where('is_connected', true)->count();
        $totalActiveRevenue = $accounts
            ->filter(fn (WhatsAppSetting $a) => $a->isUsable())
            ->sum(fn (WhatsAppSetting $a) => (float) $a->subscription_price);

        return view('admin.whatsapp.api_key', compact(
            'accounts',
            'gatewayUrl',
            'totalAccounts',
            'activeAccounts',
            'connectedAccounts',
            'totalActiveRevenue'
        ));
    }

    /**
     * إنشاء اشتراك جديد بمفتاح API مستقل وجلسة QR Code مستقلة
     */
    public function store(Request $request)
    {
        $request->validate([
            'label'              => 'required|string|max:150',
            'plan_type'          => 'required|in:monthly,yearly',
            'subscription_price' => 'nullable|numeric|min:0|max:99999',
            'expires_at'         => 'nullable|date',
            'notes'              => 'nullable|string|max:500',
        ], [
            'label.required'     => 'يرجى إدخال اسم المشترك أو الحساب.',
            'plan_type.required' => 'يرجى اختيار نوع الاشتراك (شهري أو سنوي).',
            'plan_type.in'       => 'نوع الاشتراك يجب أن يكون شهري أو سنوي.',
        ]);

        $planType = $request->input('plan_type');
        $price = $request->filled('subscription_price')
            ? (float) $request->input('subscription_price')
            : WhatsAppSetting::defaultPriceForPlan($planType);

        $startsAt = now();
        $expiresAt = $request->filled('expires_at')
            ? Carbon::parse($request->input('expires_at'))->endOfDay()
            : ($planType === WhatsAppSetting::PLAN_MONTHLY ? now()->addMonth() : now()->addYear());

        $primary = WhatsAppSetting::getPrimaryForOtp();

        $account = WhatsAppSetting::create([
            'label'              => trim($request->input('label')),
            'session_id'         => WhatsAppSetting::generateUniqueSessionId(),
            'engine'             => 'local',
            'api_key'            => WhatsAppSetting::generateUniqueApiKey(),
            'plan_type'          => $planType,
            'subscription_price' => $price,
            'currency'           => 'USD',
            'starts_at'          => $startsAt,
            'expires_at'         => $expiresAt,
            'is_active'          => true,
            'is_primary_otp'     => false,
            'local_server_url'   => $primary->local_server_url ?: WhatsAppService::DEFAULT_LOCAL_URL,
            'is_connected'       => false,
            'notes'              => trim((string) ($request->input('notes') ?? '')),
        ]);

        return redirect()->back()->with([
            'message'    => "تم إنشاء اشتراك ({$account->label}) ومفتاح الـ API المستقل بنجاح!",
            'alert-type' => 'success',
        ]);
    }

    /**
     * تعديل بيانات وصلاحية وسعر اشتراك محدد
     */
    public function update(Request $request, $id)
    {
        $account = WhatsAppSetting::findOrFail($id);

        $request->validate([
            'label'              => 'required|string|max:150',
            'plan_type'          => 'required|in:monthly,yearly',
            'subscription_price' => 'required|numeric|min:0|max:99999',
            'expires_at'         => 'required|date',
            'notes'              => 'nullable|string|max:500',
        ], [
            'label.required'      => 'يرجى إدخال اسم المشترك.',
            'expires_at.required' => 'يرجى تحديد تاريخ انتهاء الصلاحية.',
        ]);

        $account->update([
            'label'              => trim($request->input('label')),
            'plan_type'          => $request->input('plan_type'),
            'subscription_price' => (float) $request->input('subscription_price'),
            'expires_at'         => Carbon::parse($request->input('expires_at'))->endOfDay(),
            'notes'              => trim((string) ($request->input('notes') ?? '')),
        ]);

        return redirect()->back()->with([
            'message'    => "تم تحديث بيانات اشتراك ({$account->label}) بنجاح.",
            'alert-type' => 'success',
        ]);
    }

    /**
     * تجديد سريع للاشتراك (+شهر بـ 7$ أو +سنة بـ 70$)
     */
    public function renew(Request $request, $id)
    {
        $account = WhatsAppSetting::findOrFail($id);

        $request->validate([
            'plan_type' => 'required|in:monthly,yearly',
        ]);

        $planType = $request->input('plan_type');
        $price = WhatsAppSetting::defaultPriceForPlan($planType);
        $baseDate = ($account->expires_at && $account->expires_at->isFuture())
            ? $account->expires_at->copy()
            : now();

        $newExpiresAt = $planType === WhatsAppSetting::PLAN_MONTHLY
            ? $baseDate->addMonth()
            : $baseDate->addYear();

        $account->update([
            'plan_type'          => $planType,
            'subscription_price' => $price,
            'starts_at'          => $account->isExpired() ? now() : ($account->starts_at ?: now()),
            'expires_at'         => $newExpiresAt,
            'is_active'          => true,
        ]);

        $planArabic = $planType === WhatsAppSetting::PLAN_MONTHLY ? 'شهر ($7)' : 'سنة ($70)';

        return redirect()->back()->with([
            'message'    => "تم تجديد اشتراك ({$account->label}) لمدة {$planArabic} حتى {$newExpiresAt->format('Y-m-d')} بنجاح.",
            'alert-type' => 'success',
        ]);
    }

    /**
     * تفعيل / إيقاف اشتراك محدد مؤقتاً
     */
    public function toggleStatus($id)
    {
        $account = WhatsAppSetting::findOrFail($id);
        $account->update(['is_active' => !$account->is_active]);

        return redirect()->back()->with([
            'message'    => $account->is_active
                ? "تم تفعيل اشتراك ({$account->label}) بنجاح."
                : "تم إيقاف اشتراك ({$account->label}) مؤقتاً.",
            'alert-type' => 'success',
        ]);
    }

    /**
     * إعادة توليد مفتاح API جديد لحساب محدد وإلغاء المفتاح السابق فوراً
     */
    public function regenerateApiKey($id = null)
    {
        $account = $id ? WhatsAppSetting::findOrFail($id) : WhatsAppSetting::getPrimaryForOtp();
        $newKey = $this->whatsappService->regenerateApiKey($account);

        return redirect()->back()->with([
            'message'    => "تم توليد مفتاح API جديد لحساب ({$account->label}) وإلغاء القديم: {$newKey}",
            'alert-type' => 'success',
        ]);
    }

    /**
     * تعيين الحساب كحساب رئيسي لإرسال OTP تطبيق نجاح ريسنج
     */
    public function setPrimary($id)
    {
        $account = WhatsAppSetting::findOrFail($id);

        WhatsAppSetting::query()->update(['is_primary_otp' => false]);
        $account->update(['is_primary_otp' => true, 'is_active' => true]);

        return redirect()->back()->with([
            'message'    => "تم تعيين ({$account->label}) كحساب رئيسي لإرسال رموز OTP لتطبيق نجاح ريسنج.",
            'alert-type' => 'success',
        ]);
    }

    /**
     * حذف اشتراك وفصل جلسته المستقلة من محرك Node.js
     */
    public function destroy($id)
    {
        $account = WhatsAppSetting::findOrFail($id);

        if ($account->is_primary_otp) {
            return redirect()->back()->with([
                'message'    => 'لا يمكن حذف الحساب الرئيسي لـ OTP التطبيق. قم بتعيين حساب آخر كرئيسي أولاً.',
                'alert-type' => 'error',
            ]);
        }

        $label = $account->label;
        $this->whatsappService->deleteSession($account);
        WhatsAppMessage::where('whatsapp_setting_id', $account->id)->delete();
        $account->delete();

        return redirect()->back()->with([
            'message'    => "تم حذف اشتراك ({$label}) وفصل جلسته المستقلة بنجاح.",
            'alert-type' => 'success',
        ]);
    }
}
