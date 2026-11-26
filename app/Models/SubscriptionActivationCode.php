<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionActivationCode extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'extra_days' => 'integer',
        'extra_training_sessions' => 'integer',
        'extra_sub_users' => 'integer',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public const PAYMENT_METHODS = [
        'bank_transfer' => 'تحويل بنكي',
        'wamd' => 'ومض (WAMD)',
        'instapay' => 'إنستاباي (InstaPay)',
        'vodafone_cash' => 'محفظة إلكترونية',
        'cash' => 'نقدي (كاش)',
        'external_link' => 'رابط دفع خارجي',
        'gift' => 'منحة / هدية من الإدارة',
        'other' => 'وسيلة أخرى',
    ];

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function assignedOwner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function usedByOwner()
    {
        return $this->belongsTo(User::class, 'used_by_owner_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }

    public function invoice()
    {
        return $this->hasOne(SubscriptionInvoice::class, 'activation_code_id');
    }

    public static function paymentMethodLabel(?string $method): string
    {
        if (!$method) {
            return 'دفع خارجي';
        }
        return self::PAYMENT_METHODS[$method] ?? $method;
    }

    /**
     * Generate a human-friendly unambiguous code like NGH-7K4M-9P2X
     * (omits confusing characters: 0, O, 1, I, L).
     */
    public static function generateUniqueCode(string $prefix = 'NGH'): string
    {
        $alphabet = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';
        $len = strlen($alphabet);

        do {
            $part1 = '';
            $part2 = '';
            for ($i = 0; $i < 4; $i++) {
                $part1 .= $alphabet[random_int(0, $len - 1)];
                $part2 .= $alphabet[random_int(0, $len - 1)];
            }
            $code = strtoupper($prefix) . '-' . $part1 . '-' . $part2;
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public function isExpiredByDate(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
