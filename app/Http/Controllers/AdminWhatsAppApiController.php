<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;

class AdminWhatsAppApiController extends Controller
{
    protected WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * صفحة إدارة مفتاح API لبوابة الواتساب داخل لوحة التحكم فقط
     * (الربط و QR Code يتم من خلال الصفحة المستقلة خارج لوحة التحكم)
     */
    public function index()
    {
        $settings = $this->whatsappService->getSettings();
        $gatewayUrl = route('whatsapp.gateway.index');

        return view('admin.whatsapp.api_key', compact('settings', 'gatewayUrl'));
    }

    /**
     * توليد مفتاح API جديد وإلغاء المفتاح السابق فوراً
     */
    public function regenerateApiKey()
    {
        $newKey = $this->whatsappService->regenerateApiKey();

        $notification = [
            'message'    => 'تم توليد مفتاح API جديد بنجاح وإلغاء المفتاح القديم: ' . $newKey,
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);
    }
}
