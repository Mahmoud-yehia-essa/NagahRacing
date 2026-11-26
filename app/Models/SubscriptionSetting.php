<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'enable_activation_codes' => 'boolean',
        'show_subscriptions_in_app' => 'boolean',
        'show_subscriptions_on_ios' => 'boolean',
        'show_subscriptions_on_android' => 'boolean',
        'show_whatsapp_on_ios' => 'boolean',
        'show_whatsapp_on_android' => 'boolean',
    ];

    public static function current(): self
    {
        $setting = static::first();
        if (!$setting) {
            $defaults = [
                'admin_whatsapp' => '',
                'admin_phone' => '',
                'whatsapp_message_template' => "السلام عليكم إدارة تطبيق نجاح، أرغب في تفعيل أو تجديد اشتراكي.\nالاسم: {owner_name}\nرقم الحساب: #{owner_id}\nالهاتف: {owner_phone}\nالباقة المطلوبة: {plan_name}",
                'support_note' => 'عند انتهاء الباقة يمكنك التواصل مع الإدارة للحصول على كود التفعيل وإدخاله هنا لتفعيل باقتك فوراً.',
                'currency_label' => 'ر.س',
                'enable_activation_codes' => true,
                'show_whatsapp_on_ios' => true,
                'show_whatsapp_on_android' => true,
            ];
            if (\Illuminate\Support\Facades\Schema::hasColumn('subscription_settings', 'show_subscriptions_in_app')) {
                $defaults['show_subscriptions_in_app'] = true;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('subscription_settings', 'show_subscriptions_on_ios')) {
                $defaults['show_subscriptions_on_ios'] = true;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('subscription_settings', 'show_subscriptions_on_android')) {
                $defaults['show_subscriptions_on_android'] = true;
            }
            $setting = static::create($defaults);
        }

        return $setting;
    }

    public function toApiArray(): array
    {
        $showInApp = array_key_exists('show_subscriptions_in_app', $this->attributes)
            ? (bool) $this->show_subscriptions_in_app
            : true;
        $showOnIos = array_key_exists('show_subscriptions_on_ios', $this->attributes)
            ? (bool) $this->show_subscriptions_on_ios
            : true;
        $showOnAndroid = array_key_exists('show_subscriptions_on_android', $this->attributes)
            ? (bool) $this->show_subscriptions_on_android
            : true;

        return [
            'admin_whatsapp' => (string) ($this->admin_whatsapp ?? ''),
            'admin_phone' => (string) ($this->admin_phone ?? ''),
            'whatsapp_message_template' => (string) ($this->whatsapp_message_template ?? ''),
            'support_note' => (string) ($this->support_note ?? ''),
            'currency_label' => (string) ($this->currency_label ?: 'ر.س'),
            'enable_activation_codes' => (bool) $this->enable_activation_codes,
            'show_subscriptions_in_app' => $showInApp,
            'show_subscriptions_on_ios' => $showOnIos,
            'show_subscriptions_on_android' => $showOnAndroid,
            'show_whatsapp_on_ios' => (bool) $this->show_whatsapp_on_ios,
            'show_whatsapp_on_android' => (bool) $this->show_whatsapp_on_android,
        ];
    }
}
