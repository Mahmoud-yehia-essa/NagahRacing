<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WhatsAppSetting extends Model
{
    use HasFactory;

    public const PLAN_MONTHLY = 'monthly';
    public const PLAN_YEARLY  = 'yearly';

    public const PRICE_MONTHLY = 7.00;
    public const PRICE_YEARLY  = 70.00;

    protected $table = 'whatsapp_settings';

    protected $guarded = [];

    protected $casts = [
        'is_connected'       => 'boolean',
        'is_active'          => 'boolean',
        'is_primary_otp'     => 'boolean',
        'subscription_price' => 'decimal:2',
        'starts_at'          => 'datetime',
        'expires_at'         => 'datetime',
        'last_connected_at'  => 'datetime',
    ];

    public function messages()
    {
        return $this->hasMany(WhatsAppMessage::class, 'whatsapp_setting_id');
    }

    /**
     * السعر الافتراضي حسب نوع الاشتراك
     */
    public static function defaultPriceForPlan(string $planType): float
    {
        return $planType === self::PLAN_MONTHLY ? self::PRICE_MONTHLY : self::PRICE_YEARLY;
    }

    /**
     * توليد مفتاح API فريد وسري
     */
    public static function generateUniqueApiKey(): string
    {
        do {
            $key = 'nr_wa_' . Str::random(36);
        } while (self::where('api_key', $key)->exists());

        return $key;
    }

    /**
     * توليد معرف جلسة فريد لمحرك Baileys
     */
    public static function generateUniqueSessionId(): string
    {
        do {
            $sid = 'wa_' . strtolower(Str::random(12));
        } while (self::where('session_id', $sid)->exists());

        return $sid;
    }

    /**
     * جلب الحساب الرئيسي لإرسال OTP في تطبيق نجاح ريسنج (أو إنشاؤه تلقائياً إن لم يوجد)
     */
    public static function getSettings(): self
    {
        return self::getPrimaryForOtp();
    }

    public static function getPrimaryForOtp(): self
    {
        $settings = self::where('is_primary_otp', true)->first() ?? self::orderBy('id')->first();

        if (!$settings) {
            $settings = self::create([
                'label'              => 'حساب نجاح ريسنج الرئيسي',
                'session_id'         => 'default',
                'engine'             => 'local',
                'api_key'            => self::generateUniqueApiKey(),
                'plan_type'          => self::PLAN_YEARLY,
                'subscription_price' => self::PRICE_YEARLY,
                'currency'           => 'USD',
                'starts_at'          => now(),
                'expires_at'         => now()->addYear(),
                'is_active'          => true,
                'is_primary_otp'     => true,
                'local_server_url'   => 'http://127.0.0.1:3002',
                'is_connected'       => false,
            ]);
        } else {
            // ضمان وجود session_id و تواريخ صلاحية
            $updates = [];
            if (empty($settings->session_id)) {
                $updates['session_id'] = 'default';
            }
            if (empty($settings->starts_at)) {
                $updates['starts_at'] = now();
            }
            if (empty($settings->expires_at)) {
                $updates['expires_at'] = now()->addYear();
            }
            if (!empty($updates)) {
                $settings->update($updates);
            }
        }

        return $settings;
    }

    /**
     * البحث الآمن عن حساب مشترك بواسطة مفتاح الـ API
     */
    public static function findByApiKey(?string $providedKey): ?self
    {
        $key = trim((string) $providedKey);
        if ($key === '') {
            return null;
        }

        $candidate = self::where('api_key', $key)->first();
        if (!$candidate) {
            return null;
        }

        return hash_equals((string) $candidate->api_key, $key) ? $candidate : null;
    }

    public function getSessionKey(): string
    {
        return $this->session_id ?: 'default';
    }

    public function isExpired(): bool
    {
        if (is_null($this->expires_at)) {
            return false;
        }
        return $this->expires_at->isPast();
    }

    public function isUsable(): bool
    {
        return (bool) $this->is_active && !$this->isExpired();
    }

    public function getPlanLabel(): string
    {
        return $this->plan_type === self::PLAN_MONTHLY ? 'اشتراك شهري' : 'اشتراك سنوي';
    }

    public function getFormattedPrice(): string
    {
        $price = (float) ($this->subscription_price ?? self::defaultPriceForPlan($this->plan_type ?? self::PLAN_YEARLY));
        $formatted = fmod($price, 1.0) === 0.0 ? number_format($price, 0) : number_format($price, 2);
        return '$' . $formatted;
    }

    /**
     * ملخص تفصيلي لحالة الاشتراك والمدة المتبقية للعرض في الصفحة المستقلة ولوحة التحكم
     */
    public function getSubscriptionSummary(): array
    {
        $now = now();
        $startsAt = $this->starts_at ?: $this->created_at ?: $now;
        $expiresAt = $this->expires_at;
        $isExpired = $this->isExpired();
        $isActive = (bool) $this->is_active;
        $isUsable = $isActive && !$isExpired;

        $statusKey = !$isActive ? 'suspended' : ($isExpired ? 'expired' : 'active');
        $statusLabel = match ($statusKey) {
            'suspended' => 'موقوف من الإدارة',
            'expired'   => 'منتهي الصلاحية',
            default     => 'نشط وساري',
        };

        $daysRemaining = 0;
        $hoursRemaining = 0;
        $humanRemaining = 'غير محدد';
        $progressPercent = 100;
        $isExpiringSoon = false;

        if ($expiresAt) {
            if ($isExpired) {
                $daysRemaining = 0;
                $hoursRemaining = 0;
                $humanRemaining = 'منتهي الصلاحية';
                $progressPercent = 0;
            } else {
                $totalSeconds = max(1, (int) round($startsAt->diffInSeconds($expiresAt, false)));
                $remainingSeconds = max(0, (int) round($now->diffInSeconds($expiresAt, false)));
                $progressPercent = (int) min(100, max(1, round(($remainingSeconds / $totalSeconds) * 100)));

                $diff = $now->diff($expiresAt);
                $daysRemaining = intdiv($remainingSeconds, 86400);
                $hoursRemaining = intdiv($remainingSeconds % 86400, 3600);
                $isExpiringSoon = $daysRemaining <= 5;

                $parts = [];
                if ($diff->y > 0) {
                    $parts[] = $diff->y == 1 ? 'سنة واحدة' : ($diff->y == 2 ? 'سنتان' : "{$diff->y} سنوات");
                }
                if ($diff->m > 0) {
                    $parts[] = $diff->m == 1 ? 'شهر واحد' : ($diff->m == 2 ? 'شهران' : ($diff->m <= 10 ? "{$diff->m} أشهر" : "{$diff->m} شهراً"));
                }
                if ($diff->d > 0) {
                    $parts[] = $diff->d == 1 ? 'يوم واحد' : ($diff->d == 2 ? 'يومان' : ($diff->d <= 10 ? "{$diff->d} أيام" : "{$diff->d} يوماً"));
                }
                if (empty($parts)) {
                    $hours = max(1, $diff->h);
                    $parts[] = $hours == 1 ? 'ساعة واحدة' : ($hours == 2 ? 'ساعتان' : ($hours <= 10 ? "{$hours} ساعات" : "{$hours} ساعة"));
                }
                $humanRemaining = implode(' و ', $parts);
            }
        }

        return [
            'label'                => $this->label ?: 'حساب واتساب',
            'plan_type'            => $this->plan_type ?: self::PLAN_YEARLY,
            'plan_label'           => $this->getPlanLabel(),
            'price'                => (float) ($this->subscription_price ?? self::PRICE_YEARLY),
            'formatted_price'      => $this->getFormattedPrice(),
            'currency'             => $this->currency ?: 'USD',
            'starts_at'            => $startsAt->format('Y-m-d'),
            'expires_at'           => $expiresAt ? $expiresAt->format('Y-m-d H:i') : 'غير محدود',
            'expires_date'         => $expiresAt ? $expiresAt->format('Y-m-d') : 'غير محدود',
            'days_remaining'       => $daysRemaining,
            'hours_remaining'      => $hoursRemaining,
            'human_remaining'      => $humanRemaining,
            'progress_percent'     => $progressPercent,
            'is_expiring_soon'     => $isExpiringSoon,
            'is_expired'           => $isExpired,
            'is_active'            => $isActive,
            'is_usable'            => $isUsable,
            'status_key'           => $statusKey,
            'status_label'         => $statusLabel,
        ];
    }
}
