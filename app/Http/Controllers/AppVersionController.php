<?php

namespace App\Http\Controllers;

use App\Models\AppVersion;
use Illuminate\Http\Request;

class AppVersionController extends Controller
{
    public function addVersions() {
        $appVersion = AppVersion::getSettings();

        return view('admin.app_version.app_version_add', compact('appVersion'));
    }

    public function updateVersions(Request $request) {
        // Validate incoming data
        $request->validate([
            'version'                => 'nullable|string|max:100',
            'ios'                    => 'nullable|url',
            'android'                => 'nullable|url',
            'des'                    => 'nullable|string|max:500',
            'whatsapp_contact'       => 'nullable|string|max:30',
            'inactive_owner_message' => 'nullable|string|max:500',
        ], [
            'ios.url'                    => 'رابط App Store يجب أن يكون رابطًا صحيحًا.',
            'android.url'                => 'رابط Google Play يجب أن يكون رابطًا صحيحًا.',
            'des.string'                 => 'يجب أن يكون الوصف نصًا.',
            'des.max'                    => 'يجب ألا يتجاوز الوصف 500 حرف.',
            'whatsapp_contact.max'       => 'رقم الواتساب يجب ألا يتجاوز 30 حرفاً.',
            'inactive_owner_message.max' => 'رسالة الحساب غير المفعل يجب ألا تتجاوز 500 حرف.',
        ]);

        // Save or update the settings in the database
        AppVersion::updateOrCreate(
            ['id' => 1],
            [
                'version'                 => $request->version ?? '1.0.0',
                'ios'                     => $request->ios ?? '',
                'android'                 => $request->android ?? '',
                'des'                     => $request->des,
                'app_type'                => $request->app_type ?? 'user',
                'update_required'         => $request->update_required ?? 'no',
                'stop_owner_registration' => $request->boolean('stop_owner_registration'),
                'whatsapp_contact'        => trim((string) ($request->whatsapp_contact ?? '')),
                'inactive_owner_message'  => trim((string) ($request->inactive_owner_message ?? '')),
            ]
        );

        $notification = array(
            'message'    => 'تم تحديث اعدادات التطبيق بنجاح',
            'alert-type' => 'success'
        );

        return back()->with($notification);
    }


    /// Api

    public function getSettingApp($id)
    {
        $answer = AppVersion::where('id', $id)->first() ?? AppVersion::getSettings();
        $inactivePayload = AppVersion::getOwnerInactivePayload();

        $data = [
            'id'                      => $answer->id ?? 1,
            'version'                 => $answer->version ?? '1.0',
            'des'                     => $answer->des ?? 'Nagah Racing',
            'android'                 => $answer->android ?? '',
            'ios'                     => $answer->ios ?? '',
            'app_type'                => $answer->app_type ?? 'user',
            'update_required'         => $answer->update_required ?? '0',
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
