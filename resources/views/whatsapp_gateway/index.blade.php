<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الربط مع الواتساب (WhatsApp OTP Gateway) - نجاح ريسنج</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f4f6f9;
            min-height: 100vh;
        }
        .gateway-topbar {
            background: linear-gradient(135deg, #290612 0%, #420c1f 100%);
            border-bottom: 3px solid #E9C349;
            padding: 16px 0;
            color: #fff;
        }
        .stat-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.06) !important;
        }
        .custom-tab-btn {
            color: #475569;
            font-weight: 700;
            padding: 10px 18px;
            border-radius: 10px !important;
            transition: all 0.2s ease;
        }
        .custom-tab-btn:hover {
            background-color: #f1f5f9;
            color: #1e293b;
        }
        .custom-tab-btn.active {
            background-color: #25D366 !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.35);
        }
        .scanner-laser-line {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, #25D366, transparent);
            box-shadow: 0 0 12px #25D366;
            animation: scanAnim 2.2s infinite ease-in-out;
        }
        @keyframes scanAnim {
            0% { top: 5%; opacity: 0.8; }
            50% { top: 95%; opacity: 1; }
            100% { top: 5%; opacity: 0.8; }
        }
        .pulse-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            animation: pulseEffect 1.5s infinite;
        }
        @keyframes pulseEffect {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(255, 255, 255, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
        }
    </style>
</head>
<body>

    <!-- Standalone Top Header -->
    <header class="gateway-topbar shadow-sm mb-4">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-circle"
                         style="width: 48px; height: 48px; background: linear-gradient(135deg, #128C7E, #25D366);">
                        <i class="bx bxl-whatsapp fs-2 text-white"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold text-white">بوابة الربط مع الواتساب (WhatsApp OTP Gateway)</h5>
                        <small style="color: #E9C349;">نجاح ريسنج — صفحة ربط مستقلة محمية بمفتاح API</small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div id="topStatusIndicator">
                        @if(!empty($status['is_connected']))
                            <span class="badge bg-success px-3 py-2 fs-6 shadow-sm d-flex align-items-center gap-2">
                                <span class="pulse-dot bg-white"></span>
                                <span>متصل: +{{ $status['phone'] ?? $settings->connected_phone }}</span>
                            </span>
                        @elseif(($status['status'] ?? '') === 'reconnecting')
                            <span class="badge bg-info text-dark px-3 py-2 fs-6 shadow-sm d-flex align-items-center gap-2">
                                <i class="bx bx-loader-alt bx-spin fs-5"></i>
                                <span>جاري استعادة الاتصال...</span>
                            </span>
                        @elseif(($status['status'] ?? '') === 'waiting_for_scan')
                            <span class="badge bg-warning text-dark px-3 py-2 fs-6 shadow-sm d-flex align-items-center gap-2">
                                <i class="bx bx-qr-scan fs-5"></i>
                                <span>بانتظار مسح رمز QR</span>
                            </span>
                        @elseif(empty($status['online']) || ($status['status'] ?? '') === 'service_offline')
                            <span class="badge bg-danger px-3 py-2 fs-6 shadow-sm d-flex align-items-center gap-2">
                                <i class="bx bx-power-off fs-5"></i>
                                <span>الخادم متوقف</span>
                            </span>
                        @else
                            <span class="badge bg-secondary px-3 py-2 fs-6 shadow-sm d-flex align-items-center gap-2">
                                <i class="bx bx-power-off fs-5"></i>
                                <span>غير متصل</span>
                            </span>
                        @endif
                    </div>

                    <form action="{{ route('whatsapp.gateway.lock') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-warning px-3 py-2 fw-bold">
                            <i class="bx bx-lock-alt me-1"></i> قفل الصفحة
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div class="container pb-5">
        <!-- Session Alert Messages -->
        @if(session('message'))
            <div class="alert alert-{{ session('alert-type') == 'success' ? 'success' : (session('alert-type') == 'warning' ? 'warning' : 'danger') }} border-0 alert-dismissible fade show mb-4 shadow-sm" style="border-radius: 12px;">
                <div class="d-flex align-items-center">
                    <i class="bx {{ session('alert-type') == 'success' ? 'bx-check-circle' : 'bx-error-circle' }} fs-3 me-2"></i>
                    <div>
                        <strong>تنبيه النظام:</strong> {{ session('message') }}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Stats Row -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 stat-card" style="border-radius: 14px; border-right: 5px solid #25D366 !important;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="d-block mb-1 text-muted small fw-bold">حالة الاتصال</span>
                                <h5 class="mb-1 fw-bold" id="cardStatusText" style="color: {{ !empty($status['is_connected']) ? '#059669' : '#dc2626' }};">
                                    {{ !empty($status['is_connected']) ? 'متصل بنجاح' : 'غير متصل' }}
                                </h5>
                                <small class="text-muted d-block" id="cardConnectedPhone" dir="ltr">
                                    {{ !empty($status['phone']) ? '+' . $status['phone'] : 'لا يوجد رقم مقترن' }}
                                </small>
                            </div>
                            <div class="d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; border-radius: 12px; background: #ecfdf5; color: #059669;">
                                <i class="bx bxl-whatsapp fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 stat-card" style="border-radius: 14px; border-right: 5px solid #3b82f6 !important;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="d-block mb-1 text-muted small fw-bold">إجمالي الرسائل المرسلة</span>
                                <h3 class="mb-0 fw-bold text-dark">{{ number_format($totalSent ?? 0) }}</h3>
                                <small class="text-success"><i class="bx bx-check-double me-1"></i> عبر الواتساب</small>
                            </div>
                            <div class="d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; border-radius: 12px; background: #eff6ff; color: #2563eb;">
                                <i class="bx bx-send fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 stat-card" style="border-radius: 14px; border-right: 5px solid #8b5cf6 !important;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="d-block mb-1 text-muted small fw-bold">رسائل اليوم</span>
                                <h3 class="mb-0 fw-bold text-dark">{{ number_format($todaySent ?? 0) }}</h3>
                                <small class="text-primary"><i class="bx bx-time-five me-1"></i> خلال اليوم</small>
                            </div>
                            <div class="d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; border-radius: 12px; background: #f5f3ff; color: #7c3aed;">
                                <i class="bx bx-message-dots fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 stat-card" style="border-radius: 14px; border-right: 5px solid #f59e0b !important;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="d-block mb-1 text-muted small fw-bold">المحرك المعتمد</span>
                                <h6 class="mb-1 fw-bold text-dark">
                                    {{ $settings->engine === 'textmebot' ? 'بوابة TextMeBot' : 'السيرفر المباشر (Baileys)' }}
                                </h6>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                    {{ $settings->engine === 'textmebot' ? 'سحابي خارجي' : 'داخلي مستقل' }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; border-radius: 12px; background: #fffbeb; color: #d97706;">
                                <i class="bx bx-server fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Navigation Tabs -->
        <div class="card shadow-sm border-0" style="border-radius: 14px;">
            <div class="card-header bg-white border-bottom p-3">
                <ul class="nav nav-pills card-header-pills gap-2" id="whatsappTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link custom-tab-btn {{ session('active_tab', 'connect') == 'connect' ? 'active' : '' }}" id="connect-tab" data-bs-toggle="pill" data-bs-target="#connectTabPane" type="button" role="tab">
                            <i class="bx bx-qr-scan me-1"></i> ربط الرقم (QR Code)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link custom-tab-btn {{ session('active_tab') == 'send_message' ? 'active' : '' }}" id="send-tab" data-bs-toggle="pill" data-bs-target="#sendTabPane" type="button" role="tab">
                            <i class="bx bx-paper-plane me-1"></i> إرسال رسالة تجريبية
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link custom-tab-btn {{ session('active_tab') == 'logs' ? 'active' : '' }}" id="logs-tab" data-bs-toggle="pill" data-bs-target="#logsTabPane" type="button" role="tab">
                            <i class="bx bx-history me-1"></i> سجل الرسائل المرسلة
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link custom-tab-btn {{ session('active_tab') == 'api_docs' ? 'active' : '' }}" id="api-tab" data-bs-toggle="pill" data-bs-target="#apiTabPane" type="button" role="tab">
                            <i class="bx bx-code-curly me-1"></i> توثيق واجهة البرمجة (REST API)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link custom-tab-btn {{ session('active_tab') == 'settings' ? 'active' : '' }}" id="settings-tab" data-bs-toggle="pill" data-bs-target="#settingsTabPane" type="button" role="tab">
                            <i class="bx bx-cog me-1"></i> إعدادات المحرك
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4 p-md-5">
                <div class="tab-content" id="whatsappTabsContent">

                    <!-- TAB 1: QR Code & Connection -->
                    <div class="tab-pane fade {{ session('active_tab', 'connect') == 'connect' ? 'show active' : '' }}" id="connectTabPane" role="tabpanel">
                        <div class="row align-items-center justify-content-center">

                            <!-- Connected View Container -->
                            <div class="col-lg-8" id="connectedContainer" style="display: {{ !empty($status['is_connected']) ? 'block' : 'none' }};">
                                <div class="card border-0 shadow-sm p-4 text-center" style="background: linear-gradient(145deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 16px; border: 2px solid #86efac !important;">
                                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 50%; background: #22c55e; color: white;">
                                        <i class="bx bx-check fs-1"></i>
                                    </div>
                                    <h4 class="fw-bold text-success mb-2">رقمك متصل وجاهز لإرسال OTP!</h4>
                                    <p class="text-secondary mb-3">تم ربط حساب الواتساب بنجاح، ويمكن لتطبيق نجاح ريسنج والـ API إرسال رموز التحقق والرسائل الآن.</p>

                                    <div class="bg-white p-3 rounded-3 shadow-sm d-inline-block mx-auto mb-4" style="min-width: 320px; border: 1px solid #bbf7d0;">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="text-muted small">رقم الهاتف المتصل:</span>
                                            <span class="fw-bold text-dark fs-6" dir="ltr" id="connectedPhoneDisplay">+{{ $status['phone'] ?? $settings->connected_phone }}</span>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="text-muted small">اسم الحساب:</span>
                                            <span class="fw-bold text-dark" id="connectedNameDisplay">{{ $status['name'] ?? $settings->connected_name ?? 'Nagah Racing WhatsApp' }}</span>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="text-muted small">تاريخ الاتصال:</span>
                                            <span class="text-muted small">{{ $settings->last_connected_at ? $settings->last_connected_at->format('Y-m-d H:i') : now()->format('Y-m-d H:i') }}</span>
                                        </div>
                                    </div>

                                    <div>
                                        <form action="{{ route('whatsapp.gateway.disconnect') }}" method="POST" onsubmit="return confirm('هل أنت متأكد من رغبتك في قطع الاتصال وفك الارتباط؟ ستحتاج لمسح الـ QR مجدداً.');">
                                            @csrf
                                            <button type="submit" class="btn btn-danger px-4 py-2 rounded-pill shadow-sm">
                                                <i class="bx bx-power-off me-1"></i> قطع الاتصال وفك الارتباط
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Disconnected / Scan QR Container -->
                            <div class="col-lg-10" id="scanQrContainer" style="display: {{ empty($status['is_connected']) ? 'block' : 'none' }};">
                                <div class="row align-items-center g-4">
                                    <!-- QR Box Column -->
                                    <div class="col-md-5 text-center">
                                        <div class="p-4 bg-white rounded-4 shadow-sm border position-relative d-inline-block">
                                            @php
                                                $isOffline = empty($status['online']) || (($status['status'] ?? '') === 'service_offline');
                                                $isReconnecting = (($status['status'] ?? '') === 'reconnecting');
                                                $hasQr = !empty($status['qr']);
                                                $portDisplay = $status['port'] ?? 3002;
                                            @endphp

                                            <!-- 1. Service Offline Container -->
                                            <div id="serviceOfflineContainer" style="display: {{ $isOffline ? 'flex' : 'none' }}; width: 240px; height: 240px;" class="align-items-center justify-content-center flex-column text-center p-3">
                                                <div class="text-danger mb-2">
                                                    <i class="bx bx-power-off" style="font-size: 3rem;"></i>
                                                </div>
                                                <h6 class="fw-bold text-danger mb-1">خادم الواتساب متوقف</h6>
                                                <p class="text-muted small mb-3" style="font-size: 0.78rem;">محرك Node.js غير نشط على المنفذ {{ $portDisplay }}.</p>
                                                <button type="button" class="btn btn-sm btn-success px-3 py-2 rounded-pill shadow-sm" id="btnStartService" onclick="startWhatsAppService()">
                                                    <i class="bx bx-play me-1"></i> تشغيل خادم الواتساب الآن
                                                </button>
                                            </div>

                                            <!-- 2. Reconnecting Container -->
                                            <div id="reconnectingContainer" style="display: {{ (!$isOffline && $isReconnecting) ? 'flex' : 'none' }}; width: 240px; height: 240px;" class="align-items-center justify-content-center flex-column text-center p-3">
                                                <div class="spinner-border text-primary mb-3" style="width: 2.5rem; height: 2.5rem;" role="status"></div>
                                                <h6 class="fw-bold text-primary mb-1">جاري استعادة الاتصال...</h6>
                                                <p class="text-muted small mb-2" style="font-size: 0.78rem;">يتم الاتصال تلقائياً بالجلسة السابقة دون الحاجة لإعادة المسح.</p>
                                                <span class="badge bg-light text-primary border" dir="ltr" id="reconnectingPhoneBadge" style="display: {{ !empty($status['phone']) ? 'inline-block' : 'none' }};">{{ !empty($status['phone']) ? ('+' . $status['phone']) : '' }}</span>
                                            </div>

                                            <!-- 3. Loading QR Spinner -->
                                            <div id="qrLoadingSpinner" style="display: {{ (!$isOffline && !$isReconnecting && !$hasQr) ? 'flex' : 'none' }}; width: 240px; height: 240px;" class="align-items-center justify-content-center flex-column text-center">
                                                <div class="spinner-border text-success mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                                                <span class="text-muted small fw-bold">جاري تهيئة رمز QR...</span>
                                            </div>

                                            <!-- 4. QR Image -->
                                            <div id="qrImageWrapper" style="display: {{ (!$isOffline && !$isReconnecting && $hasQr) ? 'block' : 'none' }}; position: relative;">
                                                <img id="whatsappQrImage" src="{{ $status['qr'] ?? '' }}" alt="WhatsApp QR Code" class="img-fluid rounded" style="width: 240px; height: 240px; object-fit: contain;">
                                                <div class="scanner-laser-line"></div>
                                            </div>

                                            <div class="mt-3">
                                                <span class="badge bg-light text-secondary border px-3 py-2 small" id="qrRefreshNotice">
                                                    @if($isOffline)
                                                        <i class="bx bx-error text-danger me-1"></i> الخادم متوقف
                                                    @elseif($isReconnecting)
                                                        <i class="bx bx-loader bx-spin text-primary me-1"></i> جاري استعادة الاتصال...
                                                    @else
                                                        <i class="bx bx-refresh bx-spin me-1 text-success"></i> فحص الاتصال التلقائي نشط
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Instructions Column -->
                                    <div class="col-md-7">
                                        <div class="ps-md-3">
                                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold mb-2">
                                                <i class="bx bx-shield-quarter me-1"></i> ربط آمن ومباشر
                                            </span>
                                            <h4 class="fw-bold text-dark mb-3">خطوات ربط رقم الواتساب (لإرسال OTP):</h4>

                                            <ol class="list-group list-group-numbered border-0 mb-4">
                                                <li class="list-group-item border-0 px-0">افتح تطبيق <strong>واتساب (WhatsApp)</strong> على هاتفك.</li>
                                                <li class="list-group-item border-0 px-0">اضغط على <strong>القائمة (ثلاث نقاط)</strong> في أندرويد أو <strong>الإعدادات</strong> في آيفون.</li>
                                                <li class="list-group-item border-0 px-0">اختر <strong>الأجهزة المرتبطة (Linked Devices)</strong> ثم <strong>ربط جهاز (Link a Device)</strong>.</li>
                                                <li class="list-group-item border-0 px-0">وجّه كاميرا هاتفك نحو رمز <strong>QR Code</strong> المقابل، وسيتم الربط تلقائياً خلال ثوانٍ!</li>
                                            </ol>

                                            <!-- Alternative: Pairing Code -->
                                            <div class="card border bg-light p-3 rounded-3" style="border: 1px dashed #cbd5e1 !important;">
                                                <h6 class="fw-bold text-dark mb-2">
                                                    <i class="bx bx-mobile-alt text-primary me-1"></i> أو الربط بواسطة كود الاقتران (Pairing Code)
                                                </h6>
                                                <p class="text-muted small mb-3">أدخل رقم هاتفك مع كود الدولة لتوليد كود مكون من 8 أحرف لربطه في الواتساب مباشرة دون كاميرا.</p>

                                                <div class="input-group">
                                                    <input type="text" id="pairingPhoneInput" dir="ltr" class="form-control" placeholder="96550000000 أو 966500000000">
                                                    <button type="button" id="btnRequestPairingCode" class="btn btn-outline-success fw-bold">
                                                        <i class="bx bx-key me-1"></i> طلب كود الاقتران
                                                    </button>
                                                </div>

                                                <div id="pairingCodeResult" class="mt-3 p-3 bg-white border rounded text-center" style="display: none;">
                                                    <span class="text-muted small d-block mb-1">أدخل هذا الكود في هاتفك (ربط عبر رقم الهاتف):</span>
                                                    <h3 class="font-monospace text-success fw-bold mb-0" dir="ltr" id="pairingCodeDisplay"></h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- TAB 2: Send Test Message -->
                    <div class="tab-pane fade {{ session('active_tab') == 'send_message' ? 'show active' : '' }}" id="sendTabPane" role="tabpanel">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <form action="{{ route('whatsapp.gateway.send') }}" method="POST">
                                    @csrf

                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-dark">
                                            <i class="bx bx-phone text-success me-1"></i> رقم هاتف المستلم (مع كود الدولة) <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="phone" dir="ltr" value="{{ old('phone') }}" class="form-control form-control-lg @error('phone') is-invalid @enderror" placeholder="96550123456 أو 966501234567 (بدون +)" required>
                                        <small class="text-muted d-block mt-1">يجب أن يبدأ الرقم بمفتاح الدولة مباشرة (مثال: للكويت 965، للسعودية 966، للإمارات 971).</small>
                                        @error('phone')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Quick Templates -->
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold">قوالب نصوص سريعة:</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill btn-quick-template" data-text="رمز التحقق (OTP) الخاص بك في تطبيق نجاح ريسنج هو: 849201. لا تشارك هذا الرمز مع أي شخص.">رمز OTP تجريبي</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill btn-quick-template" data-text="مرحباً بك في تطبيق نجاح ريسنج! تم تفعيل حسابك كمالك بنجاح.">تفعيل حساب مالك</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill btn-quick-template" data-text="رسالة تجريبية للتأكد من عمل بوابة الواتساب بنجاح في نجاح ريسنج.">اختبار الاتصال</button>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-dark">
                                            <i class="bx bx-message-detail text-success me-1"></i> نص الرسالة <span class="text-danger">*</span>
                                        </label>
                                        <textarea name="message" id="messageContent" class="form-control @error('message') is-invalid @enderror" rows="5" placeholder="اكتب نص الرسالة التجريبية..." required>{{ old('message') }}</textarea>
                                        @error('message')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-success btn-lg px-5 rounded-pill shadow-sm" style="background: #25D366; border: none;">
                                            <i class="bx bxl-whatsapp me-1"></i> إرسال الرسالة الآن
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: Message Logs -->
                    <div class="tab-pane fade {{ session('active_tab') == 'logs' ? 'show active' : '' }}" id="logsTabPane" role="tabpanel">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="fw-bold text-dark mb-0">سجل الرسائل المرسلة:</h5>
                            @if($messages->isNotEmpty())
                                <form action="{{ route('whatsapp.gateway.clear_logs') }}" method="POST" onsubmit="return confirm('هل تريد بالتأكيد مسح جميع سجلات الرسائل؟');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bx bx-trash me-1"></i> مسح السجل بالكامل
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle border">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>رقم المستلم</th>
                                        <th>نص الرسالة</th>
                                        <th>المحرك</th>
                                        <th>المصدر</th>
                                        <th>الحالة</th>
                                        <th>تاريخ الإرسال</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($messages as $msg)
                                        <tr>
                                            <td>{{ $msg->id }}</td>
                                            <td class="fw-bold text-dark" dir="ltr">+{{ $msg->recipient_phone }}</td>
                                            <td style="max-width: 320px;">
                                                <div class="text-truncate" title="{{ $msg->message }}">{{ $msg->message }}</div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    {{ $msg->engine_used === 'textmebot' ? 'TextMeBot' : 'Local Baileys' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($msg->sender_type === 'otp')
                                                    <span class="badge bg-warning-subtle text-warning border">OTP التطبيق</span>
                                                @elseif($msg->sender_type === 'api')
                                                    <span class="badge bg-info-subtle text-info border">API</span>
                                                @else
                                                    <span class="badge bg-primary-subtle text-primary border">بوابة الربط</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($msg->status === 'sent')
                                                    <span class="badge bg-success-subtle text-success border px-2 py-1">
                                                        <i class="bx bx-check me-1"></i> ناجح
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger border px-2 py-1" title="{{ $msg->error_message }}">
                                                        <i class="bx bx-x me-1"></i> فاشل
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-muted small">{{ $msg->created_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <i class="bx bx-message-square-x fs-1 opacity-50 mb-2"></i>
                                                <p class="mb-0">لا توجد رسائل واتساب مسجلة حتى الآن.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $messages->links() }}
                        </div>
                    </div>

                    <!-- TAB 4: API Documentation -->
                    <div class="tab-pane fade {{ session('active_tab') == 'api_docs' ? 'show active' : '' }}" id="apiTabPane" role="tabpanel">
                        <div class="alert alert-info border-0 rounded-3 mb-4">
                            <i class="bx bx-shield-quarter me-1 fs-5 align-middle"></i>
                            يتم إنشاء أو تغيير <strong>مفتاح الـ API</strong> حصرياً من داخل لوحة التحكم (إدارة التطبيق &larr; مفتاح API للواتساب).
                        </div>

                        <!-- METHOD 1: GET -->
                        <div class="card border mb-4">
                            <div class="card-header bg-white py-3">
                                <span class="badge bg-success me-2 px-3 py-1">GET</span>
                                <strong class="text-dark">الإرسال المباشر عبر الرابط (متوافق مع TextMeBot)</strong>
                            </div>
                            <div class="card-body">
                                <div class="p-3 bg-dark rounded text-white font-monospace text-break position-relative" dir="ltr">
                                    <span>{{ url('/api/whatsapp/send') }}?apikey={{ $settings->api_key }}&phone=96550000000&text=123456</span>
                                    <button class="btn btn-sm btn-light position-absolute top-0 end-0 m-2" onclick="copyText('{{ url('/api/whatsapp/send') }}?apikey={{ $settings->api_key }}&phone=96550000000&text=123456')">
                                        <i class="bx bx-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- METHOD 2: POST -->
                        <div class="card border mb-4">
                            <div class="card-header bg-white py-3">
                                <span class="badge bg-primary me-2 px-3 py-1">POST</span>
                                <strong class="text-dark">الإرسال عبر طلب POST (JSON Body)</strong>
                            </div>
                            <div class="card-body">
                                <div class="p-3 bg-dark rounded text-white font-monospace text-break" dir="ltr">
<pre class="mb-0 text-white">curl -X POST "{{ url('/api/whatsapp/send') }}" \
  -H "Content-Type: application/json" \
  -H "x-api-key: {{ $settings->api_key }}" \
  -d '{
    "phone": "96550000000",
    "message": "رمز التحقق الخاص بك في نجاح ريسنج هو: 123456"
  }'</pre>
                                </div>
                            </div>
                        </div>

                        <!-- METHOD 3: Status -->
                        <div class="card border">
                            <div class="card-header bg-white py-3">
                                <span class="badge bg-secondary me-2 px-3 py-1">GET</span>
                                <strong class="text-dark">فحص حالة اتصال الواتساب برمجياً</strong>
                            </div>
                            <div class="card-body">
                                <div class="p-3 bg-dark rounded text-white font-monospace text-break" dir="ltr">
                                    {{ url('/api/whatsapp/status') }}?apikey={{ $settings->api_key }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 5: Engine Settings -->
                    <div class="tab-pane fade {{ session('active_tab') == 'settings' ? 'show active' : '' }}" id="settingsTabPane" role="tabpanel">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <form action="{{ route('whatsapp.gateway.settings.update') }}" method="POST">
                                    @csrf

                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-dark">
                                            <i class="bx bx-slider-alt text-success me-1"></i> اختيار محرك تشغيل الواتساب <span class="text-danger">*</span>
                                        </label>
                                        <div class="d-flex flex-column gap-3 p-3 bg-light rounded-3 border">
                                            <div class="form-check">
                                                <input class="form-check-input engine-radio" type="radio" name="engine" id="engineLocal" value="local" {{ $settings->engine === 'local' ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold text-dark" for="engineLocal" style="cursor: pointer;">
                                                    <i class="bx bx-server text-success me-1"></i> السيرفر الداخلي المباشر (Built-in Baileys Node Engine) — موصى به
                                                </label>
                                                <small class="text-muted d-block mt-1">يعمل بشكل مستقل ومجاني على السيرفر عبر مكتبة Baileys بدون أطراف ثالثة.</small>
                                            </div>

                                            <hr class="my-1">

                                            <div class="form-check">
                                                <input class="form-check-input engine-radio" type="radio" name="engine" id="engineTextMeBot" value="textmebot" {{ $settings->engine === 'textmebot' ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold text-dark" for="engineTextMeBot" style="cursor: pointer;">
                                                    <i class="bx bx-cloud text-primary me-1"></i> بوابة TextMeBot السحابية (احتياطي)
                                                </label>
                                                <small class="text-muted d-block mt-1">استخدام حساب ومفتاح TextMeBot كمحرك بديل إذا رغبت بذلك.</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Local Engine Config -->
                                    <div id="localConfigSection" class="card border p-3 mb-4 rounded-3" style="display: {{ $settings->engine === 'local' ? 'block' : 'none' }};">
                                        <h6 class="fw-bold text-dark mb-2">إعدادات السيرفر الداخلي:</h6>
                                        <div class="mb-2">
                                            <label class="form-label small text-muted">رابط خادم الواتساب الداخلي (Local Server URL):</label>
                                            <input type="text" name="local_server_url" dir="ltr" value="{{ old('local_server_url', $settings->local_server_url ?: 'http://127.0.0.1:3002') }}" class="form-control" placeholder="http://127.0.0.1:3002">
                                            <small class="text-muted d-block mt-1">المنفذ الافتراضي لنجاح ريسنج هو <code>http://127.0.0.1:3002</code> (منفصل عن أي مشروع آخر).</small>
                                        </div>
                                    </div>

                                    <!-- TextMeBot Engine Config -->
                                    <div id="textmebotConfigSection" class="card border p-3 mb-4 rounded-3" style="display: {{ $settings->engine === 'textmebot' ? 'block' : 'none' }};">
                                        <h6 class="fw-bold text-dark mb-2">بيانات حساب TextMeBot:</h6>
                                        <div class="mb-3">
                                            <label class="form-label small text-muted">مفتاح TextMeBot API Key:</label>
                                            <input type="text" name="textmebot_api_key" dir="ltr" value="{{ old('textmebot_api_key', $settings->textmebot_api_key) }}" class="form-control">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small text-muted">رقم الهاتف المسجل في TextMeBot:</label>
                                            <input type="text" name="textmebot_phone" dir="ltr" value="{{ old('textmebot_phone', $settings->textmebot_phone) }}" class="form-control" placeholder="96550000000">
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                                            <i class="bx bx-save me-1"></i> حفظ التغييرات
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

<script>
    function copyText(text) {
        navigator.clipboard.writeText(text).then(function() {
            alert('تم نسخ النص بنجاح!');
        }, function() {
            prompt('انسخ النص التالي:', text);
        });
    }

    $(document).ready(function() {
        $('.btn-quick-template').on('click', function() {
            $('#messageContent').val($(this).data('text'));
        });

        $('.engine-radio').on('change', function() {
            if ($('#engineLocal').is(':checked')) {
                $('#localConfigSection').slideDown();
                $('#textmebotConfigSection').slideUp();
            } else {
                $('#localConfigSection').slideUp();
                $('#textmebotConfigSection').slideDown();
            }
        });

        $('#btnRequestPairingCode').on('click', function() {
            var phone = $('#pairingPhoneInput').val().trim();
            if (!phone) {
                alert('يرجى إدخال رقم الهاتف مع كود الدولة أولاً.');
                return;
            }

            var btn = $(this);
            btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin me-1"></i> جاري الطلب...');

            $.ajax({
                url: "{{ route('whatsapp.gateway.pairing_code') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    phone: phone
                },
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="bx bx-key me-1"></i> طلب كود الاقتران');
                    if (res.success && res.pairing_code) {
                        $('#pairingCodeDisplay').text(res.pairing_code);
                        $('#pairingCodeResult').slideDown();
                    } else {
                        alert(res.message || 'تعذر توليد كود الاقتران');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bx bx-key me-1"></i> طلب كود الاقتران');
                    if (xhr.status === 401) {
                        window.location.reload();
                        return;
                    }
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'خطأ في الاتصال بالسيرفر';
                    alert(msg);
                }
            });
        });

        window.startWhatsAppService = function() {
            var btn = $('#btnStartService');
            btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin me-1"></i> جاري تشغيل الخادم...');

            $.ajax({
                url: "{{ route('whatsapp.gateway.start_service') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="bx bx-play me-1"></i> تشغيل خادم الواتساب الآن');
                    if (res && res.message) {
                        alert(res.message);
                    }
                    setTimeout(checkWhatsAppStatus, 1500);
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bx bx-play me-1"></i> تشغيل خادم الواتساب الآن');
                    if (xhr.status === 401) {
                        window.location.reload();
                        return;
                    }
                    alert('تعذر إرسال أمر التشغيل تلقائياً. يمكنك تشغيله عبر SSH باستخدام: php artisan whatsapp:serve');
                }
            });
        };

        function checkWhatsAppStatus() {
            $.ajax({
                url: "{{ route('whatsapp.gateway.ajax_status') }}",
                type: "GET",
                dataType: "json",
                success: function(data) {
                    if (data.is_connected) {
                        $('#scanQrContainer').hide();
                        $('#connectedContainer').show();
                        $('#connectedPhoneDisplay').text('+' + data.phone);
                        if (data.name) {
                            $('#connectedNameDisplay').text(data.name);
                        }
                        $('#cardStatusText').text('متصل بنجاح').css('color', '#059669');
                        $('#cardConnectedPhone').text('+' + data.phone);
                        $('#topStatusIndicator').html(
                            '<span class="badge bg-success px-3 py-2 fs-6 shadow-sm d-flex align-items-center gap-2">' +
                            '<span class="pulse-dot bg-white"></span>' +
                            '<span>متصل: +' + data.phone + '</span></span>'
                        );
                    } else {
                        $('#connectedContainer').hide();
                        $('#scanQrContainer').show();

                        if (!data.online || data.status === 'service_offline') {
                            $('#qrImageWrapper, #qrLoadingSpinner, #reconnectingContainer').hide();
                            $('#serviceOfflineContainer').show();
                            $('#cardStatusText').text('الخادم متوقف').css('color', '#dc2626');
                            $('#cardConnectedPhone').text(data.phone ? ('+' + data.phone + ' (جلسة سابقة)') : 'الخادم غير نشط');
                            $('#topStatusIndicator').html(
                                '<span class="badge bg-danger px-3 py-2 fs-6 shadow-sm d-flex align-items-center gap-2">' +
                                '<i class="bx bx-power-off fs-5"></i><span>الخادم متوقف</span></span>'
                            );
                            $('#qrRefreshNotice').html('<i class="bx bx-error text-danger me-1"></i> الخادم متوقف');
                        } else if (data.status === 'reconnecting') {
                            $('#qrImageWrapper, #qrLoadingSpinner, #serviceOfflineContainer').hide();
                            $('#reconnectingContainer').show();
                            if (data.phone) {
                                $('#reconnectingPhoneBadge').text('+' + data.phone).show();
                            } else {
                                $('#reconnectingPhoneBadge').hide();
                            }
                            $('#cardStatusText').text('جاري استعادة الاتصال...').css('color', '#2563eb');
                            $('#cardConnectedPhone').text(data.phone ? ('+' + data.phone) : 'جاري التحقق');
                            $('#topStatusIndicator').html(
                                '<span class="badge bg-info text-dark px-3 py-2 fs-6 shadow-sm d-flex align-items-center gap-2">' +
                                '<i class="bx bx-loader-alt bx-spin fs-5"></i><span>جاري استعادة الاتصال...</span></span>'
                            );
                            $('#qrRefreshNotice').html('<i class="bx bx-loader bx-spin text-primary me-1"></i> جاري استعادة الاتصال...');
                        } else if (data.qr) {
                            $('#serviceOfflineContainer, #reconnectingContainer, #qrLoadingSpinner').hide();
                            $('#whatsappQrImage').attr('src', data.qr);
                            $('#qrImageWrapper').show();
                            $('#cardStatusText').text('بانتظار مسح QR').css('color', '#d97706');
                            $('#cardConnectedPhone').text('امسح الرمز من هاتفك');
                            $('#topStatusIndicator').html(
                                '<span class="badge bg-warning text-dark px-3 py-2 fs-6 shadow-sm d-flex align-items-center gap-2">' +
                                '<i class="bx bx-qr-scan fs-5"></i><span>بانتظار مسح رمز QR</span></span>'
                            );
                            $('#qrRefreshNotice').html('<i class="bx bx-refresh bx-spin me-1 text-success"></i> فحص الاتصال التلقائي نشط');
                        } else {
                            $('#serviceOfflineContainer, #reconnectingContainer, #qrImageWrapper').hide();
                            $('#qrLoadingSpinner').show();
                            $('#cardStatusText').text('جاري التهيئة...').css('color', '#6b7280');
                            $('#cardConnectedPhone').text('يرجى الانتظار');
                            $('#topStatusIndicator').html(
                                '<span class="badge bg-secondary px-3 py-2 fs-6 shadow-sm d-flex align-items-center gap-2">' +
                                '<i class="bx bx-loader-alt bx-spin fs-5"></i><span>جاري التهيئة...</span></span>'
                            );
                            $('#qrRefreshNotice').html('<i class="bx bx-refresh bx-spin me-1 text-success"></i> فحص الاتصال التلقائي نشط');
                        }
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 401) {
                        window.location.reload();
                    }
                }
            });
        }

        setInterval(checkWhatsAppStatus, 3000);
    });
</script>
</body>
</html>
