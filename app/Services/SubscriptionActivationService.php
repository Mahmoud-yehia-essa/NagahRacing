<?php

namespace App\Services;

use App\Models\SubscriptionActivationCode;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionSetting;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubscriptionActivationService
{
    /**
     * Normalize user-typed activation code (trim, uppercase, collapse spaces).
     */
    public function normalizeCode(string $rawCode): string
    {
        $cleaned = strtoupper(trim($rawCode));
        $cleaned = preg_replace('/\s+/', '', $cleaned);
        return $cleaned;
    }

    /**
     * Atomically redeem an activation code for an owner and issue a confirmation invoice.
     *
     * @throws ValidationException
     */
    public function redeemCode(User $owner, string $rawCode): array
    {
        $normalized = $this->normalizeCode($rawCode);
        if (empty($normalized)) {
            throw ValidationException::withMessages([
                'code' => ['يرجى إدخال كود التفعيل.'],
            ]);
        }

        return DB::transaction(function () use ($owner, $normalized) {
            /** @var SubscriptionActivationCode|null $codeRecord */
            $codeRecord = SubscriptionActivationCode::where('code', $normalized)
                ->lockForUpdate()
                ->first();

            if (!$codeRecord) {
                throw ValidationException::withMessages([
                    'code' => ['كود التفعيل غير صحيح، يرجى التأكد من كتابته بشكل سليم أو التواصل مع الإدارة.'],
                ]);
            }

            if ($codeRecord->status === 'used') {
                throw ValidationException::withMessages([
                    'code' => ['تم استخدام كود التفعيل هذا مسبقاً ولا يمكن إعادة استخدامه.'],
                ]);
            }

            if ($codeRecord->status === 'revoked') {
                throw ValidationException::withMessages([
                    'code' => ['تم إلغاء كود التفعيل هذا من قبل الإدارة.'],
                ]);
            }

            if ($codeRecord->status === 'expired' || $codeRecord->isExpiredByDate()) {
                if ($codeRecord->status !== 'expired') {
                    $codeRecord->update(['status' => 'expired']);
                }
                throw ValidationException::withMessages([
                    'code' => ['انتهت صلاحية كود التفعيل هذا، يرجى التواصل مع الإدارة لإصدار كود جديد.'],
                ]);
            }

            if ($codeRecord->owner_id !== null && (int) $codeRecord->owner_id !== (int) $owner->id) {
                throw ValidationException::withMessages([
                    'code' => ['هذا الكود مخصص لحساب مالك آخر ولا يمكن تفعيله على حسابك.'],
                ]);
            }

            $result = $this->applyGrantToOwner(
                owner: $owner,
                codeType: $codeRecord->code_type,
                planId: $codeRecord->subscription_plan_id,
                extraDays: (int) $codeRecord->extra_days,
                extraTrainingSessions: (int) $codeRecord->extra_training_sessions,
                extraSubUsers: (int) $codeRecord->extra_sub_users,
                amountPaid: (float) $codeRecord->amount_paid,
                paymentMethod: SubscriptionActivationCode::paymentMethodLabel($codeRecord->payment_method),
                paymentReference: $codeRecord->payment_reference,
                notes: $codeRecord->notes,
                activationCode: $codeRecord,
                issuedByAdminId: $codeRecord->created_by_admin_id
            );

            $codeRecord->update([
                'status' => 'used',
                'used_by_owner_id' => $owner->id,
                'used_at' => now(),
            ]);

            return $result;
        });
    }

    /**
     * Directly activate a plan or top-up duration/sessions/sub-users for an owner from Admin Panel.
     */
    public function directAdminActivate(array $data, ?User $admin = null): array
    {
        $owner = User::where('role', 'owner')->findOrFail($data['owner_id']);

        return DB::transaction(function () use ($owner, $data, $admin) {
            return $this->applyGrantToOwner(
                owner: $owner,
                codeType: $data['code_type'] ?? 'plan',
                planId: !empty($data['subscription_plan_id']) ? (int) $data['subscription_plan_id'] : null,
                extraDays: (int) ($data['extra_days'] ?? 0),
                extraTrainingSessions: (int) ($data['extra_training_sessions'] ?? 0),
                extraSubUsers: (int) ($data['extra_sub_users'] ?? 0),
                amountPaid: (float) ($data['amount_paid'] ?? 0),
                paymentMethod: SubscriptionActivationCode::paymentMethodLabel($data['payment_method'] ?? 'bank_transfer'),
                paymentReference: $data['payment_reference'] ?? null,
                notes: $data['notes'] ?? null,
                activationCode: null,
                issuedByAdminId: $admin?->id,
                isDirectAdmin: true
            );
        });
    }

    /**
     * Core atomic subscription & invoice application logic shared by voucher redemption and direct admin activation.
     */
    private function applyGrantToOwner(
        User $owner,
        string $codeType,
        ?int $planId,
        int $extraDays,
        int $extraTrainingSessions,
        int $extraSubUsers,
        float $amountPaid,
        string $paymentMethod,
        ?string $paymentReference,
        ?string $notes,
        ?SubscriptionActivationCode $activationCode = null,
        ?int $issuedByAdminId = null,
        bool $isDirectAdmin = false
    ): array {
        $now = Carbon::now();
        $settings = SubscriptionSetting::current();
        $currency = $settings->currency_label ?: 'ر.س';

        $hadPreviousSub = UserSubscription::where('owner_id', $owner->id)->exists();
        $currentSub = UserSubscription::with('plan')
            ->where('owner_id', $owner->id)
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->latest('id')
            ->first();

        $daysAddedForInvoice = 0;
        $sessionsAddedForInvoice = 0;
        $subUsersAddedForInvoice = 0;
        $actionType = 'plan_activation';
        $title = '';
        $plan = $planId ? SubscriptionPlan::find($planId) : null;

        if ($codeType === 'custom_addon' && !$plan) {
            // Extend / top-up existing subscription (or create one on default plan if owner has none yet)
            $actionType = $isDirectAdmin ? 'admin_direct' : 'addon_topup';

            if (!$currentSub) {
                $fallbackPlan = SubscriptionPlan::where('is_default', true)->first()
                    ?? SubscriptionPlan::where('status', 'active')->first()
                    ?? SubscriptionPlan::first();

                if (!$fallbackPlan) {
                    throw ValidationException::withMessages([
                        'subscription_plan_id' => ['لا توجد باقة مسجلة في النظام لربط الإضافات بها.'],
                    ]);
                }

                $endDate = $extraDays > 0 ? $now->copy()->addDays($extraDays) : null;
                $subscription = UserSubscription::create([
                    'owner_id' => $owner->id,
                    'subscription_plan_id' => $fallbackPlan->id,
                    'start_date' => $now,
                    'end_date' => $endDate,
                    'amount_paid' => $amountPaid,
                    'transaction_id' => $this->generateUniqueTransactionId($activationCode ? 'VCH' : 'ADM'),
                    'status' => 'active',
                    'extra_training_sessions' => $extraTrainingSessions,
                    'extra_sub_users' => $extraSubUsers,
                    'activation_code_id' => $activationCode?->id,
                    'payment_method' => $paymentMethod,
                    'notes' => $notes,
                ]);
                $plan = $fallbackPlan;
            } else {
                $plan = $currentSub->plan;
                $newEndDate = $currentSub->end_date;

                if ($extraDays > 0) {
                    if ($newEndDate === null) {
                        // Already unlimited duration; keep open unless it was expired
                        if ($currentSub->status !== 'active') {
                            $newEndDate = $now->copy()->addDays($extraDays);
                        }
                    } else {
                        $baseDate = $newEndDate->isFuture() ? $newEndDate->copy() : $now->copy();
                        $newEndDate = $baseDate->addDays($extraDays);
                    }
                }

                // Mark any other active subs expired, reactivate this one
                UserSubscription::where('owner_id', $owner->id)
                    ->where('id', '!=', $currentSub->id)
                    ->where('status', 'active')
                    ->update(['status' => 'expired']);

                $currentSub->update([
                    'end_date' => $newEndDate,
                    'status' => 'active',
                    'extra_training_sessions' => (int) ($currentSub->extra_training_sessions ?? 0) + $extraTrainingSessions,
                    'extra_sub_users' => (int) ($currentSub->extra_sub_users ?? 0) + $extraSubUsers,
                    'amount_paid' => (float) $currentSub->amount_paid + $amountPaid,
                    'payment_method' => $paymentMethod,
                    'notes' => $notes ?: $currentSub->notes,
                ]);

                $subscription = $currentSub->fresh(['plan']);
            }

            $daysAddedForInvoice = $extraDays;
            $sessionsAddedForInvoice = $extraTrainingSessions;
            $subUsersAddedForInvoice = $extraSubUsers;

            $parts = [];
            if ($extraDays > 0) {
                $parts[] = "+{$extraDays} يوم";
            }
            if ($extraTrainingSessions > 0) {
                $parts[] = "+{$extraTrainingSessions} جلسة تدريب";
            }
            if ($extraSubUsers > 0) {
                $parts[] = "+{$extraSubUsers} مستخدم فرعي";
            }
            $title = 'شحن إضافات للاشتراك' . (!empty($parts) ? ' (' . implode(' / ', $parts) . ')' : '');
        } else {
            // Plan activation or Plan + Addons
            if (!$plan) {
                throw ValidationException::withMessages([
                    'subscription_plan_id' => ['الباقة المرتبطة غير موجودة.'],
                ]);
            }

            $actionType = $isDirectAdmin
                ? 'admin_direct'
                : ($hadPreviousSub ? 'plan_renewal' : 'plan_activation');

            // Calculate plan end date
            $startDate = $now->copy();
            $duration = (int) $plan->plan_duration;
            $interval = $plan->plan_interval;

            if ($duration === 0 && $extraDays === 0) {
                $endDate = null;
                $daysAddedForInvoice = 0;
            } else {
                $endDate = $startDate->copy();
                if ($duration > 0) {
                    if ($interval === 'day') {
                        $endDate->addDays($duration);
                    } elseif ($interval === 'month') {
                        $endDate->addMonths($duration);
                    } elseif ($interval === 'year') {
                        $endDate->addYears($duration);
                    }
                }
                if ($extraDays > 0) {
                    $endDate->addDays($extraDays);
                }
                $daysAddedForInvoice = (int) $startDate->diffInDays($endDate);
            }

            // Expire old active subscriptions
            UserSubscription::where('owner_id', $owner->id)
                ->where('status', 'active')
                ->update(['status' => 'expired']);

            $effectiveAmount = $amountPaid > 0 ? $amountPaid : (float) $plan->price;

            $subscription = UserSubscription::create([
                'owner_id' => $owner->id,
                'subscription_plan_id' => $plan->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'amount_paid' => $effectiveAmount,
                'transaction_id' => $this->generateUniqueTransactionId($activationCode ? 'VCH' : 'ADM'),
                'status' => 'active',
                'extra_training_sessions' => $extraTrainingSessions,
                'extra_sub_users' => $extraSubUsers,
                'activation_code_id' => $activationCode?->id,
                'payment_method' => $paymentMethod,
                'notes' => $notes,
            ]);
            $subscription->load('plan');

            $planSessions = (int) ($plan->number_of_training_sessions ?? 0);
            $planSubUsers = (int) ($plan->number_of_sub_users ?? 0);
            $sessionsAddedForInvoice = $planSessions + $extraTrainingSessions;
            $subUsersAddedForInvoice = $planSubUsers + $extraSubUsers;
            $amountPaid = $effectiveAmount;

            $title = ($hadPreviousSub ? 'تجديد / تفعيل ' : 'تفعيل ') . $plan->name;
            if ($extraDays > 0 || $extraTrainingSessions > 0 || $extraSubUsers > 0) {
                $bonusParts = [];
                if ($extraDays > 0) $bonusParts[] = "+{$extraDays} يوم";
                if ($extraTrainingSessions > 0) $bonusParts[] = "+{$extraTrainingSessions} جلسة";
                if ($extraSubUsers > 0) $bonusParts[] = "+{$extraSubUsers} مستخدم فرعي";
                $title .= ' (' . implode(' / ', $bonusParts) . ')';
            }
        }

        // Create confirmation invoice record for both owner & admin
        $invoice = SubscriptionInvoice::create([
            'invoice_number' => SubscriptionInvoice::generateInvoiceNumber(),
            'owner_id' => $owner->id,
            'user_subscription_id' => $subscription->id,
            'subscription_plan_id' => $plan?->id,
            'activation_code_id' => $activationCode?->id,
            'action_type' => $actionType,
            'title' => $title,
            'plan_name' => $plan?->name,
            'amount_paid' => $amountPaid,
            'currency' => $currency,
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference ?: $subscription->transaction_id,
            'activation_code_used' => $activationCode?->code,
            'days_added' => $daysAddedForInvoice,
            'training_sessions_added' => $sessionsAddedForInvoice,
            'sub_users_added' => $subUsersAddedForInvoice,
            'period_start' => $subscription->start_date,
            'period_end' => $subscription->end_date,
            'status' => 'confirmed',
            'notes' => $notes,
            'issued_by_admin_id' => $issuedByAdminId,
            'issued_at' => $now,
        ]);

        return [
            'subscription' => $subscription,
            'invoice' => $invoice,
            'message' => 'تم تفعيل الاشتراك بنجاح وإصدار فاتورة التأكيد رقم ' . $invoice->invoice_number,
        ];
    }

    /**
     * Helper to issue a confirmation invoice when a subscription is created via legacy admin/API endpoints.
     */
    public function createInvoiceForSubscription(
        UserSubscription $subscription,
        string $actionType = 'plan_activation',
        string $paymentMethod = 'تفعيل من الإدارة',
        ?int $issuedByAdminId = null,
        ?string $notes = null
    ): SubscriptionInvoice {
        $subscription->loadMissing('plan');
        $settings = SubscriptionSetting::current();
        $plan = $subscription->plan;

        $daysAdded = 0;
        if ($subscription->start_date && $subscription->end_date) {
            $daysAdded = (int) $subscription->start_date->diffInDays($subscription->end_date);
        }

        return SubscriptionInvoice::create([
            'invoice_number' => SubscriptionInvoice::generateInvoiceNumber(),
            'owner_id' => $subscription->owner_id,
            'user_subscription_id' => $subscription->id,
            'subscription_plan_id' => $subscription->subscription_plan_id,
            'activation_code_id' => $subscription->activation_code_id,
            'action_type' => $actionType,
            'title' => ($plan ? 'تفعيل ' . $plan->name : 'تفعيل اشتراك'),
            'plan_name' => $plan?->name,
            'amount_paid' => (float) $subscription->amount_paid,
            'currency' => $settings->currency_label ?: 'ر.س',
            'payment_method' => $paymentMethod,
            'payment_reference' => $subscription->transaction_id,
            'activation_code_used' => null,
            'days_added' => $daysAdded,
            'training_sessions_added' => (int) ($plan?->number_of_training_sessions ?? 0) + (int) ($subscription->extra_training_sessions ?? 0),
            'sub_users_added' => (int) ($plan?->number_of_sub_users ?? 0) + (int) ($subscription->extra_sub_users ?? 0),
            'period_start' => $subscription->start_date,
            'period_end' => $subscription->end_date,
            'status' => 'confirmed',
            'notes' => $notes,
            'issued_by_admin_id' => $issuedByAdminId,
            'issued_at' => now(),
        ]);
    }

    private function generateUniqueTransactionId(string $prefix = 'TXN'): string
    {
        do {
            $transactionId = strtoupper($prefix) . '-' . strtoupper(bin2hex(random_bytes(5)));
        } while (UserSubscription::where('transaction_id', $transactionId)->exists());

        return $transactionId;
    }
}
