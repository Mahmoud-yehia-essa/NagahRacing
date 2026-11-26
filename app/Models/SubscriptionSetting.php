<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'enable_activation_codes' => 'boolean',
        'show_whatsapp_on_ios' => 'boolean',
        'show_whatsapp_on_android' => 'boolean',
    ];

    public static function current(): self
    {
        $setting = static::first();
        if (!$setting) {
            $setting = static::create([
                'admin_whatsapp' => '',
                'admin_phone' => '',
                'whatsapp_message_template' => "السلام عليكم إدارة تطبيق نجاح، أرغب في تفعيل أو تجديد اشتراكي.\nالاسم: {owner_name}\nرقم الحساب: #{owner_id}\nالهاتف: {owner_phone}\nالباقة المطلوبة: {plan_name}",
                'support_note' => 'عند انتهاء الباقة يمكنك التواصل مع الإدارة للحصول على كود التفعيل وإدخاله هنا لتفعيل باقتك فوراً.',
                'currency_label' => 'ر.س',
                'enable_activation_codes' => true,
                'show_whatsapp_on_ios' => true,
                'show_whatsapp_on_android' => true,
            ]);
        }

        return $setting;
    }

    public function toApiArray(): array
    {
        return [
            'admin_whatsapp' => (string) ($this->admin_whatsapp ?? ''),
            'admin_phone' => (string) ($this->admin_phone ?? ''),
            'whatsapp_message_template' => (string) ($this->whatsapp_message_template ?? ''),
            'support_note' => (string) ($this->support_note ?? ''),
            'currency_label' => (string) ($this->currency_label ?: 'ر.س'),
            'enable_activation_codes' => (bool) $this->enable_activation_codes,
            'show_whatsapp_on_ios' => (bool) $this->show_whatsapp_on_ios,
            'show_whatsapp_on_android' => (bool) $this->show_whatsapp_on_android,
        ];
    }
}
