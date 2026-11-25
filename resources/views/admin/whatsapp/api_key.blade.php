@extends('admin.master_admin')
@section('admin')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">مفتاح API لبوابة الواتساب (OTP)</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">إدارة مفتاح API لبوابة الواتساب</li>
            </ol>
        </nav>
    </div>
</div>

<hr/>

<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="mb-0 fw-bold text-success">
                    <i class="bx bxl-whatsapp fs-4 align-middle me-1"></i>
                    إدارة مفتاح الوصول (API Key) لبوابة الواتساب المستقلة
                </h5>
                @if($settings->is_connected)
                    <span class="badge bg-success px-3 py-2">متصل حالياً: +{{ $settings->connected_phone }}</span>
                @else
                    <span class="badge bg-secondary px-3 py-2">غير متصل حالياً</span>
                @endif
            </div>
            <div class="card-body p-4">

                <div class="alert alert-light border mb-4">
                    <i class="bx bx-shield-quarter text-primary fs-5 align-middle me-1"></i>
                    صفحة ربط الواتساب ومسح رمز <strong>QR Code</strong> موجودة في <strong>صفحة مستقلة خارج لوحة التحكم</strong>، ولا يمكن فتحها أو استخدام الـ API للإرسال إلا عبر مفتاح الـ API التالي.
                </div>

                <!-- API Key Display -->
                <div class="p-4 bg-light rounded-3 border mb-4">
                    <label class="form-label fw-bold text-muted small mb-2">مفتاح الـ API الحالي (سري):</label>
                    <div class="input-group input-group-lg mb-3">
                        <input type="text"
                               id="whatsappApiKeyField"
                               dir="ltr"
                               class="form-control font-monospace bg-white text-primary fw-bold"
                               value="{{ $settings->api_key }}"
                               readonly>
                        <button type="button" class="btn btn-outline-primary px-4" onclick="copyValue('whatsappApiKeyField')">
                            <i class="bx bx-copy me-1"></i> نسخ المفتاح
                        </button>
                    </div>

                    <form action="{{ route('admin.whatsapp.api_key.regenerate') }}"
                          method="POST"
                          onsubmit="return confirm('تنبيه: توليد مفتاح جديد سيؤدي فوراً إلى إغلاق أي جلسة مفتوحة لصفحة الربط المستقلة وإيقاف أي استخدام للمفتاح القديم. هل تريد المتابعة؟');">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="bx bx-refresh me-1"></i> توليد مفتاح API جديد وإلغاء القديم
                        </button>
                    </form>
                </div>

                <!-- Standalone Page URL -->
                <div class="p-4 bg-light rounded-3 border mb-4">
                    <label class="form-label fw-bold text-muted small mb-2">رابط صفحة الربط المستقلة (خارج لوحة التحكم — QR Code):</label>
                    <div class="input-group mb-3">
                        <input type="text"
                               id="gatewayUrlField"
                               dir="ltr"
                               class="form-control font-monospace bg-white"
                               value="{{ $gatewayUrl }}"
                               readonly>
                        <button type="button" class="btn btn-outline-secondary" onclick="copyValue('gatewayUrlField')">
                            <i class="bx bx-copy me-1"></i> نسخ الرابط
                        </button>
                        <a href="{{ $gatewayUrl }}" target="_blank" class="btn btn-success">
                            <i class="bx bx-link-external me-1"></i> فتح صفحة الربط المستقلة
                        </a>
                    </div>
                    <small class="text-muted">
                        عند فتح الصفحة المستقلة سيُطلب إدخال مفتاح الـ API أعلاه قبل عرض رمز QR Code أو أدوات الربط.
                    </small>
                </div>

                <!-- Quick API Endpoint Reference -->
                <div class="p-3 rounded-3 border bg-white">
                    <h6 class="fw-bold mb-2"><i class="bx bx-code-alt text-success me-1"></i> رابط الإرسال البرمجي (REST API):</h6>
                    <code class="d-block p-3 bg-dark text-white rounded text-break" dir="ltr">
                        {{ url('/api/whatsapp/send') }}?apikey={{ $settings->api_key }}&amp;phone=96550000000&amp;text=123456
                    </code>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    function copyValue(elementId) {
        var el = document.getElementById(elementId);
        if (!el) return;
        navigator.clipboard.writeText(el.value).then(function() {
            alert('تم النسخ بنجاح!');
        }, function() {
            el.select();
            document.execCommand('copy');
            alert('تم النسخ بنجاح!');
        });
    }
</script>

@endsection
