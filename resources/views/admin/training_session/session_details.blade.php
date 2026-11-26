@extends('admin.master_admin')
@section('admin')

<!-- Leaflet CSS (Free, Watermark-Free High-Res Satellite/Hybrid/Street Map) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />

<style>
    #map {
        height: 490px;
        width: 100%;
        border-radius: 12px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.1);
        z-index: 1;
        background: #1e293b;
    }
    #map.map-fullscreen {
        position: fixed !important;
        inset: 0 !important;
        height: 100vh !important;
        width: 100vw !important;
        border-radius: 0 !important;
        z-index: 9999 !important;
    }
    .leaflet-popup-content-wrapper {
        border-radius: 12px !important;
        font-family: "Cairo", sans-serif !important;
    }
    .camel-marker-pin {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        border: 3px solid #971048;
        background: #ffffff;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.35);
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    .camel-marker-pin img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .camel-marker-pin.is-live::after {
        content: "";
        position: absolute;
        inset: -8px;
        border-radius: 50%;
        border: 3px solid rgba(25, 135, 84, 0.75);
        animation: sdPulse 1.6s ease-out infinite;
        pointer-events: none;
    }
    .start-pin-dot {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #198754;
        border: 3px solid #ffffff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.4);
    }
    .peak-star-pin {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #ffc107;
        border: 2px solid #971048;
        color: #971048;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.35);
    }
    .replay-pin-dot {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #0d6efd;
        border: 3px solid #ffffff;
        box-shadow: 0 0 0 5px rgba(13, 110, 253, 0.3);
    }
    .focus-pin-dot {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #971048;
        border: 3px solid #ffffff;
        box-shadow: 0 0 0 6px rgba(151, 16, 72, 0.3);
    }
    .sd-card {
        border: 1px solid rgba(0, 0, 0, 0.06);
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    }
    .metric-card {
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 14px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .metric-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.09);
    }
    .metric-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }
    .live-pulse {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #198754;
        position: relative;
        vertical-align: middle;
        margin-left: 6px;
    }
    .live-pulse::after {
        content: "";
        position: absolute;
        inset: -4px;
        border-radius: 50%;
        border: 2px solid #198754;
        animation: sdPulse 1.6s ease-out infinite;
    }
    @keyframes sdPulse {
        0% { transform: scale(0.7); opacity: 0.9; }
        100% { transform: scale(1.9); opacity: 0; }
    }
    @keyframes fadeInRow {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .chat-container {
        height: 460px;
        overflow-y: auto;
        padding: 14px;
        background: #f8f9fa;
        border-radius: 12px;
        border: 1px solid #e9ecef;
    }
    .chat-bubble {
        max-width: 82%;
        margin-bottom: 12px;
        padding: 10px 14px;
        border-radius: 14px;
        position: relative;
    }
    .chat-owner {
        background-color: #eef5ff;
        margin-left: auto;
        border-bottom-right-radius: 4px;
        color: #0d3b66;
        border: 1px solid #cfe2ff;
    }
    .chat-worker {
        background-color: #edf9f0;
        margin-right: auto;
        border-bottom-left-radius: 4px;
        color: #1b4332;
        border: 1px solid #c7eed8;
    }
    .chat-meta {
        font-size: 11px;
        opacity: 0.75;
        margin-top: 4px;
        display: block;
    }
    .replay-bar {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 10px 14px;
    }
    .speed-legend-dot {
        display: inline-block;
        width: 11px;
        height: 11px;
        border-radius: 50%;
        vertical-align: middle;
        margin-left: 4px;
    }
    .worker-online-dot {
        position: absolute;
        bottom: 2px;
        left: 2px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 2px solid #fff;
    }
    .map-type-btn.active {
        background-color: #971048 !important;
        border-color: #971048 !important;
        color: #fff !important;
    }
</style>

@php
    $isEnded = $session->round_status === 'end';
    $isWorking = $session->round_status === 'working';
    $isWorkerOnline = $session->worker && ($session->worker->is_online || $session->worker->online_status === 'online');
    $perfVal = min(100, max(0, (float)$sessionMetrics['performance']));
    $perfColor = $perfVal >= 75 ? 'bg-success' : ($perfVal >= 45 ? 'bg-warning' : 'bg-danger');
    $ownerFullName = ($session->worker && $session->worker->owner)
        ? trim($session->worker->owner->fname . ' ' . $session->worker->owner->lname)
        : null;
@endphp

<!-- Top Command Header -->
<div class="card sd-card mb-4">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h5 class="mb-0 fw-bold text-dark">
                        جلسة التدريب <span class="text-primary">#{{ $session->id }}</span>
                    </h5>

                    <!-- Dynamic Session Status Badge -->
                    <div id="session-status-badge" class="d-inline-flex align-items-center gap-1">
                        @if($session->round_status === 'working')
                            <span class="badge bg-success px-3 py-2 font-12"><span class="live-pulse bg-white"></span> قيد التدريب (نشطة)</span>
                        @elseif($session->round_status === 'pending')
                            <span class="badge bg-warning text-dark px-3 py-2 font-12"><i class="bx bx-time"></i> قيد الانتظار</span>
                        @elseif($session->round_status === 'stop')
                            <span class="badge bg-danger px-3 py-2 font-12"><i class="bx bx-pause"></i> متوقفة مؤقتاً</span>
                        @elseif($session->round_status === 'end')
                            <span class="badge bg-secondary px-3 py-2 font-12"><i class="bx bx-check-circle"></i> منتهية</span>
                        @endif
                    </div>

                    <!-- Real-time Connection / Signal Status Pill -->
                    <div id="live-signal-badge" class="d-inline-block">
                        @if($isWorking)
                            @if($activityStatus['is_desert_mode'])
                                <span class="badge bg-warning text-dark border px-2 py-1 font-12">
                                    <i class="bx bx-wifi-off"></i> وضع صحراوي (تسجيل محلي)
                                </span>
                            @elseif($activityStatus['is_weak_connection'])
                                <span class="badge bg-warning text-dark border px-2 py-1 font-12">
                                    <i class="bx bx-signal-2"></i> إشارة ضعيفة (منذ {{ $activityStatus['diff_seconds'] }} ث)
                                </span>
                            @else
                                <span class="badge bg-light text-success border border-success px-2 py-1 font-12">
                                    <i class="bx bx-radio-circle-marked"></i> بث مباشر نشط
                                </span>
                            @endif
                        @elseif($isEnded)
                            <span class="badge bg-light text-muted border px-2 py-1 font-12">
                                <i class="bx bx-archive"></i> أرشيف مكتمل
                            </span>
                        @endif
                    </div>
                </div>

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0 font-12">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('all.training.sessions') }}">جلسات التدريب</a></li>
                        <li class="breadcrumb-item active" aria-current="page">متابعة وتحليل الجلسة #{{ $session->id }}</li>
                    </ol>
                </nav>
            </div>

            <!-- Action Controls -->
            <div class="d-flex flex-wrap align-items-center gap-2">
                @if(!$isEnded)
                    <button type="button" id="btn-pause-session"
                            class="btn btn-sm btn-outline-warning px-3 {{ $isWorking ? '' : 'd-none' }}">
                        <i class="bx bx-pause-circle"></i> إيقاف مؤقت
                    </button>
                    <button type="button" id="btn-resume-session"
                            class="btn btn-sm btn-success px-3 {{ !$isWorking ? '' : 'd-none' }}">
                        <i class="bx bx-play-circle"></i> استئناف الجلسة
                    </button>
                    <button type="button" id="btn-end-session" class="btn btn-sm btn-danger px-3">
                        <i class="bx bx-stop-circle"></i> إنهاء الجلسة
                    </button>
                @endif

                <a href="{{ route('details.training.session.exportLogs', $session->id) }}"
                   class="btn btn-sm btn-outline-success px-3" title="تحميل جميع نقاط التتبع والسرعات بصيغة Excel CSV">
                    <i class="bx bx-download"></i> تصدير CSV
                </a>

                <!-- Advanced / Maintenance Tools Dropdown (Safely tucked away from accidental clicks) -->
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle px-3" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bx bx-cog"></i> أدوات متقدمة
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        @if(!$isEnded)
                            <li>
                                <button type="button" id="btn-start-simulation" class="dropdown-item text-success fw-semibold">
                                    <i class="bx bx-play-circle me-1"></i> بدء محاكاة حركة (للاختبار)
                                </button>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                        @endif
                        <li>
                            <button type="button" id="btn-clear-logs" class="dropdown-item text-danger fw-semibold">
                                <i class="bx bx-trash me-1"></i> مسح سجلات السرعة وإعادة التهيئة
                            </button>
                        </li>
                    </ul>
                </div>

                <a href="{{ route('all.training.sessions') }}" class="btn btn-sm btn-secondary px-3">
                    <i class="bx bx-arrow-back"></i> العودة
                </a>
            </div>
        </div>
    </div>
</div>

<!-- General Info + 6 Executive KPIs Row -->
<div class="row g-3 mb-4">
    <!-- Worker & Owner Card -->
    <div class="col-lg-4 col-md-12">
        <div class="card sd-card h-100 mb-0">
            <div class="card-body d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="card-title fw-bold mb-0"><i class="bx bx-id-card text-primary"></i> بيانات الطاقم والموقع</h6>
                        <span class="font-11 text-muted" id="live-poll-clock">
                            @if(!$isEnded)<i class="bx bx-sync text-success"></i> مزامنة تلقائية@endif
                        </span>
                    </div>

                    <div class="d-flex align-items-center p-2 rounded-3 bg-light border mb-3">
                        <div class="position-relative me-3 flex-shrink-0">
                            <img src="{{ $workerAvatarUrl }}"
                                 onerror="this.src='{{ asset('upload/no_image.jpg') }}'"
                                 style="width: 58px; height: 58px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.12);">
                            <span id="worker-online-dot"
                                  class="worker-online-dot {{ $isWorkerOnline ? 'bg-success' : 'bg-secondary' }}"
                                  title="{{ $isWorkerOnline ? 'متصل الآن' : 'غير متصل' }}"></span>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="mb-0 fw-bold text-truncate">{{ $session->worker ? $session->worker->full_name : 'عامل غير معروف' }}</h6>
                            <span class="text-muted font-12 d-block">المدرب / العامل المسؤول</span>
                            <span id="worker-online-text" class="font-11 fw-semibold {{ $isWorkerOnline ? 'text-success' : 'text-muted' }}">
                                {{ $isWorkerOnline ? 'متصل بالتطبيق حالياً' : 'غير متصل حالياً' }}
                                @if($session->worker && $session->worker->phone)
                                    • <span dir="ltr">{{ $session->worker->phone }}</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="mb-3 pb-2 border-bottom">
                        <span class="text-muted d-block font-11">المالك المشرف</span>
                        @if($ownerFullName)
                            <div class="fw-bold font-14 text-dark">
                                <i class="bx bx-user text-primary"></i> {{ $ownerFullName }}
                            </div>
                            @if($session->worker->owner->phone)
                                <div class="text-muted font-12" dir="ltr" style="text-align: right;">
                                    <i class="bx bx-phone"></i> {{ $session->worker->owner->phone }}
                                </div>
                            @endif
                        @else
                            <span class="text-muted fw-semibold">غير محدد</span>
                        @endif
                    </div>

                    <div class="mb-3 pb-2 border-bottom">
                        <span class="text-muted d-block font-11">موقع الجلسة</span>
                        <div class="fw-bold font-13 text-dark">
                            <i class="bx bx-map-pin text-danger"></i> {{ $session->location_name ?? 'غير محدد' }}
                        </div>
                        @if($sessionMetrics['has_coordinates'])
                            <a href="https://www.google.com/maps?q={{ $sessionMetrics['current_lat'] }},{{ $sessionMetrics['current_lng'] }}"
                               target="_blank" rel="noopener" class="font-11 text-primary text-decoration-none">
                                <i class="bx bx-link-external"></i> فتح آخر موقع في خرائط Google
                            </a>
                        @endif
                    </div>
                </div>

                <div class="row g-2 font-12">
                    <div class="col-6">
                        <span class="text-muted d-block font-11">وقت البدء</span>
                        <strong class="text-dark"><i class="bx bx-calendar text-success"></i> {{ $session->created_at ? $session->created_at->format('Y-m-d H:i') : '-' }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block font-11">وقت الانتهاء</span>
                        <strong class="text-dark" id="session-ended-at-text">
                            @if($session->session_ended_at)
                                <i class="bx bx-calendar-check text-secondary"></i> {{ $session->session_ended_at->format('Y-m-d H:i') }}
                            @else
                                <span class="text-muted">{{ $isEnded ? 'منتهية' : 'مستمرة...' }}</span>
                            @endif
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 6 KPI Cards + Performance & Speed Zones -->
    <div class="col-lg-8 col-md-12">
        <div class="row g-3">
            <!-- Current Speed -->
            <div class="col-md-4 col-sm-6">
                <div class="card metric-card border-start border-0 border-4 border-info mb-0 h-100">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-secondary font-12 fw-semibold">السرعة الحالية / الأخيرة</p>
                                <h4 id="card-current-speed" class="mb-0 text-info fw-bold">
                                    {{ number_format($sessionMetrics['current_speed'], 2) }} <span class="font-13">كم/س</span>
                                </h4>
                            </div>
                            <div class="metric-icon bg-light-info text-info"><i class="bx bx-tachometer"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Max Speed -->
            <div class="col-md-4 col-sm-6">
                <div class="card metric-card border-start border-0 border-4 border-danger mb-0 h-100"
                     style="{{ $sessionMetrics['max_speed_lat'] ? 'cursor:pointer;' : '' }}"
                     id="card-max-speed-box"
                     title="{{ $sessionMetrics['max_speed_lat'] ? 'اضغط لعرض نقطة أعلى سرعة على الخريطة' : '' }}">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-secondary font-12 fw-semibold">السرعة القصوى (ذروة)</p>
                                <h4 id="card-max-speed" class="mb-0 text-danger fw-bold">
                                    {{ number_format($sessionMetrics['max_speed'], 2) }} <span class="font-13">كم/س</span>
                                </h4>
                                @if($sessionMetrics['max_speed_time'])
                                    <small class="text-muted font-11"><i class="bx bx-time"></i> {{ $sessionMetrics['max_speed_time'] }}</small>
                                @endif
                            </div>
                            <div class="metric-icon bg-light-danger text-danger"><i class="bx bx-trending-up"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Average Speed -->
            <div class="col-md-4 col-sm-6">
                <div class="card metric-card border-start border-0 border-4 border-primary mb-0 h-100">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-secondary font-12 fw-semibold">متوسط السرعة</p>
                                <h4 id="card-average-speed" class="mb-0 text-primary fw-bold">
                                    {{ number_format($sessionMetrics['avg_speed'], 2) }} <span class="font-13">كم/س</span>
                                </h4>
                            </div>
                            <div class="metric-icon" style="background: rgba(151,16,72,0.1); color: #971048;"><i class="bx bx-pulse"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Distance -->
            <div class="col-md-4 col-sm-6">
                <div class="card metric-card border-start border-0 border-4 border-success mb-0 h-100">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-secondary font-12 fw-semibold">المسافة المقطوعة</p>
                                <h4 id="card-distance" class="mb-0 text-success fw-bold">
                                    {{ number_format($sessionMetrics['distance_km'], 2) }} <span class="font-13">كم</span>
                                </h4>
                            </div>
                            <div class="metric-icon bg-light-success text-success"><i class="bx bx-map-alt"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Duration -->
            <div class="col-md-4 col-sm-6">
                <div class="card metric-card border-start border-0 border-4 border-warning mb-0 h-100">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-secondary font-12 fw-semibold">مدة التدريب</p>
                                <h4 id="card-session-duration" class="mb-0 text-warning fw-bold" dir="ltr" style="text-align: right;">
                                    {{ $sessionMetrics['round_time'] }}
                                </h4>
                            </div>
                            <div class="metric-icon bg-light-warning text-warning"><i class="bx bx-time-five"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GPS Logs Count -->
            <div class="col-md-4 col-sm-6">
                <div class="card metric-card border-start border-0 border-4 border-secondary mb-0 h-100">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-secondary font-12 fw-semibold">نقاط التتبع (GPS)</p>
                                <h4 id="card-logs-count" class="mb-0 text-dark fw-bold">
                                    {{ number_format($sessionMetrics['total_logs_count']) }} <span class="font-13 fw-normal text-muted">نقطة</span>
                                </h4>
                            </div>
                            <div class="metric-icon bg-light text-secondary"><i class="bx bx-radar"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance & Speed Zones Card -->
            <div class="col-12">
                <div class="card sd-card mb-0">
                    <div class="card-body py-3">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="text-secondary font-12 fw-bold">تقييم أداء الهجن</span>
                                    <span id="card-performance-badge" class="badge {{ $perfColor }} font-13">{{ number_format($perfVal, 1) }}%</span>
                                </div>
                                <div class="progress" style="height: 10px; border-radius: 999px;">
                                    <div id="card-performance-bar" class="progress-bar {{ $perfColor }} progress-bar-striped {{ $isWorking ? 'progress-bar-animated' : '' }}"
                                         role="progressbar" style="width: {{ $perfVal }}%"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center justify-content-between mb-1 font-12">
                                    <span class="text-secondary fw-bold">توزيع مناطق السرعة</span>
                                    <span class="text-muted font-11">
                                        بطيء {{ $sessionMetrics['speed_zones']['low_pct'] }}% •
                                        متوسط {{ $sessionMetrics['speed_zones']['medium_pct'] }}% •
                                        سريع {{ $sessionMetrics['speed_zones']['high_pct'] }}%
                                    </span>
                                </div>
                                <div class="progress" style="height: 10px; border-radius: 999px;">
                                    <div class="progress-bar bg-success" style="width: {{ $sessionMetrics['speed_zones']['low_pct'] }}%"
                                         title="أقل من 15 كم/س ({{ $sessionMetrics['speed_zones']['low'] }} نقطة)"></div>
                                    <div class="progress-bar bg-warning" style="width: {{ $sessionMetrics['speed_zones']['medium_pct'] }}%"
                                         title="15 - 30 كم/س ({{ $sessionMetrics['speed_zones']['medium'] }} نقطة)"></div>
                                    <div class="progress-bar bg-danger" style="width: {{ $sessionMetrics['speed_zones']['high_pct'] }}%"
                                         title="أعلى من 30 كم/س ({{ $sessionMetrics['speed_zones']['high'] }} نقطة)"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Worker End-of-Session Summary (shown whenever summary exists, or dynamically populated when live session finishes) -->
<div id="session-summary-card" class="card sd-card mb-4 {{ ($session->summary_text || $summaryAudioUrl || $summaryImageUrl) ? '' : 'd-none' }}"
     style="border-right: 4px solid #198754 !important;">
    <div class="card-body">
        <h6 class="card-title fw-bold mb-3 text-success">
            <i class="bx bx-notepad"></i> ملخص وملاحظات العامل بعد الجلسة
        </h6>
        <div class="row align-items-center g-3">
            <div class="col-md-8">
                <p id="summary-text-content" class="font-15 text-dark fw-semibold mb-3 {{ $session->summary_text ? '' : 'd-none' }}" style="line-height: 1.8;">
                    {{ $session->summary_text }}
                </p>
                <div id="summary-audio-wrap" class="{{ $summaryAudioUrl ? '' : 'd-none' }}">
                    <span class="text-muted d-block font-12 mb-1"><i class="bx bx-volume-full text-success"></i> الملاحظة الصوتية:</span>
                    <audio id="summary-audio-player" src="{{ $summaryAudioUrl ?? '' }}" controls class="w-100" style="max-width: 500px; height: 40px;"></audio>
                </div>
            </div>
            <div id="summary-image-wrap" class="col-md-4 text-center {{ $summaryImageUrl ? '' : 'd-none' }}">
                <span class="text-muted d-block font-12 mb-2"><i class="bx bx-image"></i> الصورة المرفقة:</span>
                <img id="summary-image-el" src="{{ $summaryImageUrl ?? '' }}"
                     class="img-fluid rounded shadow-sm border"
                     style="max-height: 200px; cursor: pointer; object-fit: cover;"
                     onclick="window.open(this.src)" alt="ملخص الجلسة">
            </div>
        </div>
    </div>
</div>

<!-- Interactive Tracking Map & Communications Row -->
<div class="row g-3 mb-4">
    <!-- Map & Replay Column -->
    <div class="col-lg-8 col-md-12">
        <div class="card sd-card h-100 mb-0">
            <div class="card-body">
                <!-- Map Header Controls -->
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h6 class="card-title fw-bold mb-0">
                        <i class="bx bx-map text-danger"></i> خريطة مسار التدريب والتتبع
                    </h6>
                    <div class="d-flex flex-wrap align-items-center gap-1">
                        <div class="btn-group btn-group-sm" role="group" aria-label="نوع الخريطة">
                            <button type="button" class="btn btn-outline-secondary map-type-btn active" data-map-type="hybrid">هجين</button>
                            <button type="button" class="btn btn-outline-secondary map-type-btn" data-map-type="satellite">قمر صناعي</button>
                            <button type="button" class="btn btn-outline-secondary map-type-btn" data-map-type="roadmap">خريطة</button>
                        </div>
                        <button type="button" id="btn-toggle-speed-colors" class="btn btn-sm btn-primary" title="تلوين المسار حسب سرعة المطية">
                            <i class="bx bx-palette"></i> ألوان السرعة
                        </button>
                        <button type="button" id="btn-fit-route" class="btn btn-sm btn-outline-secondary" title="عرض كامل المسار">
                            <i class="bx bx-target-lock"></i> كامل المسار
                        </button>
                        <button type="button" id="btn-track-mode" class="btn btn-sm btn-outline-primary" title="تتبع حركة المطية مع البوصلة">
                            <i class="bx bx-navigation"></i> وضع التتبع
                        </button>
                        <button type="button" id="btn-map-fullscreen" class="btn btn-sm btn-outline-dark" title="ملء الشاشة">
                            <i class="bx bx-fullscreen"></i>
                        </button>
                    </div>
                </div>

                <!-- Map Canvas + Compass -->
                <div class="position-relative">
                    <div id="map"></div>
                    <!-- Compass Overlay -->
                    <div id="compass-container" style="display: none; position: absolute; top: 15px; right: 15px; width: 64px; height: 64px; background: rgba(255,255,255,0.92); backdrop-filter: blur(5px); border-radius: 50%; box-shadow: 0 4px 15px rgba(0,0,0,0.22); z-index: 99; align-items: center; justify-content: center; border: 2px solid #971048;">
                        <div style="position: absolute; width: 100%; height: 100%; border-radius: 50%; border: 1px dashed rgba(151,16,72,0.3); pointer-events: none;"></div>
                        <span style="position: absolute; top: 3px; font-size: 9px; font-weight: bold; color: #971048; font-family: Arial, sans-serif;">N</span>
                        <span style="position: absolute; bottom: 3px; font-size: 9px; font-weight: bold; color: #777; font-family: Arial, sans-serif;">S</span>
                        <span style="position: absolute; left: 5px; font-size: 9px; font-weight: bold; color: #777; font-family: Arial, sans-serif;">W</span>
                        <span style="position: absolute; right: 5px; font-size: 9px; font-weight: bold; color: #777; font-family: Arial, sans-serif;">E</span>
                        <div id="compass-needle" style="width: 8px; height: 42px; position: relative; transition: transform 0.5s ease-out; transform: rotate(0deg);">
                            <div style="width: 0; height: 0; border-left: 4px solid transparent; border-right: 4px solid transparent; border-bottom: 21px solid #971048; position: absolute; top: 0; left: 0;"></div>
                            <div style="width: 0; height: 0; border-left: 4px solid transparent; border-right: 4px solid transparent; border-top: 21px solid #c0c0c0; position: absolute; bottom: 0; left: 0;"></div>
                        </div>
                    </div>
                </div>

                <!-- Interactive Route Replay Player -->
                <div id="replay-controls-bar" class="replay-bar mt-3 {{ count($routePoints) < 2 ? 'd-none' : '' }}">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <button type="button" id="btn-replay-toggle" class="btn btn-sm btn-primary rounded-pill px-3">
                            <i class="bx bx-play"></i> إعادة تشغيل المسار
                        </button>
                        <button type="button" id="btn-replay-reset" class="btn btn-sm btn-outline-secondary rounded-circle" title="العودة لبداية المسار">
                            <i class="bx bx-skip-previous"></i>
                        </button>
                        <select id="replay-speed-select" class="form-select form-select-sm" style="width: auto;">
                            <option value="1">1x</option>
                            <option value="2" selected>2x</option>
                            <option value="5">5x</option>
                            <option value="10">10x</option>
                        </select>
                        <div class="flex-grow-1 px-2" style="min-width: 140px;">
                            <input type="range" class="form-range mb-0" id="replay-slider"
                                   min="0" max="{{ max(0, count($routePoints) - 1) }}" value="{{ max(0, count($routePoints) - 1) }}">
                        </div>
                        <div class="font-12 text-nowrap bg-white border rounded-pill px-3 py-1">
                            <span class="fw-bold text-primary" id="replay-speed-readout">-- كم/س</span>
                            <span class="text-muted mx-1">|</span>
                            <span class="text-dark" id="replay-time-readout">--:--:--</span>
                            <span class="text-muted mx-1">|</span>
                            <span class="text-muted" id="replay-index-readout">{{ count($routePoints) }}/{{ count($routePoints) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Map Speed Legend -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2 pt-2 border-top font-11 text-muted">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <span><span class="speed-legend-dot" style="background:#198754;"></span> بطيء (&lt;15 كم/س)</span>
                        <span><span class="speed-legend-dot" style="background:#f59e0b;"></span> متوسط (15–30 كم/س)</span>
                        <span><span class="speed-legend-dot" style="background:#c1121f;"></span> سريع (≥30 كم/س)</span>
                    </div>
                    <div>
                        <i class="bx bx-info-circle"></i> تم تنظيف المسار تلقائياً لإزالة تشويش GPS
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Communications (Chats + Instructions) Column -->
    <div class="col-lg-4 col-md-12">
        <div class="card sd-card h-100 mb-0">
            <div class="card-body d-flex flex-column">
                <ul class="nav nav-pills nav-fill mb-3 bg-light p-1 rounded-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active py-2 font-13 fw-bold" data-bs-toggle="pill" data-bs-target="#tab-chats" type="button" role="tab">
                            <i class="bx bx-message-rounded-dots"></i> المحادثة
                            <span class="badge bg-white text-primary ms-1" id="chats-count-badge">{{ $session->chats->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-2 font-13 fw-bold" data-bs-toggle="pill" data-bs-target="#tab-instructions" type="button" role="tab">
                            <i class="bx bx-broadcast"></i> التوجيهات
                            <span class="badge bg-secondary ms-1">{{ $session->instructions->count() }}</span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content flex-grow-1">
                    <!-- Chats Tab -->
                    <div class="tab-pane fade show active" id="tab-chats" role="tabpanel">
                        <div class="chat-container" id="session-chat-container">
                            @forelse($session->chats as $chat)
                                @php
                                    $isOwnerMsg = !empty($chat->owner_id);
                                    $chatMediaUrl = $chat->content_type !== 'text'
                                        ? (str_starts_with((string)$chat->content, 'http') ? $chat->content : asset(ltrim((string)$chat->content, '/')))
                                        : null;
                                @endphp
                                <div class="chat-bubble {{ $isOwnerMsg ? 'chat-owner' : 'chat-worker' }}">
                                    <div class="fw-bold font-12 mb-1">
                                        @if($isOwnerMsg)
                                            <i class="bx bx-user"></i> المالك ({{ $chat->owner ? ($chat->owner->fname . ' ' . $chat->owner->lname) : ($ownerFullName ?? 'المالك') }})
                                        @else
                                            <i class="bx bx-run"></i> العامل ({{ $chat->worker ? $chat->worker->full_name : ($session->worker ? $session->worker->full_name : 'العامل') }})
                                        @endif
                                    </div>
                                    @if($chat->content_type === 'text')
                                        <p class="mb-0 font-13">{{ $chat->content }}</p>
                                    @elseif($chat->content_type === 'image')
                                        <img src="{{ $chatMediaUrl }}" class="img-fluid rounded border my-1" style="max-height: 150px; cursor: pointer;" onclick="window.open(this.src)">
                                    @elseif($chat->content_type === 'sound' || $chat->content_type === 'audio')
                                        <audio src="{{ $chatMediaUrl }}" controls class="w-100 my-1" style="max-height: 38px;"></audio>
                                    @endif
                                    <span class="chat-meta">{{ $chat->created_at ? $chat->created_at->format('H:i:s | Y-m-d') : '' }}</span>
                                </div>
                            @empty
                                <div id="chat-empty-state" class="text-center text-muted py-5">
                                    <i class="bx bx-message-alt-x font-24 d-block mb-1 opacity-50"></i>
                                    لم يتم تسجيل رسائل محادثة في هذه الجلسة.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Instructions Tab -->
                    <div class="tab-pane fade" id="tab-instructions" role="tabpanel">
                        <div class="chat-container">
                            @forelse($session->instructions as $inst)
                                @php
                                    $instMedia = ($inst->instruction_type !== 'text' && $inst->instruction)
                                        ? (str_starts_with((string)$inst->instruction, 'http') ? $inst->instruction : asset(ltrim((string)$inst->instruction, '/')))
                                        : null;
                                    $replyMedia = ($inst->reply_type !== 'text' && $inst->reply)
                                        ? (str_starts_with((string)$inst->reply, 'http') ? $inst->reply : asset(ltrim((string)$inst->reply, '/')))
                                        : null;
                                @endphp
                                <div class="chat-bubble {{ $inst->is_from_owner ? 'chat-owner' : 'chat-worker' }}">
                                    <div class="fw-bold font-12 mb-1">
                                        <i class="bx {{ $inst->is_from_owner ? 'bx-user' : 'bx-run' }}"></i>
                                        {{ $inst->is_from_owner ? 'توجيه المالك' : 'توجيه العامل' }}
                                    </div>
                                    @if($inst->instruction_type === 'text' || empty($inst->instruction_type))
                                        <p class="mb-0 font-13">{{ $inst->instruction }}</p>
                                    @elseif($inst->instruction_type === 'image')
                                        <img src="{{ $instMedia }}" class="img-fluid rounded border my-1" style="max-height: 150px; cursor: pointer;" onclick="window.open(this.src)">
                                    @elseif(in_array($inst->instruction_type, ['sound', 'audio']))
                                        <audio src="{{ $instMedia }}" controls class="w-100 my-1" style="max-height: 38px;"></audio>
                                    @endif

                                    @if($inst->reply)
                                        <div class="mt-2 pt-2 border-top">
                                            <div class="font-11 fw-bold text-success mb-1"><i class="bx bx-reply"></i> الرد:</div>
                                            @if($inst->reply_type === 'text' || empty($inst->reply_type))
                                                <p class="mb-0 font-12">{{ $inst->reply }}</p>
                                            @elseif($inst->reply_type === 'image')
                                                <img src="{{ $replyMedia }}" class="img-fluid rounded border my-1" style="max-height: 120px; cursor: pointer;" onclick="window.open(this.src)">
                                            @elseif(in_array($inst->reply_type, ['sound', 'audio']))
                                                <audio src="{{ $replyMedia }}" controls class="w-100 my-1" style="max-height: 36px;"></audio>
                                            @endif
                                        </div>
                                    @endif
                                    <span class="chat-meta">{{ $inst->created_at ? $inst->created_at->format('H:i:s | Y-m-d') : '' }}</span>
                                </div>
                            @empty
                                <div class="text-center text-muted py-5">
                                    <i class="bx bx-broadcast font-24 d-block mb-1 opacity-50"></i>
                                    لا توجد توجيهات مسجلة لهذه الجلسة.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interactive Speed Profile Chart Card -->
<div class="card sd-card mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h6 class="card-title fw-bold mb-0">
                    <i class="bx bx-line-chart text-primary"></i> منحنى تطور السرعة خلال الجلسة (كم/س)
                </h6>
                <small class="text-muted font-12">اضغط على أي نقطة في الرسم البياني لتحديد موقعها الفعلي على الخريطة أعلاه</small>
            </div>
            <div class="d-flex gap-2 font-12">
                <span class="badge bg-light text-danger border">الذروة: <strong id="chart-peak-badge">{{ number_format($sessionMetrics['max_speed'], 1) }}</strong> كم/س</span>
                <span class="badge bg-light text-primary border">المتوسط: <strong id="chart-avg-badge">{{ number_format($sessionMetrics['avg_speed'], 1) }}</strong> كم/س</span>
            </div>
        </div>
        <div style="position: relative; height: 260px; width: 100%;">
            <canvas id="speedProfileChart"></canvas>
            <div id="speed-chart-empty" class="{{ count($chartPoints) > 0 ? 'd-none' : '' }} position-absolute top-50 start-50 translate-middle text-muted text-center">
                <i class="bx bx-bar-chart-alt-2 font-30 d-block mb-1 opacity-50"></i>
                لا توجد بيانات سرعة كافية لعرض المنحنى حالياً
            </div>
        </div>
    </div>
</div>

<!-- Detailed Paginated Speed Logs Table Card -->
<div class="card sd-card">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h6 class="card-title fw-bold mb-0">
                    <i class="bx bx-list-ol text-info"></i> سجل السرعات والمواقع التفصيلي
                    <span class="badge bg-light text-dark border ms-1" id="table-logs-total-badge">{{ number_format($sessionMetrics['total_logs_count']) }} نقطة</span>
                </h6>
                <small class="text-muted font-11">يُعرض بترتيب الأحدث أولاً (50 نقطة في كل صفحة حفاظاً على سرعة التصفح)</small>
            </div>
            <a href="{{ route('details.training.session.exportLogs', $session->id) }}" class="btn btn-sm btn-outline-success">
                <i class="bx bx-spreadsheet"></i> تحميل كامل السجل (CSV)
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle mb-0" id="speedLogsTable" style="width: 100%">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">#</th>
                        <th>السرعة المسجلة</th>
                        <th>خط العرض (Lat)</th>
                        <th>خط الطول (Lng)</th>
                        <th>وصف الموقع</th>
                        <th>وقت التسجيل</th>
                        <th class="text-center">الموقع</th>
                    </tr>
                </thead>
                <tbody id="speed-logs-tbody">
                    @forelse($speedLogs as $index => $log)
                        @php
                            $spd = (float)$log->speed;
                            $spdBadge = $spd >= 30 ? 'bg-danger text-white' : ($spd >= 15 ? 'bg-warning text-dark' : 'bg-success text-white');
                        @endphp
                        <tr>
                            <td class="text-center text-muted font-12">{{ $speedLogs->total() - (($speedLogs->currentPage() - 1) * $speedLogs->perPage()) - $index }}</td>
                            <td>
                                <span class="badge {{ $spdBadge }} font-12 fw-bold px-2 py-1">{{ number_format($spd, 2) }} كم/س</span>
                            </td>
                            <td dir="ltr" class="font-12">{{ $log->latitude }}</td>
                            <td dir="ltr" class="font-12">{{ $log->longitude }}</td>
                            <td class="font-12">
                                @if($log->location_name)
                                    <i class="bx bx-map text-danger"></i> {{ $log->location_name }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="font-12" dir="ltr" style="text-align: right;">{{ $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-' }}</td>
                            <td class="text-center">
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary py-0 px-2 btn-focus-point"
                                        data-lat="{{ (float)$log->latitude }}"
                                        data-lng="{{ (float)$log->longitude }}"
                                        data-speed="{{ number_format($spd, 2) }}"
                                        data-time="{{ $log->created_at ? $log->created_at->format('H:i:s') : '' }}"
                                        title="عرض هذه النقطة على الخريطة">
                                    <i class="bx bx-target-lock"></i>
                                </button>
                                <a href="https://www.google.com/maps?q={{ $log->latitude }},{{ $log->longitude }}"
                                   target="_blank" rel="noopener"
                                   class="btn btn-sm btn-outline-secondary py-0 px-2" title="فتح في خرائط Google">
                                    <i class="bx bx-link-external"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr id="speed-logs-empty-row">
                            <td colspan="7" class="text-center text-muted py-4">لا توجد سجلات سرعة متوفرة لهذه الجلسة التدريبية.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($speedLogs->hasPages())
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 pt-3 border-top">
                <div class="text-muted font-12">
                    عرض {{ $speedLogs->firstItem() }} – {{ $speedLogs->lastItem() }} من {{ number_format($speedLogs->total()) }} نقطة
                </div>
                <div>
                    {{ $speedLogs->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Pusher & Laravel Echo (for real-time Reverb support when active) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.3.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.0/dist/echo.iife.js"></script>
<!-- Leaflet JS (Watermark-free Satellite, Hybrid & Street Map) -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

<script>
(function () {
    // =========================================================================
    // 1. INITIAL SERVER PAYLOAD (Clean, Compact & Memory-Safe)
    // =========================================================================
    const SESSION_ID = {{ (int)$session->id }};
    const CSRF_TOKEN = @json(csrf_token());
    const URL_UPDATE_STATUS = @json(route('details.training.session.updateStatus', $session->id));
    const URL_CLEAR_LOGS = @json(route('details.training.session.clearLogs', $session->id));
    const URL_LIVE_DATA = @json(route('details.training.session.liveData', $session->id));
    const WORKER_NAME = @json($session->worker ? $session->worker->full_name : 'غير محدد');
    const LOCATION_NAME = @json($session->location_name ?? '');
    const HAS_WORKER_PHOTO = {{ ($session->worker && !empty($session->worker->photo_path)) ? 'true' : 'false' }};
    const FALLBACK_LOGO_URL = @json(asset('backend/assets/images/logo-icon.png'));
    const MARKER_AVATAR_URL = HAS_WORKER_PHOTO ? @json($workerAvatarUrl) : FALLBACK_LOGO_URL;
    const REVERB_CFG = @json($reverbConfig);

    let sessionStatus = @json($session->round_status);
    let routePoints = @json($routePoints); // [{id, speed, latitude, longitude, created_at}, ...]
    let chartPoints = @json($chartPoints);
    let metrics = @json($sessionMetrics);

    let lastLogId = metrics.last_log_id || 0;
    let lastChatId = metrics.last_chat_id || 0;
    let totalLogsCount = metrics.total_logs_count || 0;
    let maxSpeedVal = parseFloat(metrics.max_speed || 0);
    let avgSpeedVal = parseFloat(metrics.avg_speed || 0);
    let seenLogIds = new Set(routePoints.map(p => Number(p.id)));
    let seenChatIds = new Set(@json($session->chats->pluck('id')->map(fn($i) => (int)$i)->values()));

    // Leaflet Map & Layer State
    let map = null;
    let tileLayers = {};
    let currentBaseLayer = null;
    let routeLayerGroup = null;
    let useSpeedColors = true;
    let startMarker = null;
    let peakMarker = null;
    let currentMarker = null;
    let focusMarker = null;

    let isTrackingMode = false;
    let currentBearing = 0;
    let isSimulating = false;
    let simulationTimer = null;
    let livePollTimer = null;
    let durationTickTimer = null;
    let simulatedLogsCount = routePoints.length;

    // Replay State
    let isReplaying = false;
    let replayTimer = null;
    let replayIndex = Math.max(0, routePoints.length - 1);
    let replayMarker = null;

    // Chart.js State
    let speedChart = null;

    // =========================================================================
    // 2. HELPERS
    // =========================================================================
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function getSpeedColor(speed) {
        const s = parseFloat(speed) || 0;
        if (s >= 30) return '#c1121f'; // High speed (crimson)
        if (s >= 15) return '#f59e0b'; // Medium speed (amber)
        return '#198754';              // Low speed (green)
    }

    function getSpeedBadgeClass(speed) {
        const s = parseFloat(speed) || 0;
        if (s >= 30) return 'bg-danger text-white';
        if (s >= 15) return 'bg-warning text-dark';
        return 'bg-success text-white';
    }

    function formatTimeOnly(ts) {
        if (!ts) return '--:--:--';
        const str = String(ts);
        const match = str.match(/(\d{2}:\d{2}:\d{2})/);
        if (match) return match[1];
        const d = new Date(str);
        if (!isNaN(d.getTime())) {
            return String(d.getHours()).padStart(2, '0') + ':' +
                   String(d.getMinutes()).padStart(2, '0') + ':' +
                   String(d.getSeconds()).padStart(2, '0');
        }
        return str;
    }

    function formatDateTime(ts) {
        if (!ts) return '-';
        const d = new Date(ts);
        if (!isNaN(d.getTime())) {
            return d.getFullYear() + '-' +
                String(d.getMonth() + 1).padStart(2, '0') + '-' +
                String(d.getDate()).padStart(2, '0') + ' ' +
                String(d.getHours()).padStart(2, '0') + ':' +
                String(d.getMinutes()).padStart(2, '0') + ':' +
                String(d.getSeconds()).padStart(2, '0');
        }
        return String(ts);
    }

    function timeStringToSeconds(timeStr) {
        if (!timeStr) return 0;
        const parts = String(timeStr).trim().split(':');
        if (parts.length === 3) {
            return (parseInt(parts[0], 10) || 0) * 3600 +
                   (parseInt(parts[1], 10) || 0) * 60 +
                   (parseInt(parts[2], 10) || 0);
        }
        return 0;
    }

    function secondsToTimeString(totalSecs) {
        const secs = Math.max(0, parseInt(totalSecs, 10) || 0);
        const hrs = Math.floor(secs / 3600);
        const mins = Math.floor((secs % 3600) / 60);
        const rem = secs % 60;
        const pad = n => String(n).padStart(2, '0');
        return `${pad(hrs)}:${pad(mins)}:${pad(rem)}`;
    }

    function calculateBearing(lat1, lng1, lat2, lng2) {
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const r1 = lat1 * Math.PI / 180;
        const r2 = lat2 * Math.PI / 180;
        const y = Math.sin(dLng) * Math.cos(r2);
        const x = Math.cos(r1) * Math.sin(r2) - Math.sin(r1) * Math.cos(r2) * Math.cos(dLng);
        return ((Math.atan2(y, x) * 180 / Math.PI) + 360) % 360;
    }

    function confirmDialog(title, text, confirmText, onConfirm) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#971048',
                cancelButtonColor: '#6c757d',
                confirmButtonText: confirmText,
                cancelButtonText: 'إلغاء'
            }).then(function (result) {
                if (result.isConfirmed) onConfirm();
            });
        } else if (confirm(title + '\n' + text)) {
            onConfirm();
        }
    }

    // =========================================================================
    // 3. LEAFLET MAP ENGINE (Watermark-Free Satellite / Hybrid / Street)
    // =========================================================================
    function buildCurrentMarkerHtml(speedVal, distVal) {
        const s = parseFloat(speedVal || 0).toFixed(2);
        const d = parseFloat(distVal || 0).toFixed(2);
        return `<div style="direction:rtl;text-align:right;font-family:Cairo,sans-serif;font-size:13px;line-height:1.7;min-width:170px;">
            <strong style="color:#971048;font-size:14px;display:block;margin-bottom:4px;">${sessionStatus === 'end' ? 'نقطة نهاية الجلسة' : 'الموقع الحالي للهجن'}</strong>
            <b>العامل:</b> ${escapeHtml(WORKER_NAME)}<br>
            <b>السرعة:</b> <span id="info-current-speed">${s} كم/س</span><br>
            <b>المسافة:</b> <span id="info-current-distance">${d} كم</span>
        </div>`;
    }

    function createCamelDivIcon() {
        const liveCls = sessionStatus === 'working' ? 'is-live' : '';
        return L.divIcon({
            className: '',
            iconSize: [46, 46],
            iconAnchor: [23, 23],
            popupAnchor: [0, -24],
            html: `<div class="camel-marker-pin ${liveCls}">
                <img src="${escapeHtml(MARKER_AVATAR_URL)}" onerror="this.onerror=null;this.src='${escapeHtml(FALLBACK_LOGO_URL)}';" alt="camel">
            </div>`
        });
    }

    function drawRouteOnMap() {
        if (!map || !routeLayerGroup) return;
        routeLayerGroup.clearLayers();

        if (routePoints.length < 2) return;

        const latLngs = routePoints.map(p => [parseFloat(p.latitude), parseFloat(p.longitude)]);

        // Subtle dark halo underneath so track stands out sharply on satellite imagery
        L.polyline(latLngs, {
            color: '#000000',
            weight: 8,
            opacity: 0.35,
            lineCap: 'round',
            lineJoin: 'round'
        }).addTo(routeLayerGroup);

        if (useSpeedColors) {
            // Group contiguous points of the same speed band into minimal Polylines
            let segColor = getSpeedColor(routePoints[1].speed);
            let segCoords = [latLngs[0], latLngs[1]];

            for (let i = 2; i < routePoints.length; i++) {
                const c = getSpeedColor(routePoints[i].speed);
                if (c === segColor) {
                    segCoords.push(latLngs[i]);
                } else {
                    L.polyline(segCoords, {
                        color: segColor,
                        weight: 4.5,
                        opacity: 0.96,
                        lineCap: 'round',
                        lineJoin: 'round'
                    }).addTo(routeLayerGroup);
                    segColor = c;
                    segCoords = [segCoords[segCoords.length - 1], latLngs[i]];
                }
            }
            if (segCoords.length >= 2) {
                L.polyline(segCoords, {
                    color: segColor,
                    weight: 4.5,
                    opacity: 0.96,
                    lineCap: 'round',
                    lineJoin: 'round'
                }).addTo(routeLayerGroup);
            }
        } else {
            L.polyline(latLngs, {
                color: '#971048',
                weight: 4.5,
                opacity: 0.96,
                lineCap: 'round',
                lineJoin: 'round'
            }).addTo(routeLayerGroup);
        }
    }

    function fitMapToRoute() {
        if (!map) return;
        if (routePoints.length >= 2) {
            const bounds = L.latLngBounds(routePoints.map(p => [parseFloat(p.latitude), parseFloat(p.longitude)]));
            map.fitBounds(bounds, { padding: [45, 45], maxZoom: 18 });
        } else if (routePoints.length === 1) {
            map.setView([parseFloat(routePoints[0].latitude), parseFloat(routePoints[0].longitude)], 16);
        } else {
            map.setView([metrics.start_lat, metrics.start_lng], 15);
        }
    }

    function switchMapLayer(type) {
        if (!map || !tileLayers[type]) return;
        if (currentBaseLayer) {
            map.removeLayer(currentBaseLayer);
        }
        currentBaseLayer = tileLayers[type];
        currentBaseLayer.addTo(map);
    }

    function focusMapOnPoint(lat, lng, speed, timeLabel, titleText) {
        if (!map || isNaN(lat) || isNaN(lng)) return;
        if (focusMarker) map.removeLayer(focusMarker);

        focusMarker = L.marker([lat, lng], {
            icon: L.divIcon({
                className: '',
                iconSize: [18, 18],
                iconAnchor: [9, 9],
                html: '<div class="focus-pin-dot"></div>'
            }),
            zIndexOffset: 900
        }).addTo(map);

        focusMarker.bindPopup(
            `<div style="direction:rtl;text-align:right;font-family:Cairo,sans-serif;font-size:13px;padding:2px 4px;">
                <strong style="color:#971048;">${escapeHtml(titleText || 'نقطة محددة على المسار')}</strong><br>
                <b>السرعة:</b> ${escapeHtml(speed)} كم/س<br>
                <b>التوقيت:</b> ${escapeHtml(timeLabel || '-')}<br>
                <span style="font-size:11px;color:#666;">${lat.toFixed(6)}, ${lng.toFixed(6)}</span>
            </div>`
        ).openPopup();

        map.setView([lat, lng], Math.max(map.getZoom(), 16), { animate: true });

        const mapEl = document.getElementById('map');
        if (mapEl) mapEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function initMap() {
        if (typeof L === 'undefined') {
            const mapDiv = document.getElementById('map');
            if (mapDiv) {
                mapDiv.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-white">تعذر تحميل مكتبة الخرائط. يرجى التحقق من الاتصال بالإنترنت.</div>';
            }
            return;
        }

        // Define free, watermark-free high-resolution tile layers
        const esriSatellite = L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            { maxNativeZoom: 19, maxZoom: 20, attribution: 'Esri World Imagery' }
        );
        const esriHybrid = L.layerGroup([
            L.tileLayer(
                'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                { maxNativeZoom: 19, maxZoom: 20, attribution: 'Esri World Imagery' }
            ),
            L.tileLayer(
                'https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}',
                { maxNativeZoom: 19, maxZoom: 20, opacity: 0.9 }
            ),
            L.tileLayer(
                'https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Transportation/MapServer/tile/{z}/{y}/{x}',
                { maxNativeZoom: 19, maxZoom: 20, opacity: 0.7 }
            )
        ]);
        const osmRoadmap = L.tileLayer(
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            { maxZoom: 19, attribution: '&copy; OpenStreetMap' }
        );

        tileLayers = {
            hybrid: esriHybrid,
            satellite: esriSatellite,
            roadmap: osmRoadmap
        };

        map = L.map('map', {
            center: [metrics.current_lat, metrics.current_lng],
            zoom: 16,
            zoomControl: true,
            attributionControl: true
        });

        // Default to Hybrid (best for camel racetracks + Arabic place labels)
        switchMapLayer('hybrid');

        routeLayerGroup = L.layerGroup().addTo(map);

        // 1. True Start Marker (Green pin at FIRST GPS point)
        if (metrics.has_coordinates) {
            startMarker = L.marker([metrics.start_lat, metrics.start_lng], {
                icon: L.divIcon({
                    className: '',
                    iconSize: [18, 18],
                    iconAnchor: [9, 9],
                    html: '<div class="start-pin-dot"></div>'
                }),
                zIndexOffset: 200
            }).addTo(map).bindPopup(
                `<div style="direction:rtl;text-align:right;font-family:Cairo,sans-serif;font-size:13px;">
                    <strong style="color:#198754;">نقطة بداية التدريب</strong><br>
                    ${escapeHtml(LOCATION_NAME || 'موقع الانطلاق')}
                </div>`
            );
        }

        // 2. Peak Speed Marker (Gold star at highest speed coordinate)
        if (metrics.max_speed_lat && metrics.max_speed_lng && maxSpeedVal > 0) {
            peakMarker = L.marker([parseFloat(metrics.max_speed_lat), parseFloat(metrics.max_speed_lng)], {
                icon: L.divIcon({
                    className: '',
                    iconSize: [28, 28],
                    iconAnchor: [14, 14],
                    html: '<div class="peak-star-pin"><i class="bx bxs-star"></i></div>'
                }),
                zIndexOffset: 300
            }).addTo(map).bindPopup(
                `<div style="direction:rtl;text-align:right;font-family:Cairo,sans-serif;font-size:13px;">
                    <strong style="color:#c1121f;">ذروة السرعة (Max Speed)</strong><br>
                    <b>السرعة:</b> ${maxSpeedVal.toFixed(2)} كم/س<br>
                    <b>التوقيت:</b> ${escapeHtml(metrics.max_speed_time || '-')}
                </div>`
            );
        }

        // 3. Draw Route & Fit Map
        drawRouteOnMap();
        fitMapToRoute();

        // 4. Current / End Circular Camel/Worker Marker
        const endPos = routePoints.length > 0
            ? [parseFloat(routePoints[routePoints.length - 1].latitude), parseFloat(routePoints[routePoints.length - 1].longitude)]
            : [metrics.current_lat, metrics.current_lng];

        currentMarker = L.marker(endPos, {
            icon: createCamelDivIcon(),
            zIndexOffset: 500
        }).addTo(map).bindPopup(buildCurrentMarkerHtml(metrics.current_speed, metrics.distance_km));
    }

    function animateMarkerSmooth(marker, startPos, endPos, durationMs) {
        if (!marker) return;
        const startTime = performance.now();
        function step(now) {
            const frac = Math.min((now - startTime) / durationMs, 1);
            const lat = startPos.lat + (endPos.lat - startPos.lat) * frac;
            const lng = startPos.lng + (endPos.lng - startPos.lng) * frac;
            marker.setLatLng([lat, lng]);
            if (isTrackingMode && map) {
                map.setView([lat, lng], Math.max(map.getZoom(), 17), { animate: false });
            }
            if (frac < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    // =========================================================================
    // 4. INTERACTIVE ROUTE REPLAY ENGINE
    // =========================================================================
    function updateReplayUI(index) {
        if (routePoints.length === 0) return;
        const idx = Math.max(0, Math.min(index, routePoints.length - 1));
        replayIndex = idx;
        const pt = routePoints[idx];

        const slider = document.getElementById('replay-slider');
        const speedEl = document.getElementById('replay-speed-readout');
        const timeEl = document.getElementById('replay-time-readout');
        const idxEl = document.getElementById('replay-index-readout');

        if (slider) {
            slider.max = Math.max(0, routePoints.length - 1);
            slider.value = idx;
        }
        if (speedEl) speedEl.textContent = parseFloat(pt.speed || 0).toFixed(1) + ' كم/س';
        if (timeEl) timeEl.textContent = formatTimeOnly(pt.created_at);
        if (idxEl) idxEl.textContent = (idx + 1) + '/' + routePoints.length;

        if (map) {
            const pos = [parseFloat(pt.latitude), parseFloat(pt.longitude)];
            if (!replayMarker) {
                replayMarker = L.marker(pos, {
                    icon: L.divIcon({
                        className: '',
                        iconSize: [20, 20],
                        iconAnchor: [10, 10],
                        html: '<div class="replay-pin-dot"></div>'
                    }),
                    zIndexOffset: 800
                }).addTo(map);
            } else {
                replayMarker.setLatLng(pos);
                if (!map.hasLayer(replayMarker)) replayMarker.addTo(map);
            }
            if (isReplaying) map.panTo(pos, { animate: true, duration: 0.25 });
        }
    }

    function stopReplay() {
        isReplaying = false;
        if (replayTimer) {
            clearInterval(replayTimer);
            replayTimer = null;
        }
        const btn = document.getElementById('btn-replay-toggle');
        if (btn) {
            btn.classList.remove('btn-danger');
            btn.classList.add('btn-primary');
            btn.innerHTML = '<i class="bx bx-play"></i> إعادة تشغيل المسار';
        }
    }

    function startReplay() {
        if (routePoints.length < 2) return;
        if (replayIndex >= routePoints.length - 1) {
            replayIndex = 0;
        }
        isReplaying = true;
        const btn = document.getElementById('btn-replay-toggle');
        if (btn) {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-danger');
            btn.innerHTML = '<i class="bx bx-pause"></i> إيقاف مؤقت';
        }

        const speedSel = document.getElementById('replay-speed-select');
        const multiplier = speedSel ? parseInt(speedSel.value, 10) || 2 : 2;
        const intervalMs = Math.max(60, Math.round(500 / multiplier));

        updateReplayUI(replayIndex);
        if (replayTimer) clearInterval(replayTimer);
        replayTimer = setInterval(function () {
            if (replayIndex >= routePoints.length - 1) {
                stopReplay();
                return;
            }
            replayIndex++;
            updateReplayUI(replayIndex);
        }, intervalMs);
    }

    // =========================================================================
    // 5. SPEED PROFILE CHART (Chart.js v4.2.1)
    // =========================================================================
    function initSpeedChart() {
        const canvas = document.getElementById('speedProfileChart');
        if (!canvas || typeof Chart === 'undefined') return;
        if (chartPoints.length === 0) return;

        const ctx = canvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 260);
        gradient.addColorStop(0, 'rgba(151, 16, 72, 0.32)');
        gradient.addColorStop(1, 'rgba(151, 16, 72, 0.02)');

        const labels = chartPoints.map(p => formatTimeOnly(p.created_at));
        const speeds = chartPoints.map(p => parseFloat(p.speed || 0));
        const avgLine = chartPoints.map(() => parseFloat(avgSpeedVal.toFixed(2)));

        speedChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'السرعة (كم/س)',
                        data: speeds,
                        borderColor: '#971048',
                        backgroundColor: gradient,
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                        pointRadius: chartPoints.length > 60 ? 1.5 : 3.5,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#971048'
                    },
                    {
                        label: 'متوسط السرعة',
                        data: avgLine,
                        borderColor: '#0d6efd',
                        borderWidth: 1.5,
                        borderDash: [6, 6],
                        fill: false,
                        pointRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                onClick: function (evt, elements) {
                    if (elements && elements.length > 0) {
                        const idx = elements[0].index;
                        const pt = chartPoints[idx];
                        if (pt) {
                            focusMapOnPoint(
                                parseFloat(pt.latitude),
                                parseFloat(pt.longitude),
                                parseFloat(pt.speed || 0).toFixed(2),
                                formatTimeOnly(pt.created_at),
                                'نقطة من منحنى السرعة'
                            );
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: { font: { family: 'Cairo, sans-serif', size: 12 } }
                    },
                    tooltip: {
                        rtl: true,
                        callbacks: {
                            label: function (ctxItem) {
                                return ctxItem.dataset.label + ': ' + parseFloat(ctxItem.parsed.y).toFixed(2) + ' كم/س';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: { maxTicksLimit: 10, font: { size: 11 } },
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => value + ' كم/س',
                            font: { size: 11 }
                        }
                    }
                }
            }
        });
    }

    function appendPointToChart(log) {
        const emptyEl = document.getElementById('speed-chart-empty');
        if (emptyEl) emptyEl.classList.add('d-none');

        chartPoints.push(log);
        if (!speedChart) {
            initSpeedChart();
            return;
        }
        if (chartPoints.length > 220) {
            chartPoints.shift();
            speedChart.data.labels.shift();
            speedChart.data.datasets[0].data.shift();
            speedChart.data.datasets[1].data.shift();
        }
        speedChart.data.labels.push(formatTimeOnly(log.created_at));
        speedChart.data.datasets[0].data.push(parseFloat(log.speed || 0));
        speedChart.data.datasets[1].data = speedChart.data.labels.map(() => parseFloat(avgSpeedVal.toFixed(2)));
        speedChart.update('none');
    }

    // =========================================================================
    // 6. UI UPDATERS (KPIs, STATUS, SIGNAL, CHAT, LOG TABLE)
    // =========================================================================
    function renderStatusBadge(status) {
        const badge = document.getElementById('session-status-badge');
        if (!badge) return;
        if (status === 'working') {
            badge.innerHTML = '<span class="badge bg-success px-3 py-2 font-12"><span class="live-pulse bg-white"></span> قيد التدريب (نشطة)</span>';
        } else if (status === 'pending') {
            badge.innerHTML = '<span class="badge bg-warning text-dark px-3 py-2 font-12"><i class="bx bx-time"></i> قيد الانتظار</span>';
        } else if (status === 'stop') {
            badge.innerHTML = '<span class="badge bg-danger px-3 py-2 font-12"><i class="bx bx-pause"></i> متوقفة مؤقتاً</span>';
        } else if (status === 'end') {
            badge.innerHTML = '<span class="badge bg-secondary px-3 py-2 font-12"><i class="bx bx-check-circle"></i> منتهية</span>';
        }

        if (currentMarker) {
            currentMarker.setIcon(createCamelDivIcon());
        }

        const btnPause = document.getElementById('btn-pause-session');
        const btnResume = document.getElementById('btn-resume-session');
        const btnEnd = document.getElementById('btn-end-session');
        const btnSim = document.getElementById('btn-start-simulation');

        if (status === 'end') {
            if (btnPause) btnPause.classList.add('d-none');
            if (btnResume) btnResume.classList.add('d-none');
            if (btnEnd) btnEnd.classList.add('d-none');
            if (btnSim) btnSim.classList.add('d-none');
            stopLivePolling();
            stopDurationTicker();
        } else if (status === 'working') {
            if (btnPause) btnPause.classList.remove('d-none');
            if (btnResume) btnResume.classList.add('d-none');
        } else {
            if (btnPause) btnPause.classList.add('d-none');
            if (btnResume) btnResume.classList.remove('d-none');
        }
    }

    function renderSignalBadge(status, activity) {
        const el = document.getElementById('live-signal-badge');
        if (!el) return;
        if (status === 'end') {
            el.innerHTML = '<span class="badge bg-light text-muted border px-2 py-1 font-12"><i class="bx bx-archive"></i> أرشيف مكتمل</span>';
            return;
        }
        if (status !== 'working' || !activity) {
            el.innerHTML = '';
            return;
        }
        if (activity.is_desert_mode) {
            el.innerHTML = '<span class="badge bg-warning text-dark border px-2 py-1 font-12"><i class="bx bx-wifi-off"></i> وضع صحراوي (تسجيل محلي)</span>';
        } else if (activity.is_weak_connection) {
            el.innerHTML = `<span class="badge bg-warning text-dark border px-2 py-1 font-12"><i class="bx bx-signal-2"></i> إشارة ضعيفة (منذ ${activity.diff_seconds} ث)</span>`;
        } else {
            el.innerHTML = '<span class="badge bg-light text-success border border-success px-2 py-1 font-12"><i class="bx bx-radio-circle-marked"></i> بث مباشر نشط</span>';
        }
    }

    function prependLogRow(log) {
        const tbody = document.getElementById('speed-logs-tbody');
        if (!tbody) return;
        const emptyRow = document.getElementById('speed-logs-empty-row');
        if (emptyRow) emptyRow.remove();

        const spd = parseFloat(log.speed || 0);
        const lat = parseFloat(log.latitude);
        const lng = parseFloat(log.longitude);
        const badgeCls = getSpeedBadgeClass(spd);
        const timeStr = formatDateTime(log.created_at);
        const timeShort = formatTimeOnly(log.created_at);

        const rowHtml = `
            <tr style="animation: fadeInRow 0.5s ease-out; background-color: rgba(25, 135, 84, 0.06);">
                <td class="text-center text-muted font-12">${totalLogsCount}</td>
                <td><span class="badge ${badgeCls} font-12 fw-bold px-2 py-1">${spd.toFixed(2)} كم/س</span></td>
                <td dir="ltr" class="font-12">${lat}</td>
                <td dir="ltr" class="font-12">${lng}</td>
                <td class="font-12">${log.location_name ? '<i class="bx bx-map text-danger"></i> ' + escapeHtml(log.location_name) : '<span class="text-muted">-</span>'}</td>
                <td class="font-12" dir="ltr" style="text-align:right;">${escapeHtml(timeStr)}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 btn-focus-point"
                            data-lat="${lat}" data-lng="${lng}" data-speed="${spd.toFixed(2)}" data-time="${escapeHtml(timeShort)}">
                        <i class="bx bx-target-lock"></i>
                    </button>
                    <a href="https://www.google.com/maps?q=${lat},${lng}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary py-0 px-2">
                        <i class="bx bx-link-external"></i>
                    </a>
                </td>
            </tr>`;
        tbody.insertAdjacentHTML('afterbegin', rowHtml);

        while (tbody.children.length > 60) {
            tbody.removeChild(tbody.lastElementChild);
        }
    }

    function appendChatBubble(chat) {
        if (!chat || seenChatIds.has(Number(chat.id))) return;
        seenChatIds.add(Number(chat.id));

        const container = document.getElementById('session-chat-container');
        if (!container) return;
        const emptyEl = document.getElementById('chat-empty-state');
        if (emptyEl) emptyEl.remove();

        let bodyHtml = '';
        if (chat.content_type === 'text') {
            bodyHtml = `<p class="mb-0 font-13">${escapeHtml(chat.content)}</p>`;
        } else if (chat.content_type === 'image') {
            bodyHtml = `<img src="${escapeHtml(chat.content)}" class="img-fluid rounded border my-1" style="max-height:150px;cursor:pointer;" onclick="window.open(this.src)">`;
        } else if (chat.content_type === 'sound' || chat.content_type === 'audio') {
            bodyHtml = `<audio src="${escapeHtml(chat.content)}" controls class="w-100 my-1" style="max-height:38px;"></audio>`;
        }

        const bubbleHtml = `
            <div class="chat-bubble ${chat.is_owner ? 'chat-owner' : 'chat-worker'}" style="animation: fadeInRow 0.4s ease;">
                <div class="fw-bold font-12 mb-1">
                    <i class="bx ${chat.is_owner ? 'bx-user' : 'bx-run'}"></i>
                    ${chat.is_owner ? 'المالك' : 'العامل'} (${escapeHtml(chat.sender_name)})
                </div>
                ${bodyHtml}
                <span class="chat-meta">${escapeHtml(chat.created_at)}</span>
            </div>`;
        container.insertAdjacentHTML('beforeend', bubbleHtml);
        container.scrollTop = container.scrollHeight;

        const countBadge = document.getElementById('chats-count-badge');
        if (countBadge) countBadge.textContent = String(seenChatIds.size);
    }

    function ingestNewLocation(log, stats, animate) {
        if (!log) return;
        const logId = Number(log.id || 0);
        if (logId > 0 && seenLogIds.has(logId)) {
            return;
        }
        if (logId > 0) {
            seenLogIds.add(logId);
            if (logId > lastLogId) lastLogId = logId;
        }

        const lat = parseFloat(log.latitude);
        const lng = parseFloat(log.longitude);
        const spd = parseFloat(log.speed || 0);
        if (isNaN(lat) || isNaN(lng) || (lat === 0 && lng === 0)) return;

        const newCoord = { lat: lat, lng: lng };
        const prevPoint = routePoints.length > 0 ? routePoints[routePoints.length - 1] : null;
        const oldCoord = prevPoint
            ? { lat: parseFloat(prevPoint.latitude), lng: parseFloat(prevPoint.longitude) }
            : (currentMarker ? { lat: currentMarker.getLatLng().lat, lng: currentMarker.getLatLng().lng } : newCoord);

        routePoints.push({
            id: logId,
            speed: spd,
            latitude: lat,
            longitude: lng,
            created_at: log.created_at
        });

        totalLogsCount++;
        if (spd > maxSpeedVal) maxSpeedVal = spd;

        const replayBar = document.getElementById('replay-controls-bar');
        if (replayBar && routePoints.length >= 2) replayBar.classList.remove('d-none');
        const slider = document.getElementById('replay-slider');
        if (slider && !isReplaying) {
            slider.max = Math.max(0, routePoints.length - 1);
            slider.value = routePoints.length - 1;
        }

        if (map) {
            const bearing = calculateBearing(oldCoord.lat, oldCoord.lng, newCoord.lat, newCoord.lng);
            currentBearing = bearing;
            const needle = document.getElementById('compass-needle');
            if (needle) {
                needle.style.transform = 'rotate(' + bearing + 'deg)';
            }

            drawRouteOnMap();
            if (currentMarker) {
                if (animate) {
                    animateMarkerSmooth(currentMarker, oldCoord, newCoord, 1600);
                } else {
                    currentMarker.setLatLng([lat, lng]);
                }
            }
            if (!isTrackingMode && !isReplaying) {
                map.panTo([lat, lng], { animate: true });
            }
        }

        appendPointToChart({ id: logId, speed: spd, latitude: lat, longitude: lng, created_at: log.created_at });
        prependLogRow(log);

        if (stats) {
            applySessionStats(stats);
        }
    }

    function applySessionStats(s) {
        if (!s) return;
        const curSpd = parseFloat(s.current_speed !== undefined ? s.current_speed : s.speed);
        const avgSpd = parseFloat(s.average_speed);
        const dist = parseFloat(s.distance);
        const maxSpd = s.max_speed !== undefined ? parseFloat(s.max_speed) : maxSpeedVal;

        if (!isNaN(curSpd)) {
            const el = document.getElementById('card-current-speed');
            if (el) el.innerHTML = `${curSpd.toFixed(2)} <span class="font-13">كم/س</span>`;
        }
        if (!isNaN(maxSpd)) {
            maxSpeedVal = Math.max(maxSpeedVal, maxSpd);
            const el = document.getElementById('card-max-speed');
            if (el) el.innerHTML = `${maxSpeedVal.toFixed(2)} <span class="font-13">كم/س</span>`;
            const badge = document.getElementById('chart-peak-badge');
            if (badge) badge.textContent = maxSpeedVal.toFixed(1);
        }
        if (!isNaN(avgSpd)) {
            avgSpeedVal = avgSpd;
            const el = document.getElementById('card-average-speed');
            if (el) el.innerHTML = `${avgSpd.toFixed(2)} <span class="font-13">كم/س</span>`;
            const badge = document.getElementById('chart-avg-badge');
            if (badge) badge.textContent = avgSpd.toFixed(1);
        }
        if (!isNaN(dist)) {
            const el = document.getElementById('card-distance');
            if (el) el.innerHTML = `${dist.toFixed(2)} <span class="font-13">كم</span>`;
        }
        if (s.round_time && !isSimulating) {
            const el = document.getElementById('card-session-duration');
            if (el) el.textContent = s.round_time;
        }
        if (s.logs_count !== undefined) {
            totalLogsCount = Math.max(totalLogsCount, Number(s.logs_count));
            const el = document.getElementById('card-logs-count');
            if (el) el.innerHTML = `${totalLogsCount.toLocaleString()} <span class="font-13 fw-normal text-muted">نقطة</span>`;
            const tb = document.getElementById('table-logs-total-badge');
            if (tb) tb.textContent = `${totalLogsCount.toLocaleString()} نقطة`;
        }
        if (s.performance !== undefined) {
            const pv = Math.min(100, Math.max(0, parseFloat(s.performance) || 0));
            const pb = document.getElementById('card-performance-bar');
            const pBadge = document.getElementById('card-performance-badge');
            if (pb) pb.style.width = pv + '%';
            if (pBadge) pBadge.textContent = pv.toFixed(1) + '%';
        }
        if (currentMarker && !isNaN(curSpd)) {
            currentMarker.setPopupContent(buildCurrentMarkerHtml(curSpd, isNaN(dist) ? metrics.distance_km : dist));
        }
        if (s.round_status && s.round_status !== sessionStatus) {
            sessionStatus = s.round_status;
            renderStatusBadge(sessionStatus);
        }
        if (s.session_ended_at) {
            const endEl = document.getElementById('session-ended-at-text');
            if (endEl) endEl.innerHTML = '<i class="bx bx-calendar-check text-secondary"></i> ' + escapeHtml(s.session_ended_at);
        }
        if (s.summary_text || s.summary_audio_url || s.summary_image_url) {
            const card = document.getElementById('session-summary-card');
            if (card) card.classList.remove('d-none');
            if (s.summary_text) {
                const tEl = document.getElementById('summary-text-content');
                if (tEl) { tEl.textContent = s.summary_text; tEl.classList.remove('d-none'); }
            }
            if (s.summary_audio_url) {
                const aWrap = document.getElementById('summary-audio-wrap');
                const aEl = document.getElementById('summary-audio-player');
                if (aEl && aEl.src !== s.summary_audio_url) aEl.src = s.summary_audio_url;
                if (aWrap) aWrap.classList.remove('d-none');
            }
            if (s.summary_image_url) {
                const iWrap = document.getElementById('summary-image-wrap');
                const iEl = document.getElementById('summary-image-el');
                if (iEl) iEl.src = s.summary_image_url;
                if (iWrap) iWrap.classList.remove('d-none');
            }
        }
    }

    // =========================================================================
    // 7. DUAL REAL-TIME ENGINE (Live AJAX Polling + Reverb WebSocket)
    // =========================================================================
    function pollLiveSessionData() {
        if (document.hidden || sessionStatus === 'end') return;
        const url = `${URL_LIVE_DATA}?after_id=${lastLogId}&after_chat_id=${lastChatId}`;
        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(res => res.ok ? res.json() : null)
            .then(data => {
                if (!data || !data.success) return;

                const clockEl = document.getElementById('live-poll-clock');
                if (clockEl) clockEl.innerHTML = `<i class="bx bx-sync text-success"></i> تحديث: ${escapeHtml(data.server_time)}`;

                if (data.session) applySessionStats(data.session);
                if (data.activity) renderSignalBadge(data.session ? data.session.round_status : sessionStatus, data.activity);
                if (data.worker) {
                    const dot = document.getElementById('worker-online-dot');
                    const txt = document.getElementById('worker-online-text');
                    if (dot) {
                        dot.classList.toggle('bg-success', !!data.worker.is_online);
                        dot.classList.toggle('bg-secondary', !data.worker.is_online);
                    }
                    if (txt) {
                        txt.className = 'font-11 fw-semibold ' + (data.worker.is_online ? 'text-success' : 'text-muted');
                        txt.textContent = data.worker.is_online ? 'متصل بالتطبيق حالياً' : 'غير متصل حالياً';
                    }
                }
                if (Array.isArray(data.new_logs) && data.new_logs.length > 0) {
                    const animateSingle = data.new_logs.length <= 2;
                    data.new_logs.forEach(log => ingestNewLocation(log, null, animateSingle));
                    if (data.session) applySessionStats(data.session);
                }
                if (data.last_log_id && data.last_log_id > lastLogId) lastLogId = data.last_log_id;
                if (Array.isArray(data.new_chats) && data.new_chats.length > 0) data.new_chats.forEach(appendChatBubble);
                if (data.last_chat_id && data.last_chat_id > lastChatId) lastChatId = data.last_chat_id;
            })
            .catch(() => {});
    }

    function startLivePolling() {
        if (sessionStatus === 'end') return;
        if (livePollTimer) clearInterval(livePollTimer);
        livePollTimer = setInterval(pollLiveSessionData, 3000);
    }

    function stopLivePolling() {
        if (livePollTimer) {
            clearInterval(livePollTimer);
            livePollTimer = null;
        }
    }

    function stopDurationTicker() {
        if (durationTickTimer) {
            clearInterval(durationTickTimer);
            durationTickTimer = null;
        }
    }

    function initWebSocketListener() {
        if (typeof Echo === 'undefined' || !REVERB_CFG || !REVERB_CFG.enabled || !REVERB_CFG.key) return;
        try {
            const isHttps = window.location.protocol === 'https:';
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: REVERB_CFG.key,
                wsHost: REVERB_CFG.host || window.location.hostname,
                wsPort: REVERB_CFG.port || 8080,
                wssPort: REVERB_CFG.port || 8080,
                forceTLS: isHttps || REVERB_CFG.scheme === 'https',
                enabledTransports: ['ws', 'wss'],
                cluster: 'mt1'
            });

            window.Echo.channel('tracking-session.' + SESSION_ID)
                .listen('.location.updated', function (event) {
                    if (!isSimulating && event && event.log) {
                        ingestNewLocation(event.log, event.sessionStats, true);
                    }
                });
        } catch (e) {}
    }

    // =========================================================================
    // 8. ADMIN CONTROLS (Status Change, Simulation, Clear Logs, Map Tools)
    // =========================================================================
    function updateSessionStatus(newStatus, successMsg) {
        fetch(URL_UPDATE_STATUS, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                sessionStatus = newStatus;
                renderStatusBadge(newStatus);
                if (typeof toastr !== 'undefined') toastr.success(successMsg);
                if (newStatus === 'working') startLivePolling();
            } else if (typeof toastr !== 'undefined') {
                toastr.error('تعذر تحديث حالة الجلسة');
            }
        })
        .catch(() => {
            if (typeof toastr !== 'undefined') toastr.error('حدث خطأ أثناء الاتصال بالسيرفر');
        });
    }

    function triggerGpsSimulatePing() {
        const durEl = document.getElementById('card-session-duration');
        const currentDuration = durEl ? durEl.textContent.trim() : '00:00:00';
        const angle = simulatedLogsCount * 0.025;
        const radius = 0.005;
        const lat = metrics.start_lat + radius * Math.sin(angle);
        const lng = (metrics.start_lng - radius * 1.5) + radius * 1.5 * Math.cos(angle);
        const speed = parseFloat((Math.random() * (34 - 12) + 12).toFixed(2));

        fetch('/api/tracking/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({
                training_session_id: SESSION_ID,
                latitude: lat,
                longitude: lng,
                speed: speed,
                location_name: 'موقع محاكاة #' + (simulatedLogsCount + 1),
                duration: currentDuration
            })
        })
        .then(res => res.ok ? res.json() : null)
        .then(data => {
            if (data && data.success && data.data) {
                simulatedLogsCount++;
                ingestNewLocation(data.data, {
                    current_speed: data.current_speed,
                    average_speed: data.average_speed,
                    distance: data.distance,
                    round_status: data.round_status
                }, true);
            }
        })
        .catch(() => {});
    }

    function bindEvents() {
        // Map type switcher (Hybrid / Satellite / Roadmap)
        document.querySelectorAll('.map-type-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const type = this.getAttribute('data-map-type');
                if (type) switchMapLayer(type);
                document.querySelectorAll('.map-type-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Fullscreen toggle
        const btnFs = document.getElementById('btn-map-fullscreen');
        if (btnFs) {
            btnFs.addEventListener('click', function () {
                const mapEl = document.getElementById('map');
                if (!mapEl) return;
                const isFs = mapEl.classList.toggle('map-fullscreen');
                this.innerHTML = isFs ? '<i class="bx bx-exit-fullscreen"></i>' : '<i class="bx bx-fullscreen"></i>';
                if (map) setTimeout(() => map.invalidateSize(), 150);
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    const mapEl = document.getElementById('map');
                    if (mapEl && mapEl.classList.contains('map-fullscreen')) {
                        mapEl.classList.remove('map-fullscreen');
                        btnFs.innerHTML = '<i class="bx bx-fullscreen"></i>';
                        if (map) setTimeout(() => map.invalidateSize(), 150);
                    }
                }
            });
        }

        // Toggle speed-colored polyline
        const btnColors = document.getElementById('btn-toggle-speed-colors');
        if (btnColors) {
            btnColors.addEventListener('click', function () {
                useSpeedColors = !useSpeedColors;
                this.classList.toggle('btn-primary', useSpeedColors);
                this.classList.toggle('btn-outline-primary', !useSpeedColors);
                drawRouteOnMap();
            });
        }

        // Fit full route
        const btnFit = document.getElementById('btn-fit-route');
        if (btnFit) btnFit.addEventListener('click', fitMapToRoute);

        // Peak speed card -> zoom to peak marker
        const peakCard = document.getElementById('card-max-speed-box');
        if (peakCard && metrics.max_speed_lat && metrics.max_speed_lng) {
            peakCard.addEventListener('click', function () {
                focusMapOnPoint(
                    parseFloat(metrics.max_speed_lat),
                    parseFloat(metrics.max_speed_lng),
                    maxSpeedVal.toFixed(2),
                    metrics.max_speed_time || '-',
                    'نقطة أعلى سرعة مسجلة'
                );
            });
        }

        // Tracking / Follow mode
        const btnTrack = document.getElementById('btn-track-mode');
        if (btnTrack) {
            btnTrack.addEventListener('click', function () {
                const compass = document.getElementById('compass-container');
                isTrackingMode = !isTrackingMode;
                if (isTrackingMode) {
                    this.classList.remove('btn-outline-primary');
                    this.classList.add('btn-primary');
                    this.innerHTML = '<i class="bx bx-navigation"></i> وضع التتبع نشط';
                    if (compass) compass.style.display = 'flex';
                    if (map && currentMarker) {
                        if (routePoints.length >= 2) {
                            const p1 = routePoints[routePoints.length - 2];
                            const p2 = routePoints[routePoints.length - 1];
                            currentBearing = calculateBearing(parseFloat(p1.latitude), parseFloat(p1.longitude), parseFloat(p2.latitude), parseFloat(p2.longitude));
                        }
                        const needle = document.getElementById('compass-needle');
                        if (needle) needle.style.transform = 'rotate(' + currentBearing + 'deg)';
                        map.setView(currentMarker.getLatLng(), 17, { animate: true });
                    }
                } else {
                    this.classList.remove('btn-primary');
                    this.classList.add('btn-outline-primary');
                    this.innerHTML = '<i class="bx bx-navigation"></i> وضع التتبع';
                    if (compass) compass.style.display = 'none';
                    fitMapToRoute();
                }
            });
        }

        // Replay controls
        const btnReplay = document.getElementById('btn-replay-toggle');
        if (btnReplay) {
            btnReplay.addEventListener('click', function () {
                if (isReplaying) stopReplay();
                else startReplay();
            });
        }
        const btnReplayReset = document.getElementById('btn-replay-reset');
        if (btnReplayReset) {
            btnReplayReset.addEventListener('click', function () {
                stopReplay();
                updateReplayUI(0);
            });
        }
        const replaySlider = document.getElementById('replay-slider');
        if (replaySlider) {
            replaySlider.addEventListener('input', function () {
                stopReplay();
                updateReplayUI(parseInt(this.value, 10) || 0);
            });
        }
        const replaySpeed = document.getElementById('replay-speed-select');
        if (replaySpeed) {
            replaySpeed.addEventListener('change', function () {
                if (isReplaying) startReplay();
            });
        }

        // Table row focus-point button
        const tbody = document.getElementById('speed-logs-tbody');
        if (tbody) {
            tbody.addEventListener('click', function (e) {
                const btn = e.target.closest('.btn-focus-point');
                if (!btn) return;
                focusMapOnPoint(
                    parseFloat(btn.getAttribute('data-lat')),
                    parseFloat(btn.getAttribute('data-lng')),
                    btn.getAttribute('data-speed'),
                    btn.getAttribute('data-time'),
                    'نقطة من سجل السرعات'
                );
            });
        }

        // Pause / Resume / End Session buttons
        const btnPause = document.getElementById('btn-pause-session');
        if (btnPause) {
            btnPause.addEventListener('click', function () {
                confirmDialog('إيقاف الجلسة مؤقتاً؟', 'يمكن استئناف التدريب مرة أخرى لاحقاً.', 'إيقاف مؤقت', function () {
                    updateSessionStatus('stop', 'تم إيقاف الجلسة مؤقتاً.');
                });
            });
        }

        const btnResume = document.getElementById('btn-resume-session');
        if (btnResume) {
            btnResume.addEventListener('click', function () {
                updateSessionStatus('working', 'تم استئناف الجلسة التدريبية.');
            });
        }

        const btnEnd = document.getElementById('btn-end-session');
        if (btnEnd) {
            btnEnd.addEventListener('click', function () {
                confirmDialog('إنهاء الجلسة التدريبية #' + SESSION_ID + '؟', 'سيتم إغلاق الجلسة وحفظها في الأرشيف.', 'نعم، إنهاء الجلسة', function () {
                    if (isSimulating && document.getElementById('btn-start-simulation')) {
                        document.getElementById('btn-start-simulation').click();
                    }
                    updateSessionStatus('end', 'تم إنهاء الجلسة التدريبية بنجاح.');
                });
            });
        }

        // Simulation toggle (Advanced Tools dropdown)
        const btnSim = document.getElementById('btn-start-simulation');
        if (btnSim) {
            btnSim.addEventListener('click', function () {
                if (isSimulating) {
                    clearInterval(simulationTimer);
                    stopDurationTicker();
                    isSimulating = false;
                    this.classList.remove('text-danger');
                    this.classList.add('text-success');
                    this.innerHTML = '<i class="bx bx-play-circle me-1"></i> بدء محاكاة حركة (للاختبار)';
                    updateSessionStatus('stop', 'تم إيقاف المحاكاة.');
                } else {
                    isSimulating = true;
                    this.classList.remove('text-success');
                    this.classList.add('text-danger');
                    this.innerHTML = '<i class="bx bx-stop-circle me-1"></i> إيقاف المحاكاة الجارية';
                    renderStatusBadge('working');

                    const durEl = document.getElementById('card-session-duration');
                    let secs = timeStringToSeconds(durEl ? durEl.textContent : '00:00:00');
                    stopDurationTicker();
                    durationTickTimer = setInterval(function () {
                        secs++;
                        if (durEl) durEl.textContent = secondsToTimeString(secs);
                    }, 1000);

                    triggerGpsSimulatePing();
                    simulationTimer = setInterval(triggerGpsSimulatePing, 3000);
                }
            });
        }

        // Clear Logs button
        const btnClear = document.getElementById('btn-clear-logs');
        if (btnClear) {
            btnClear.addEventListener('click', function () {
                confirmDialog(
                    'مسح جميع سجلات السرعة والمسار؟',
                    'تحذير: هذا الإجراء سيحذف جميع نقاط GPS المسجلة لهذه الجلسة نهائياً ولا يمكن التراجع عنه.',
                    'نعم، احذف السجلات',
                    function () {
                        if (isSimulating && btnSim) btnSim.click();
                        fetch(URL_CLEAR_LOGS)
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    if (typeof toastr !== 'undefined') toastr.success(data.message);
                                    setTimeout(() => window.location.reload(), 700);
                                }
                            })
                            .catch(() => {
                                if (typeof toastr !== 'undefined') toastr.error('حدث خطأ أثناء حذف السجلات.');
                            });
                    }
                );
            });
        }
    }

    // =========================================================================
    // 9. BOOTSTRAP PAGE CLEANLY
    // =========================================================================
    document.addEventListener('DOMContentLoaded', function () {
        bindEvents();
        initSpeedChart();
        if (routePoints.length > 0) {
            const pt = routePoints[routePoints.length - 1];
            const speedEl = document.getElementById('replay-speed-readout');
            const timeEl = document.getElementById('replay-time-readout');
            if (speedEl) speedEl.textContent = parseFloat(pt.speed || 0).toFixed(1) + ' كم/س';
            if (timeEl) timeEl.textContent = formatTimeOnly(pt.created_at);
        }
        const chatBox = document.getElementById('session-chat-container');
        if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
    });

    window.addEventListener('load', function () {
        initMap();
        initWebSocketListener();
        if (sessionStatus !== 'end') {
            startLivePolling();
        }
    });
})();
</script>

@endsection
