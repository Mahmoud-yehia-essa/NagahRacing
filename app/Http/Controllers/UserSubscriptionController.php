<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
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

        $subscriptions = $query->latest()->get();

        return view('admin.user_subscription.all_subscriptions', compact('owners', 'plans', 'subscriptions'));
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
            'transaction_id' => 'nullable|string|max:255',
            'status' => 'required|in:active,expired,canceled,pending_payment',
        ], [
            'owner_id.required' => 'يرجى اختيار المالك',
            'subscription_plan_id.required' => 'يرجى اختيار باقة الاشتراك',
            'start_date.required' => 'يرجى تحديد تاريخ بداية الاشتراك',
            'amount_paid.required' => 'يرجى إدخال المبلغ المدفوع',
        ]);

        $plan = SubscriptionPlan::findOrFail($request->subscription_plan_id);
        
        // Calculate end date based on plan duration and interval
        $startDate = Carbon::parse($request->start_date);
        $duration = $plan->plan_duration;
        $interval = $plan->plan_interval;

        $endDate = clone $startDate;
        if ($interval === 'day') {
            $endDate->addDays($duration);
        } elseif ($interval === 'month') {
            $endDate->addMonths($duration);
        } elseif ($interval === 'year') {
            $endDate->addYears($duration);
        }

        // Cancel any active subscriptions for this owner to ensure they don't have overlapping active plans
        if ($request->status === 'active') {
            UserSubscription::where('owner_id', $request->owner_id)
                ->where('status', 'active')
                ->update(['status' => 'expired']);
        }

        UserSubscription::create([
            'owner_id' => $request->owner_id,
            'subscription_plan_id' => $request->subscription_plan_id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'amount_paid' => $request->amount_paid,
            'transaction_id' => $request->transaction_id,
            'status' => $request->status,
        ]);

        $notification = [
            'message' => 'تم تسجيل اشتراك المالك بنجاح وحساب تاريخ الانتهاء',
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

        // Query all subscriptions for the owner, loading plan details
        $subscriptions = UserSubscription::with(['plan'])
            ->where('owner_id', $request->owner_id)
            ->latest()
            ->get()
            ->map(function ($subscription) use ($now) {
                $remainingDays = 0;
                $remainingDaysFormatted = 'منتهية'; // default if expired or canceled

                if ($subscription->status === 'active' && $subscription->end_date) {
                    if ($subscription->end_date->gt($now)) {
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
                    }
                } elseif ($subscription->status === 'canceled') {
                    $remainingDaysFormatted = 'ملغية';
                } elseif ($subscription->status === 'pending_payment') {
                    $remainingDaysFormatted = 'بانتظار الدفع';
                }

                $subscription->remaining_days = $remainingDays;
                $subscription->remaining_formatted = $remainingDaysFormatted;

                return $subscription;
            });

        return response()->json([
            'success' => true,
            'subscriptions' => $subscriptions
        ], 200);
    }
}
