<?php

namespace App\Http\Controllers;

use App\Models\AppVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AppVersionController extends Controller
{
    public function addVersions()
    {
        $appVersion = AppVersion::getSettings();

        return view('admin.app_version.app_version_add', compact('appVersion'));
    }

    public function updateVersions(Request $request)
    {
        // Validate incoming data
        $request->validate([
            'version'                 => 'nullable|string|max:100',
            'ios_version'             => 'nullable|string|max:50',
            'android_version'         => 'nullable|string|max:50',
            'ios'                     => 'nullable|url',
            'android'                 => 'nullable|url',
            'des'                     => 'nullable|string|max:500',
            'ios_des'                 => 'nullable|string|max:500',
            'android_des'             => 'nullable|string|max:500',
            'ios_update_required'     => 'nullable|in:yes,no',
            'android_update_required' => 'nullable|in:yes,no',
            'whatsapp_contact'        => 'nullable|string|max:30',
            'inactive_owner_message'  => 'nullable|string|max:500',
        ], [
            'ios.url'                    => 'رابط App Store يجب أن يكون رابطًا صحيحًا.',
            'android.url'                => 'رابط Google Play يجب أن يكون رابطًا صحيحًا.',
            'des.string'                 => 'يجب أن يكون الوصف نصًا.',
            'des.max'                    => 'يجب ألا يتجاوز الوصف 500 حرف.',
            'ios_des.max'                => 'يجب ألا يتجاوز وصف تحديث iOS 500 حرف.',
            'android_des.max'            => 'يجب ألا يتجاوز وصف تحديث Android 500 حرف.',
            'whatsapp_contact.max'       => 'رقم الواتساب يجب ألا يتجاوز 30 حرفاً.',
            'inactive_owner_message.max' => 'رسالة الحساب غير المفعل يجب ألا تتجاوز 500 حرف.',
        ]);

        $iosVersion = trim((string) ($request->ios_version ?: ($request->version ?: '1.0.0')));
        $androidVersion = trim((string) ($request->android_version ?: ($request->version ?: '1.0.0')));
        $iosRequired = $request->input('ios_update_required', 'no') === 'yes' ? 'yes' : 'no';
        $androidRequired = $request->input('android_update_required', 'no') === 'yes' ? 'yes' : 'no';
        $iosDes = trim((string) ($request->ios_des ?? ''));
        $androidDes = trim((string) ($request->android_des ?? ''));
        $fallbackDes = trim((string) ($request->des ?? ($iosDes ?: $androidDes)));

        $payload = [
            'version'                 => $androidVersion ?: $iosVersion,
            'ios'                     => trim((string) ($request->ios ?? '')),
            'android'                 => trim((string) ($request->android ?? '')),
            'des'                     => $fallbackDes,
            'app_type'                => $request->app_type ?? 'user',
            'update_required'         => ($iosRequired === 'yes' || $androidRequired === 'yes') ? 'yes' : 'no',
            'stop_owner_registration' => $request->boolean('stop_owner_registration'),
            'whatsapp_contact'        => trim((string) ($request->whatsapp_contact ?? '')),
            'inactive_owner_message'  => trim((string) ($request->inactive_owner_message ?? '')),
        ];

        if (Schema::hasColumn('app_versions', 'ios_version')) {
            $payload['ios_version'] = $iosVersion;
            $payload['android_version'] = $androidVersion;
            $payload['ios_update_enabled'] = $request->boolean('ios_update_enabled');
            $payload['android_update_enabled'] = $request->boolean('android_update_enabled');
            $payload['ios_update_required'] = $iosRequired;
            $payload['android_update_required'] = $androidRequired;
            $payload['ios_des'] = $iosDes;
            $payload['android_des'] = $androidDes;
        }

        AppVersion::updateOrCreate(['id' => 1], $payload);

        $notification = [
            'message'    => 'تم تحديث إعدادات التطبيق وإصدارات iOS و Android بنجاح',
            'alert-type' => 'success',
        ];

        return back()->with($notification);
    }

    /// Api

    public function getSettingApp($id)
    {
        $answer = AppVersion::where('id', $id)->first() ?? AppVersion::getSettings();
        $inactivePayload = AppVersion::getOwnerInactivePayload();

        $legacyVersion = (string) ($answer->version ?? '1.0.0');
        $legacyRequired = (string) ($answer->update_required ?? 'no');
        $legacyDes = (string) ($answer->des ?? 'يتوفر تحديث جديد للتطبيق.');

        $iosVersion = trim((string) ($answer->ios_version ?? '')) ?: $legacyVersion;
        $androidVersion = trim((string) ($answer->android_version ?? '')) ?: $legacyVersion;
        $iosUpdateEnabled = array_key_exists('ios_update_enabled', $answer->getAttributes())
            ? (bool) $answer->ios_update_enabled
            : true;
        $androidUpdateEnabled = array_key_exists('android_update_enabled', $answer->getAttributes())
            ? (bool) $answer->android_update_enabled
            : true;
        $iosUpdateRequired = trim((string) ($answer->ios_update_required ?? '')) ?: $legacyRequired;
        $androidUpdateRequired = trim((string) ($answer->android_update_required ?? '')) ?: $legacyRequired;
        $iosDes = trim((string) ($answer->ios_des ?? '')) ?: $legacyDes;
        $androidDes = trim((string) ($answer->android_des ?? '')) ?: $legacyDes;

        $data = [
            'id'                      => $answer->id ?? 1,
            'version'                 => $legacyVersion,
            'ios_version'             => $iosVersion,
            'android_version'         => $androidVersion,
            'des'                     => $legacyDes,
            'ios_des'                 => $iosDes,
            'android_des'             => $androidDes,
            'android'                 => (string) ($answer->android ?? ''),
            'ios'                     => (string) ($answer->ios ?? ''),
            'app_type'                => $answer->app_type ?? 'user',
            'update_required'         => $legacyRequired,
            'ios_update_enabled'      => $iosUpdateEnabled,
            'android_update_enabled'  => $androidUpdateEnabled,
            'ios_update_required'     => $iosUpdateRequired,
            'android_update_required' => $androidUpdateRequired,
            'login_method'            => 'phone',
            'otp_method'              => 'WhatsApp',
            'contact'                 => $inactivePayload['contact'],
            'contact_phone'           => $inactivePayload['whatsapp_contact'],
            'stop_owner_registration' => (bool) ($answer->stop_owner_registration ?? false),
            'whatsapp_contact'        => $inactivePayload['whatsapp_contact'],
            'inactive_owner_message'  => $inactivePayload['inactive_message'],
        ];

        return response()->json($data);
    }
}
