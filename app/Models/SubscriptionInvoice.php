<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionInvoice extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'days_added' => 'integer',
        'training_sessions_added' => 'integer',
        'sub_users_added' => 'integer',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'issued_at' => 'datetime',
    ];

    public const ACTION_LABELS = [
        'plan_activation' => 'تفعيل باقة جديدة',
        'plan_renewal' => 'تجديد / ترقية باقة',
        'addon_topup' => 'شحن إضافات (مدة / جلسات)',
        'admin_direct' => 'تفعيل مباشر من الإدارة',
        'trial_activation' => 'تفعيل الباقة التجريبية',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function subscription()
    {
        return $this->belongsTo(UserSubscription::class, 'user_subscription_id');
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function activationCode()
    {
        return $this->belongsTo(SubscriptionActivationCode::class, 'activation_code_id');
    }

    public function issuedByAdmin()
    {
        return $this->belongsTo(User::class, 'issued_by_admin_id');
    }

    public function getActionLabelAttribute(): string
    {
        return self::ACTION_LABELS[$this->action_type] ?? $this->action_type;
    }

    public static function generateInvoiceNumber(): string
    {
        $datePrefix = 'INV-' . now()->format('Ymd') . '-';
        do {
            $suffix = strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
            $number = $datePrefix . $suffix;
        } while (static::where('invoice_number', $number)->exists());

        return $number;
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'owner_id' => $this->owner_id,
            'user_subscription_id' => $this->user_subscription_id,
            'subscription_plan_id' => $this->subscription_plan_id,
            'action_type' => $this->action_type,
            'action_label' => $this->action_label,
            'title' => $this->title,
            'plan_name' => $this->plan_name ?? $this->plan?->name,
            'amount_paid' => number_format((float) $this->amount_paid, 2, '.', ''),
            'currency' => $this->currency ?: 'ر.س',
            'payment_method' => $this->payment_method ?: 'تفعيل من الإدارة',
            'payment_reference' => $this->payment_reference,
            'activation_code_used' => $this->activation_code_used,
            'days_added' => (int) $this->days_added,
            'training_sessions_added' => (int) $this->training_sessions_added,
            'sub_users_added' => (int) $this->sub_users_added,
            'period_start' => $this->period_start ? $this->period_start->toIso8601String() : null,
            'period_end' => $this->period_end ? $this->period_end->toIso8601String() : null,
            'status' => $this->status,
            'notes' => $this->notes,
            'issued_at' => ($this->issued_at ?? $this->created_at)?->toIso8601String(),
        ];
    }
}
