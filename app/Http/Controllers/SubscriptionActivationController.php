<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionActivationCode;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionSetting;
use App\Models\User;
use App\Services\SubscriptionActivationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class SubscriptionActivationController extends Controller
{
    public function __construct(
        private readonly SubscriptionActivationService $activationService
    ) {}

    // =========================================================================
    // ADMIN WEB PANEL: SETTINGS
    // =========================================================================

    public function settings()
    {
        $settings = SubscriptionSetting::current();
        return view('admin.subscription_activation.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'admin_whatsapp' => 'nullable|string|max:30',
            'admin_phone' => 'nullable|string|max:30',
            'whatsapp_message_template' => 'nullable|string|max:1500',
            'support_note' => 'nullable|string|max:1500',
            'currency_label' => 'required|string|max:20',
        ]);

        $settings = SubscriptionSetting::current();
        $data = [
            'admin_whatsapp' => trim((string) $request->input('admin_whatsapp', '')),
            'admin_phone' => trim((string) $request->input('admin_phone', '')),
            'whatsapp_message_template' => trim((string) $request->input('whatsapp_message_template', '')),
            'support_note' => trim((string) $request->input('support_note', '')),
            'currency_label' => trim((string) $request->input('currency_label', 'ر.س')),
            'enable_activation_codes' => $request->has('enable_activation_codes'),
            'show_whatsapp_on_ios' => $request->has('show_whatsapp_on_ios'),
            'show_whatsapp_on_android' => $request->has('show_whatsapp_on_android'),
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn('subscription_settings', 'show_subscriptions_in_app')) {
            $data['show_subscriptions_in_app'] = $request->has('show_subscriptions_in_app');
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('subscription_settings', 'show_subscriptions_on_ios')) {
            $data['show_subscriptions_on_ios'] = $request->has('show_subscriptions_on_ios');
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('subscription_settings', 'show_subscriptions_on_android')) {
            $data['show_subscriptions_on_android'] = $request->has('show_subscriptions_on_android');
        }

        $settings->update($data);

        return redirect()->route('admin.subscription.settings')->with([
            'message' => 'تم حفظ إعدادات الباقات والتواصل والتفعيل بنجاح',
            'alert-type' => 'success',
        ]);
    }

    /**
     * Quick 1-click toggle for showing/hiding subscription packages in the mobile app (All / iOS / Android).
     */
    public function toggleAppVisibility(Request $request)
    {
        $request->validate([
            'target' => 'required|in:all,ios,android',
        ]);

        $columnMap = [
            'all' => 'show_subscriptions_in_app',
            'ios' => 'show_subscriptions_on_ios',
            'android' => 'show_subscriptions_on_android',
        ];
        $labelMap = [
            'all' => 'في التطبيق بالكامل',
            'ios' => 'على أجهزة iOS (آيفون)',
            'android' => 'على أجهزة Android',
        ];

        $target = $request->input('target');
        $col = $columnMap[$target];

        if (!\Illuminate\Support\Facades\Schema::hasColumn('subscription_settings', $col)) {
            return redirect()->back()->with([
                'message' => 'يرجى تنفيذ تحديث قاعدة البيانات (Migration/SQL) أولاً لتفعيل أعمدة إخفاء وإظهار الباقات.',
                'alert-type' => 'warning',
            ]);
        }

        $settings = SubscriptionSetting::current();
        $newState = !$settings->{$col};
        $settings->update([$col => $newState]);

        $stateLabel = $newState ? 'إظهار الباقات والاشتراكات' : 'إخفاء الباقات والاشتراكات';

        return redirect()->back()->with([
            'message' => "تم {$stateLabel} {$labelMap[$target]} بنجاح.",
            'alert-type' => 'success',
        ]);
    }

    // =========================================================================
    // ADMIN WEB PANEL: ACTIVATION CODES
    // =========================================================================

    public function codesIndex(Request $request)
    {
        // Auto-mark date-expired active codes
        SubscriptionActivationCode::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        $owners = User::where('role', 'owner')->latest()->get();
        $plans = SubscriptionPlan::where('status', 'active')->latest()->get();
        $settings = SubscriptionSetting::current();
        $paymentMethods = SubscriptionActivationCode::PAYMENT_METHODS;

        $query = SubscriptionActivationCode::with(['plan', 'assignedOwner', 'usedByOwner', 'creator', 'invoice']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('owner_id')) {
            $ownerId = $request->owner_id;
            $query->where(function ($q) use ($ownerId) {
                $q->where('owner_id', $ownerId)
                  ->orWhere('used_by_owner_id', $ownerId);
            });
        }
        if ($request->filled('code_type')) {
            $query->where('code_type', $request->code_type);
        }
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('payment_reference', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $codes = $query->latest()->get();

        $stats = [
            'total' => SubscriptionActivationCode::count(),
            'active' => SubscriptionActivationCode::where('status', 'active')->count(),
            'used' => SubscriptionActivationCode::where('status', 'used')->count(),
            'revenue' => SubscriptionActivationCode::where('status', 'used')->sum('amount_paid'),
        ];

        return view('admin.subscription_activation.codes', compact(
            'codes',
            'owners',
            'plans',
            'settings',
            'paymentMethods',
            'stats'
        ));
    }

    public function generateCodes(Request $request)
    {
        $request->validate([
            'code_type' => 'required|in:plan,custom_addon,plan_plus_addon',
            'subscription_plan_id' => 'nullable|exists:subscription_plans,id',
            'owner_id' => 'nullable|exists:users,id',
            'extra_days' => 'nullable|integer|min:0|max:3650',
            'extra_training_sessions' => 'nullable|integer|min:0|max:100000',
            'extra_sub_users' => 'nullable|integer|min:0|max:1000',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:60',
            'payment_reference' => 'nullable|string|max:120',
            'expires_at' => 'nullable|date|after:now',
            'quantity' => 'nullable|integer|min:1|max:20',
            'notes' => 'nullable|string|max:1000',
        ], [
            'code_type.required' => 'يرجى تحديد نوع الكود.',
            'expires_at.after' => 'تاريخ انتهاء صلاحية الكود يجب أن يكون في المستقبل.',
        ]);

        $codeType = $request->code_type;
        $planId = $request->filled('subscription_plan_id') ? (int) $request->subscription_plan_id : null;
        $extraDays = (int) $request->input('extra_days', 0);
        $extraSessions = (int) $request->input('extra_training_sessions', 0);
        $extraSubUsers = (int) $request->input('extra_sub_users', 0);

        if (in_array($codeType, ['plan', 'plan_plus_addon'], true) && !$planId) {
            return redirect()->back()->withInput()->with([
                'message' => 'يرجى اختيار الباقة المطلوب تفعيلها لهذا النوع من الأكواد.',
                'alert-type' => 'error',
            ]);
        }

        if ($codeType === 'custom_addon' && $extraDays === 0 && $extraSessions === 0 && $extraSubUsers === 0) {
            return redirect()->back()->withInput()->with([
                'message' => 'يرجى تحديد قيمة واحدة على الأقل (أيام إضافية أو جلسات تدريب أو مستخدمين فرعيين).',
                'alert-type' => 'error',
            ]);
        }

        $plan = $planId ? SubscriptionPlan::find($planId) : null;
        $amountPaid = $request->filled('amount_paid')
            ? (float) $request->amount_paid
            : ($plan ? (float) $plan->price : 0.00);

        $quantity = $request->filled('owner_id') ? 1 : max(1, min(20, (int) $request->input('quantity', 1)));
        $expiresAt = $request->filled('expires_at') ? Carbon::parse($request->expires_at)->endOfDay() : null;

        $generatedCodes = [];
        for ($i = 0; $i < $quantity; $i++) {
            $code = SubscriptionActivationCode::create([
                'code' => SubscriptionActivationCode::generateUniqueCode('NGH'),
                'code_type' => $codeType,
                'subscription_plan_id' => $codeType === 'custom_addon' ? null : $planId,
                'owner_id' => $request->filled('owner_id') ? (int) $request->owner_id : null,
                'extra_days' => $codeType === 'plan' ? 0 : $extraDays,
                'extra_training_sessions' => $codeType === 'plan' ? 0 : $extraSessions,
                'extra_sub_users' => $codeType === 'plan' ? 0 : $extraSubUsers,
                'amount_paid' => $amountPaid,
                'payment_method' => $request->input('payment_method', 'bank_transfer'),
                'payment_reference' => $request->input('payment_reference'),
                'notes' => $request->input('notes'),
                'status' => 'active',
                'expires_at' => $expiresAt,
                'created_by_admin_id' => Auth::id(),
            ]);
            $generatedCodes[] = $code->code;
        }

        $msg = count($generatedCodes) === 1
            ? 'تم توليد كود التفعيل بنجاح: ' . $generatedCodes[0]
            : 'تم توليد عدد ' . count($generatedCodes) . ' كود تفعيل بنجاح.';

        return redirect()->route('admin.subscription.codes')->with([
            'message' => $msg,
            'alert-type' => 'success',
            'last_generated_code' => $generatedCodes[0] ?? null,
        ]);
    }

    public function revokeCode($id)
    {
        $code = SubscriptionActivationCode::findOrFail($id);
        if ($code->status === 'used') {
            return redirect()->back()->with([
                'message' => 'لا يمكن إلغاء كود تم استخدامه بالفعل.',
                'alert-type' => 'error',
            ]);
        }

        $code->update(['status' => 'revoked']);

        return redirect()->back()->with([
            'message' => 'تم إلغاء كود التفعيل بنجاح.',
            'alert-type' => 'success',
        ]);
    }

    public function deleteCode($id)
    {
        $code = SubscriptionActivationCode::findOrFail($id);
        if ($code->status === 'used') {
            return redirect()->back()->with([
                'message' => 'لا يمكن حذف كود مستخدم للحفاظ على سجل الفواتير والتدقيق.',
                'alert-type' => 'error',
            ]);
        }

        $code->delete();

        return redirect()->back()->with([
            'message' => 'تم حذف كود التفعيل بنجاح.',
            'alert-type' => 'success',
        ]);
    }

    // =========================================================================
    // ADMIN WEB PANEL: DIRECT ACTIVATION / TOP-UP
    // =========================================================================

    public function directActivate(Request $request)
    {
        $request->validate([
            'owner_id' => 'required|exists:users,id',
            'code_type' => 'required|in:plan,custom_addon,plan_plus_addon',
            'subscription_plan_id' => 'nullable|exists:subscription_plans,id',
            'extra_days' => 'nullable|integer|min:0|max:3650',
            'extra_training_sessions' => 'nullable|integer|min:0|max:100000',
            'extra_sub_users' => 'nullable|integer|min:0|max:1000',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:60',
            'payment_reference' => 'nullable|string|max:120',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $result = $this->activationService->directAdminActivate($request->all(), Auth::user());

            return redirect()->back()->with([
                'message' => $result['message'],
                'alert-type' => 'success',
            ]);
        } catch (ValidationException $e) {
            return redirect()->back()->withInput()->withErrors($e->errors());
        }
    }

    // =========================================================================
    // ADMIN WEB PANEL: INVOICES & PAYMENT HISTORY
    // =========================================================================

    public function invoicesIndex(Request $request)
    {
        $owners = User::where('role', 'owner')->latest()->get();
        $plans = SubscriptionPlan::where('status', 'active')->latest()->get();
        $settings = SubscriptionSetting::current();
        $paymentMethods = SubscriptionActivationCode::PAYMENT_METHODS;
        $actionLabels = SubscriptionInvoice::ACTION_LABELS;

        $query = SubscriptionInvoice::with(['owner', 'plan', 'activationCode', 'issuedByAdmin']);

        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }
        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('issued_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('issued_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('activation_code_used', 'like', "%{$search}%")
                  ->orWhere('payment_reference', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $invoices = $query->latest('issued_at')->latest('id')->get();

        $stats = [
            'total_count' => $invoices->count(),
            'confirmed_count' => $invoices->where('status', 'confirmed')->count(),
            'total_revenue' => $invoices->where('status', 'confirmed')->sum('amount_paid'),
            'this_month_revenue' => SubscriptionInvoice::where('status', 'confirmed')
                ->whereMonth('issued_at', now()->month)
                ->whereYear('issued_at', now()->year)
                ->sum('amount_paid'),
        ];

        return view('admin.subscription_activation.invoices', compact(
            'invoices',
            'owners',
            'plans',
            'settings',
            'paymentMethods',
            'actionLabels',
            'stats'
        ));
    }

    public function showInvoice($id)
    {
        $invoice = SubscriptionInvoice::with(['owner', 'plan', 'subscription', 'activationCode', 'issuedByAdmin'])
            ->findOrFail($id);
        $settings = SubscriptionSetting::current();

        return view('admin.subscription_activation.invoice_show', compact('invoice', 'settings'));
    }

    // =========================================================================
    // OWNER MOBILE APP APIs
    // =========================================================================

    public function getActivationConfigApi(Request $request)
    {
        $settings = SubscriptionSetting::current();

        return response()->json([
            'success' => true,
            'config' => $settings->toApiArray(),
        ], 200);
    }

    public function redeemCodeApi(Request $request)
    {
        $request->validate([
            'owner_id' => 'required|exists:users,id',
            'code' => 'required|string|max:40',
        ], [
            'owner_id.required' => 'معرف المالك مطلوب.',
            'owner_id.exists' => 'المالك غير موجود.',
            'code.required' => 'يرجى إدخال كود التفعيل.',
        ]);

        $owner = User::find($request->owner_id);
        if (!$owner || $owner->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'معرف المالك غير صالح.',
            ], 403);
        }

        $settings = SubscriptionSetting::current();
        if (!$settings->enable_activation_codes) {
            return response()->json([
                'success' => false,
                'message' => 'تفعيل الأكواد متوقف مؤقتاً من الإدارة، يرجى التواصل مع الدعم الفني.',
            ], 403);
        }

        // Anti-brute-force rate limit: max 5 failed code attempts per 15 minutes per owner
        $attemptsKey = 'redeem_code_failures_' . $owner->id;
        $failures = (int) Cache::get($attemptsKey, 0);
        if ($failures >= 5) {
            return response()->json([
                'success' => false,
                'message' => 'تم تجاوز الحد المسموح من المحاولات الخاطئة. يرجى المحاولة بعد 15 دقيقة أو التواصل مع الإدارة.',
            ], 429);
        }

        try {
            $result = $this->activationService->redeemCode($owner, (string) $request->code);
            Cache::forget($attemptsKey);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'subscription' => $result['subscription'],
                'invoice' => $result['invoice']->toApiArray(),
            ], 200);
        } catch (ValidationException $e) {
            Cache::put($attemptsKey, $failures + 1, now()->addMinutes(15));
            $errors = $e->errors();
            $firstMessage = collect($errors)->flatten()->first() ?? 'تعذر تفعيل الكود.';

            return response()->json([
                'success' => false,
                'message' => $firstMessage,
                'errors' => $errors,
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تفعيل الكود، يرجى المحاولة مرة أخرى.',
            ], 500);
        }
    }

    public function getOwnerInvoicesApi(Request $request)
    {
        $request->validate([
            'owner_id' => 'required|exists:users,id',
        ], [
            'owner_id.required' => 'معرف المالك مطلوب.',
            'owner_id.exists' => 'المالك غير موجود.',
        ]);

        $owner = User::find($request->owner_id);
        if (!$owner || $owner->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'معرف المالك غير صالح.',
            ], 403);
        }

        $settings = SubscriptionSetting::current();
        $invoices = SubscriptionInvoice::with(['plan'])
            ->where('owner_id', $owner->id)
            ->latest('issued_at')
            ->latest('id')
            ->get();

        $confirmed = $invoices->where('status', 'confirmed');

        return response()->json([
            'success' => true,
            'summary' => [
                'total_invoices' => $invoices->count(),
                'confirmed_invoices' => $confirmed->count(),
                'total_paid' => number_format((float) $confirmed->sum('amount_paid'), 2, '.', ''),
                'currency' => $settings->currency_label ?: 'ر.س',
                'total_days_added' => (int) $confirmed->sum('days_added'),
                'total_sessions_added' => (int) $confirmed->sum('training_sessions_added'),
            ],
            'invoices' => $invoices->map(fn (SubscriptionInvoice $inv) => $inv->toApiArray())->values(),
        ], 200);
    }
}
