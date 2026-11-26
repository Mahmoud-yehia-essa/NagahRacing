<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionSetting;
use App\Models\UserSubscription;
use App\Services\SubscriptionActivationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserSubscriptionController extends Controller
{
    /**
     * Display a listing of user subscriptions with filters.
     */
    public function index(Request $request)
    {
        $owners = User::where('role', 'owner')->latest()->get();
        $plans = SubscriptionPlan::latest()->get();

        $query = UserSubscription::with(['owner', 'plan']);

        // Filter by Owner
        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        // Filter by Plan
        if ($request->filled('subscription_plan_id')) {
            $query->where('subscription_plan_id', $request->subscription_plan_id);
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $subscriptions = $query->latest()->get()->map(function ($subscription) {
            // Count sub-users of this owner
            $subscription->sub_users_count = \App\Models\SubUser::where('owner_id', $subscription->owner_id)->count();

            // Count training sessions of workers of this owner since this subscription started
            $workerIds = \App\Models\CamelWorker::where('owner_id', $subscription->owner_id)->pluck('id');
            $sessionsQuery = \App\Models\TrainingSession::whereIn('camel_worker_id', $workerIds);
            if ($subscription->start_date) {
                $sessionsQuery->where('created_at', '>=', $subscription->start_date);
            }
            $subscription->training_sessions_count = $sessionsQuery->count();

            return $subscription;
        });

        $paymentMethods = \App\Models\SubscriptionActivationCode::PAYMENT_METHODS;
        $settings = SubscriptionSetting::current();
        $selectedOwner = $request->filled('owner_id')
            ? $owners->firstWhere('id', (int) $request->owner_id)
            : null;
        $selectedPlanId = $request->filled('plan_id')
            ? (int) $request->plan_id
            : ($request->filled('subscription_plan_id') ? (int) $request->subscription_plan_id : null);
        $autoOpenModal = $request->boolean('auto_open');

        return view('admin.user_subscription.all_subscriptions', compact(
            'owners',
            'plans',
            'subscriptions',
            'paymentMethods',
            'settings',
            'selectedOwner',
            'selectedPlanId',
            'autoOpenModal'
        ));
    }

    /**
     * Show the form for creating a new user subscription manually.
     */
    public function create()
    {
        $owners = User::where('role', 'owner')->latest()->get();
        $plans = SubscriptionPlan::where('status', 'active')->latest()->get();
        
        return view('admin.user_subscription.add_subscription', compact('owners', 'plans'));
    }

    /**
     * Store a newly created user subscription in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'owner_id' => 'required|exists:users,id',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'start_date' => 'required|date',
            'amount_paid' => 'required|numeric|min:0',
            'transaction_id' => 'nullable|string|max:255|unique:user_subscriptions,transaction_id',
            'status' => 'required|in:active,expired,canceled,pending_payment',
        ], [
            'owner_id.required' => 'يرجى اختيار المالك',
            'subscription_plan_id.required' => 'يرجى اختيار باقة الاشتراك',
            'start_date.required' => 'يرجى تحديد تاريخ بداية الاشتراك',
            'amount_paid.required' => 'يرجى إدخال المبلغ المدفوع',
            'transaction_id.unique' => 'رقم العملية (Transaction ID) مستخدم بالفعل.',
        ]);

        $plan = SubscriptionPlan::findOrFail($request->subscription_plan_id);
        
        // Calculate end date based on plan duration and interval
        $startDate = Carbon::parse($request->start_date);
        $duration = $plan->plan_duration;
        $interval = $plan->plan_interval;

        if ($duration == 0) {
            $endDate = null;
        } else {
            $endDate = clone $startDate;
            if ($interval === 'day') {
                $endDate->addDays($duration);
            } elseif ($interval === 'month') {
                $endDate->addMonths($duration);
            } elseif ($interval === 'year') {
                $endDate->addYears($duration);
            }
        }

        // Cancel any active subscriptions for this owner to ensure they don't have overlapping active plans
        if ($request->status === 'active') {
            UserSubscription::where('owner_id', $request->owner_id)
                ->where('status', 'active')
                ->update(['status' => 'expired']);
        }

        $transactionId = $request->transaction_id;
        if (empty($transactionId)) {
            $transactionId = $this->generateUniqueTransactionId();
        }

        $createdSub = UserSubscription::create([
            'owner_id' => $request->owner_id,
            'subscription_plan_id' => $request->subscription_plan_id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'amount_paid' => $request->amount_paid,
            'transaction_id' => $transactionId,
            'status' => $request->status,
            'payment_method' => 'تفعيل يدوي من الإدارة',
        ]);

        if ($request->status === 'active') {
            try {
                app(SubscriptionActivationService::class)->createInvoiceForSubscription(
                    $createdSub,
                    'admin_direct',
                    'تفعيل يدوي من الإدارة',
                    Auth::id()
                );
            } catch (\Throwable $e) {
            }
        }

        $notification = [
            'message' => 'تم تسجيل اشتراك المالك بنجاح وحساب تاريخ الانتهاء وإصدار فاتورة التأكيد',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.user.subscriptions')->with($notification);
    }

    /**
     * Cancel the specified subscription.
     */
    public function cancel($id)
    {
        $subscription = UserSubscription::findOrFail($id);
        $subscription->update([
            'status' => 'canceled'
        ]);

        $notification = [
            'message' => 'تم إلغاء الاشتراك المالي للمالك بنجاح',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.user.subscriptions')->with($notification);
    }

    /**
     * Remove the specified subscription.
     */
    public function destroy($id)
    {
        $subscription = UserSubscription::findOrFail($id);
        $subscription->delete();

        $notification = [
            'message' => 'تم حذف سجل الاشتراك بنجاح',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.user.subscriptions')->with($notification);
    }

    /**
     * API to fetch all subscriptions for a specific owner, calculating remaining duration.
     */
    public function getOwnerSubscriptionsApi(Request $request)
    {
        $request->validate([
            'owner_id' => 'required|exists:users,id',
        ], [
            'owner_id.required' => 'معرف المالك مطلوب.',
            'owner_id.exists'   => 'المالك غير موجود.',
        ]);

        // Check if the provided owner_id belongs to a user with the owner role
        $owner = User::find($request->owner_id);
        if (!$owner || $owner->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'The provided owner_id does not belong to a valid owner.'
            ], 403);
        }

        $now = Carbon::now();
        $settings = SubscriptionSetting::current();

        // Query all subscriptions for the owner, loading plan details
        $subscriptions = UserSubscription::with(['plan'])
            ->where('owner_id', $request->owner_id)
            ->latest()
            ->get()
            ->map(function ($subscription) use ($now) {
                $remainingDays = 0;
                $remainingDaysFormatted = 'منتهية'; // default if expired or canceled
                $isExpiredByDate = false;

                if ($subscription->status === 'active') {
                    if (is_null($subscription->end_date)) {
                        $remainingDays = 999999;
                        $remainingDaysFormatted = 'فترة مفتوحة';
                    } elseif ($subscription->end_date->gt($now)) {
                        $totalSeconds = $now->diffInSeconds($subscription->end_date);
                        $days = floor($totalSeconds / 86400);
                        $remainingSeconds = $totalSeconds % 86400;
                        $hours = floor($remainingSeconds / 3600);
                        $remainingSeconds %= 3600;
                        $minutes = floor($remainingSeconds / 60);
                        $seconds = $remainingSeconds % 60;

                        $remainingDays = $days;
                        $remainingDaysFormatted = "متبقي {$days} يوم و {$hours} ساعة و {$minutes} دقيقة و {$seconds} ثانية";
                    } else {
                        $remainingDaysFormatted = 'منتهية';
                        $isExpiredByDate = true;
                    }
                } elseif ($subscription->status === 'canceled') {
                    $remainingDaysFormatted = 'ملغية';
                } elseif ($subscription->status === 'pending_payment') {
                    $remainingDaysFormatted = 'بانتظار الدفع';
                }

                $subscription->remaining_days = $remainingDays;
                $subscription->remaining_formatted = $remainingDaysFormatted;

                // Count sub-users of this owner
                $subscription->sub_users_count = \App\Models\SubUser::where('owner_id', $subscription->owner_id)->count();

                // Count training sessions of workers of this owner since subscription start
                $workerIds = \App\Models\CamelWorker::where('owner_id', $subscription->owner_id)->pluck('id');
                $sessionsQuery = \App\Models\TrainingSession::whereIn('camel_worker_id', $workerIds);
                if ($subscription->start_date) {
                    $sessionsQuery->where('created_at', '>=', $subscription->start_date);
                }
                $subscription->training_sessions_count = $sessionsQuery->count();

                // Include bonus extra_training_sessions & extra_sub_users in effective plan totals
                $extraSessions = (int) ($subscription->extra_training_sessions ?? 0);
                $extraSubUsers = (int) ($subscription->extra_sub_users ?? 0);
                $baseSessions = (int) ($subscription->plan?->number_of_training_sessions ?? 0);
                $baseSubUsers = (int) ($subscription->plan?->number_of_sub_users ?? 0);

                $effectiveSessions = $baseSessions > 0 ? ($baseSessions + $extraSessions) : 0;
                $effectiveSubUsers = $baseSubUsers > 0 ? ($baseSubUsers + $extraSubUsers) : 0;

                if ($subscription->plan) {
                    $planClone = clone $subscription->plan;
                    $planClone->number_of_training_sessions = $effectiveSessions;
                    $planClone->number_of_sub_users = $effectiveSubUsers;
                    $subscription->setRelation('plan', $planClone);
                }

                $isSessionsExhausted = $effectiveSessions > 0 && $subscription->training_sessions_count >= $effectiveSessions;
                $isExpired = ($subscription->status !== 'active') || $isExpiredByDate;

                $subscription->extra_training_sessions = $extraSessions;
                $subscription->extra_sub_users = $extraSubUsers;
                $subscription->effective_training_sessions = $effectiveSessions;
                $subscription->effective_sub_users = $effectiveSubUsers;
                $subscription->is_expired = $isExpired;
                $subscription->is_sessions_exhausted = $isSessionsExhausted;
                $subscription->needs_renewal = $isExpired || $isSessionsExhausted;

                return $subscription;
            });

        return response()->json([
            'success' => true,
            'subscriptions' => $subscriptions,
            'activation_config' => $settings->toApiArray(),
        ], 200);
    }

    /**
     * API to manually or automatically subscribe an owner to a plan.
     */
    public function subscribeOwnerApi(Request $request)
    {
        $request->validate([
            'owner_id'             => 'required|exists:users,id',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'amount_paid'          => 'nullable|numeric|min:0',
            'transaction_id'       => 'nullable|string|max:255|unique:user_subscriptions,transaction_id',
            'status'               => 'nullable|in:active,expired,canceled,pending_payment',
        ], [
            'owner_id.required'             => 'معرف المالك مطلوب.',
            'owner_id.exists'               => 'المالك غير موجود.',
            'subscription_plan_id.required' => 'معرف الباقة مطلوب.',
            'subscription_plan_id.exists'   => 'الباقة غير موجودة.',
            'transaction_id.unique'         => 'رقم المعاملة (Transaction ID) مستخدم بالفعل.',
        ]);

        // Check if the user is an owner
        $owner = User::find($request->owner_id);
        if (!$owner || $owner->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'معرّف المالك المقدم لا ينتمي لمالك صالح.'
            ], 403);
        }

        $plan = SubscriptionPlan::findOrFail($request->subscription_plan_id);

        $startDate = now();
        $duration = $plan->plan_duration;
        $interval = $plan->plan_interval;

        if ($duration == 0) {
            $endDate = null;
        } else {
            $endDate = clone $startDate;
            if ($interval === 'day') {
                $endDate->addDays($duration);
            } elseif ($interval === 'month') {
                $endDate->addMonths($duration);
            } elseif ($interval === 'year') {
                $endDate->addYears($duration);
            }
        }

        $status = $request->status ?? 'active';

        // Cancel/Expire any existing active subscriptions for this owner
        if ($status === 'active') {
            UserSubscription::where('owner_id', $request->owner_id)
                ->where('status', 'active')
                ->update(['status' => 'expired']);
        }

        $amountPaid = $request->amount_paid ?? $plan->price;

        $transactionId = $request->transaction_id;
        if (empty($transactionId)) {
            $transactionId = $this->generateUniqueTransactionId();
        }

        $subscription = UserSubscription::create([
            'owner_id'             => $request->owner_id,
            'subscription_plan_id' => $request->subscription_plan_id,
            'start_date'           => $startDate,
            'end_date'             => $endDate,
            'amount_paid'          => $amountPaid,
            'transaction_id'       => $transactionId,
            'status'               => $status,
        ]);

        return response()->json([
            'success'      => true,
            'message'      => 'تم تسجيل الاشتراك بنجاح.',
            'subscription' => $subscription
        ], 201);
    }

    /**
     * API to automatically subscribe an owner to the available trial plan.
     */
    public function subscribeTrialOwnerApi(Request $request)
    {
        $request->validate([
            'owner_id'       => 'required|exists:users,id',
            'amount_paid'    => 'nullable|numeric|min:0',
            'transaction_id' => 'nullable|string|max:255|unique:user_subscriptions,transaction_id',
            'status'         => 'nullable|in:active,expired,canceled,pending_payment',
        ], [
            'owner_id.required'     => 'معرف المالك مطلوب.',
            'owner_id.exists'       => 'المالك غير موجود.',
            'transaction_id.unique' => 'رقم المعاملة (Transaction ID) مستخدم بالفعل.',
        ]);

        // Check if the user is an owner
        $owner = User::find($request->owner_id);
        if (!$owner || $owner->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'معرّف المالك المقدم لا ينتمي لمالك صالح.'
            ], 403);
        }

        // Find the trial subscription plan
        $plan = SubscriptionPlan::where('is_trial', true)->first();
        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد باقة تجريبية متاحة حالياً في النظام.'
            ], 422);
        }

        $startDate = now();
        $duration = $plan->plan_duration;
        $interval = $plan->plan_interval;

        if ($duration == 0) {
            $endDate = null;
        } else {
            $endDate = clone $startDate;
            if ($interval === 'day') {
                $endDate->addDays($duration);
            } elseif ($interval === 'month') {
                $endDate->addMonths($duration);
            } elseif ($interval === 'year') {
                $endDate->addYears($duration);
            }
        }

        $status = $request->status ?? 'active';

        // Cancel/Expire any existing active subscriptions for this owner
        if ($status === 'active') {
            UserSubscription::where('owner_id', $request->owner_id)
                ->where('status', 'active')
                ->update(['status' => 'expired']);
        }

        $amountPaid = $request->amount_paid ?? 0.00;

        $transactionId = $request->transaction_id;
        if (empty($transactionId)) {
            $transactionId = $this->generateUniqueTransactionId();
        }

        $subscription = UserSubscription::create([
            'owner_id'             => $request->owner_id,
            'subscription_plan_id' => $plan->id,
            'start_date'           => $startDate,
            'end_date'             => $endDate,
            'amount_paid'          => $amountPaid,
            'transaction_id'       => $transactionId,
            'status'               => $status,
        ]);

        return response()->json([
            'success'      => true,
            'message'      => 'تم تسجيل الاشتراك في الباقة التجريبية بنجاح.',
            'subscription' => $subscription
        ], 201);
    }

    /**
     * Generate a unique transaction ID.
     */
    private function generateUniqueTransactionId()
    {
        do {
            $transactionId = 'TXN-' . strtoupper(bin2hex(random_bytes(5)));
        } while (UserSubscription::where('transaction_id', $transactionId)->exists());

        return $transactionId;
    }
}
