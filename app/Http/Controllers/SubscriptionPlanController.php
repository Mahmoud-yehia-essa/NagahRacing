<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    /**
     * Display a listing of the subscription plans.
     */
    public function index()
    {
        $plans = SubscriptionPlan::withCount('subscriptions')->latest()->get();
        return view('admin.subscription_plan.all_plans', compact('plans'));
    }

    /**
     * Show the form for creating a new subscription plan.
     */
    public function create()
    {
        return view('admin.subscription_plan.add_plan');
    }

    /**
     * Store a newly created subscription plan in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'plan_duration' => 'required|integer|min:1',
            'plan_interval' => 'required|in:day,month,year',
            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => 'يرجى إدخال اسم الباقة',
            'price.required' => 'يرجى إدخال سعر الباقة',
            'plan_duration.required' => 'يرجى إدخال مدة صلاحية الباقة',
            'plan_interval.required' => 'يرجى تحديد وحدة قياس المدة',
        ]);

        SubscriptionPlan::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'plan_duration' => $request->plan_duration,
            'plan_interval' => $request->plan_interval,
            'is_trial' => $request->has('is_trial') ? true : false,
            'status' => $request->status,
        ]);

        $notification = [
            'message' => 'تم إنشاء باقة الاشتراك بنجاح',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.subscription.plans')->with($notification);
    }

    /**
     * Show the form for editing the specified subscription plan.
     */
    public function edit($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        return view('admin.subscription_plan.edit_plan', compact('plan'));
    }

    /**
     * Update the specified subscription plan in storage.
     */
    public function update(Request $request)
    {
        $id = $request->id;
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'plan_duration' => 'required|integer|min:1',
            'plan_interval' => 'required|in:day,month,year',
            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => 'يرجى إدخال اسم الباقة',
            'price.required' => 'يرجى إدخال سعر الباقة',
            'plan_duration.required' => 'يرجى إدخال مدة صلاحية الباقة',
        ]);

        $plan = SubscriptionPlan::findOrFail($id);
        $plan->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'plan_duration' => $request->plan_duration,
            'plan_interval' => $request->plan_interval,
            'is_trial' => $request->has('is_trial') ? true : false,
            'status' => $request->status,
        ]);

        $notification = [
            'message' => 'تم تحديث باقة الاشتراك بنجاح',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.subscription.plans')->with($notification);
    }

    /**
     * Remove the specified subscription plan from storage.
     */
    public function destroy($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        
        // Prevent deletion if there are active/subscribed users associated with it
        if ($plan->subscriptions()->where('status', 'active')->count() > 0) {
            $notification = [
                'message' => 'لا يمكن حذف الباقة لوجود ملاك مشتركين فيها حالياً',
                'alert-type' => 'error',
            ];
            return redirect()->back()->with($notification);
        }

        $plan->delete();

        $notification = [
            'message' => 'تم حذف باقة الاشتراك بنجاح',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.subscription.plans')->with($notification);
    }

    /**
     * Toggle the status of the subscription plan.
     */
    public function toggleStatus($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $newStatus = $plan->status == 'active' ? 'inactive' : 'active';
        $plan->update(['status' => $newStatus]);

        $notification = [
            'message' => 'تم تغيير حالة الباقة بنجاح',
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * API to retrieve all subscription plans.
     */
    public function getPlansApi(Request $request)
    {
        $request->validate([
            'owner_id' => 'required|exists:users,id',
        ], [
            'owner_id.required' => 'معرف المالك مطلوب.',
            'owner_id.exists'   => 'المالك غير موجود.',
        ]);

        // Verify the owner exists and is an owner
        $owner = User::find($request->owner_id);
        if (!$owner || $owner->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'The provided owner_id does not belong to a valid owner.'
            ], 403);
        }

        // Fetch active subscription for the owner
        $activeSubscription = \App\Models\UserSubscription::where('owner_id', $request->owner_id)
            ->where('status', 'active')
            ->where(function($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>', now());
            })
            ->latest()
            ->first();

        $activePlanId = $activeSubscription ? $activeSubscription->subscription_plan_id : null;

        // Fetch plans and mark the subscribed one
        $plans = SubscriptionPlan::latest()->get()->map(function($plan) use ($activePlanId) {
            $plan->is_subscribed = ($plan->id === $activePlanId);
            return $plan;
        });

        return response()->json([
            'success' => true,
            'plans' => $plans,
            'active_subscription' => $activeSubscription
        ], 200);
    }
}
