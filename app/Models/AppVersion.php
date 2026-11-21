<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'stop_owner_registration' => 'boolean',
        ];
    }

    public static function getSettings(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'version'                 => '1.0.0',
                'des'                     => 'Nagah Racing',
                'android'                 => '',
                'ios'                     => '',
                'app_type'                => 'user',
                'update_required'         => 'no',
                'stop_owner_registration' => false,
                'whatsapp_contact'        => '+96551673464',
                'inactive_owner_message'  => 'حسابك غير مفعل حالياً، الرجاء التواصل مع الإدارة عبر الواتساب لتفعيل الحساب.',
            ]
        );
    }

    public static function isOwnerRegistrationStopped(): bool
    {
        try {
            $settings = self::getSettings();
            return (bool) ($settings->stop_owner_registration ?? false);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function getOwnerInactivePayload(): array
    {
        $whatsapp = '+96551673464';
        $message = 'حسابك غير مفعل حالياً، الرجاء التواصل مع الإدارة عبر الواتساب لتفعيل الحساب.';

        try {
            $settings = self::getSettings();
            $configuredWhatsapp = trim((string) ($settings->whatsapp_contact ?? ''));
            if ($configuredWhatsapp !== '') {
                $whatsapp = $configuredWhatsapp;
            }
            $configuredMessage = trim((string) ($settings->inactive_owner_message ?? ''));
            if ($configuredMessage !== '') {
                $message = $configuredMessage;
            }
        } catch (\Throwable $e) {
            // Fall back to safe defaults
        }

        $digits = preg_replace('/[^0-9]/', '', $whatsapp);
        $contactUrl = $digits !== '' ? 'https://wa.me/' . $digits : 'https://wa.me/96551673464';

        return [
            'account_inactive' => true,
            'whatsapp_contact' => $whatsapp,
            'contact'          => $contactUrl,
            'inactive_message' => $message,
        ];
    }
}
