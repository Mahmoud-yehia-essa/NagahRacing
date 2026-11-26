@extends('admin.master_admin')
@section('admin')

<style>
    .ts-kpi-card {
        border: 1px solid rgba(0, 0, 0, 0.06);
        border-radius: 14px;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        cursor: pointer;
        text-decoration: none;
        display: block;
        color: inherit;
    }
    .ts-kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 22px rgba(151, 16, 72, 0.12);
        border-color: rgba(151, 16, 72, 0.25);
        color: inherit;
    }
    .ts-kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }
    .pulse-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #198754;
        position: relative;
        vertical-align: middle;
        margin-left: 6px;
    }
    .pulse-dot::after {
        content: "";
        position: absolute;
        inset: -4px;
        border-radius: 50%;
        border: 2px solid #198754;
        animation: tsPulse 1.6s ease-out infinite;
    }
    @keyframes tsPulse {
        0% { transform: scale(0.7); opacity: 0.9; }
        100% { transform: scale(1.8); opacity: 0; }
    }
    .live-strip-card {
        border: 1px solid rgba(25, 135, 84, 0.25);
        background: linear-gradient(135deg, #ffffff 0%, #f4fbf7 100%);
        border-radius: 14px;
        transition: all 0.2s ease;
    }
    .live-strip-card:hover {
        box-shadow: 0 6px 18px rgba(25, 135, 84, 0.15);
        transform: translateY(-2px);
    }
    .live-strip-card.is-paused {
        border-color: rgba(220, 53, 69, 0.25);
        background: linear-gradient(135deg, #ffffff 0%, #fff7f8 100%);
    }
    .live-strip-card.is-desert {
        border-color: rgba(255, 193, 7, 0.45);
        background: linear-gradient(135deg, #ffffff 0%, #fffdf2 100%);
    }
    .worker-avatar-sm {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #f1f3f5;
    }
    .online-indicator {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 11px;
        height: 11px;
        border-radius: 50%;
        border: 2px solid #fff;
    }
    .ts-table th {
        font-weight: 700;
        font-size: 13px;
        background-color: #f8f9fa;
        white-space: nowrap;
        vertical-align: middle;
    }
    .ts-table td {
        vertical-align: middle;
        font-size: 13px;
    }
    .quick-pill {
        border-radius: 999px;
        padding: 5px 14px;
        font-size: 12.5px;
        font-weight: 600;
        border: 1px solid #dee2e6;
        background: #fff;
        color: #495057;
        text-decoration: none;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .quick-pill:hover, .quick-pill.active {
        background: #971048;
        border-color: #971048;
        color: #fff;
    }
    .speed-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 12px;
    }
</style>

<!-- Breadcrumb & Live Control Bar -->
<div class="page-breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div class="d-flex align-items-center">
        <div class="breadcrumb-title pe-3 fw-bold">إدارة جلسات التدريب</div>
        <div class="ps-3 d-none d-sm-block">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">مركز متابعة التدريبات</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="badge bg-light text-dark border px-3 py-2 font-12" id="live-sync-status">
            <i class="bx bx-sync text-success me-1"></i>
            آخر تحديث: <strong id="live-last-updated">{{ now()->format('H:i:s') }}</strong>
        </span>
        <div class="form-check form-switch mb-0 bg-white border rounded-pill px-3 py-1 d-flex align-items-center gap-2">
            <input class="form-check-input m-0" type="checkbox" id="toggle-auto-refresh" checked>
            <label class="form-check-label font-12 fw-bold text-dark m-0" for="toggle-auto-refresh" style="cursor: pointer;">
                تحديث تلقائي (15 ث)
            </label>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btn-manual-refresh">
            <i class="bx bx-refresh"></i> تحديث الآن
        </button>
    </div>
</div>

<!-- KPI Summary Cards -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4">
    <div class="col">
        <a href="{{ route('all.training.sessions', ['status' => 'working']) }}" class="card ts-kpi-card mb-0 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 text-secondary font-13 fw-semibold">
                            @if($stats['live_now'] > 0)<span class="pulse-dot"></span>@endif
                            جلسات نشطة الآن
                        </p>
                        <h3 class="mb-0 fw-bold text-success" id="kpi-live-now">{{ $stats['live_now'] }}</h3>
                        <small class="text-muted font-11">اضغط لعرض الجلسات الجارية</small>
                    </div>
                    <div class="ts-kpi-icon bg-light-success text-success">
                        <i class="bx bx-broadcast"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="{{ route('all.training.sessions', ['status' => 'stop']) }}" class="card ts-kpi-card mb-0 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 text-secondary font-13 fw-semibold">متوقفة مؤقتاً / انتظار</p>
                        <h3 class="mb-0 fw-bold text-warning" id="kpi-paused-pending">{{ $stats['paused_pending'] }}</h3>
                        <small class="text-muted font-11">جلسات غير منتهية بانتظار الاستئناف</small>
                    </div>
                    <div class="ts-kpi-icon bg-light-warning text-warning">
                        <i class="bx bx-pause-circle"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="{{ route('all.training.sessions', ['date_from' => now()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}" class="card ts-kpi-card mb-0 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 text-secondary font-13 fw-semibold">تدريبات اليوم</p>
                        <h3 class="mb-0 fw-bold text-primary" id="kpi-today-count">{{ $stats['today_count'] }}</h3>
                        <small class="text-muted font-11">{{ now()->format('Y-m-d') }}</small>
                    </div>
                    <div class="ts-kpi-icon" style="background: rgba(151,16,72,0.1); color: #971048;">
                        <i class="bx bx-calendar-event"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="{{ route('all.training.sessions') }}" class="card ts-kpi-card mb-0 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 text-secondary font-13 fw-semibold">إجمالي الجلسات والمسافة</p>
                        <h3 class="mb-0 fw-bold text-dark">
                            <span id="kpi-total-count">{{ number_format($stats['total_count']) }}</span>
                            <span class="font-13 fw-normal text-muted">جلسة</span>
                        </h3>
                        <small class="text-info fw-semibold font-12">
                            <i class="bx bx-map-alt"></i>
                            {{ number_format($stats['filtered_distance_km'], 2) }} كم (وفق التصفية)
                        </small>
                    </div>
                    <div class="ts-kpi-icon bg-light-info text-info">
                        <i class="bx bx-stats"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Live Operations Monitoring Strip (Visible whenever active/paused sessions exist) -->
<div id="live-operations-wrapper" class="{{ $activeSessionsPreview->isEmpty() ? 'd-none' : '' }} mb-4">
    <div class="card border-0 shadow-sm" style="border-radius: 14px; border-right: 4px solid #198754 !important;">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                    <span class="pulse-dot"></span>
                    لوحة المتابعة اللحظية للتدريبات الجارية
                    <span class="badge bg-success rounded-pill" id="live-strip-count">{{ $activeSessionsPreview->count() }}</span>
                </h6>
                <span class="text-muted font-12">اضغط على أي جلسة لفتح خريطة التتبع المباشر والسرعات الفورية</span>
            </div>

            <div class="row g-3" id="live-strip-grid">
                @foreach($activeSessionsPreview as $act)
                    @php
                        $stripClass = $act->round_status === 'stop' ? 'is-paused' : ($act->is_desert_mode ? 'is-desert' : '');
                    @endphp
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="live-strip-card p-3 h-100 {{ $stripClass }}">
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                <div>
                                    <div class="fw-bold text-dark font-14">
                                        #{{ $act->id }} — {{ $act->worker ? $act->worker->full_name : 'عامل غير معروف' }}
                                    </div>
                                    <div class="text-muted font-12">
                                        <i class="bx bx-user"></i>
                                        {{ ($act->worker && $act->worker->owner) ? ($act->worker->owner->fname . ' ' . $act->worker->owner->lname) : 'غير محدد' }}
                                    </div>
                                </div>
                                <div>
                                    @if($act->round_status === 'working')
                                        @if($act->is_desert_mode)
                                            <span class="badge bg-warning text-dark"><i class="bx bx-wifi-off"></i> وضع صحراوي</span>
                                        @elseif($act->is_weak_connection)
                                            <span class="badge bg-warning text-dark"><i class="bx bx-signal-2"></i> إشارة ضعيفة</span>
                                        @else
                                            <span class="badge bg-success"><i class="bx bx-radio-circle-marked"></i> بث مباشر</span>
                                        @endif
                                    @elseif($act->round_status === 'stop')
                                        <span class="badge bg-danger"><i class="bx bx-pause"></i> متوقفة</span>
                                    @else
                                        <span class="badge bg-warning text-dark"><i class="bx bx-time"></i> انتظار</span>
                                    @endif
                                </div>
                            </div>

                            <div class="row g-2 text-center my-2 bg-white rounded-3 py-2 border">
                                <div class="col-4 border-start">
                                    <div class="font-11 text-muted">السرعة</div>
                                    <div class="fw-bold text-info font-13">{{ number_format((float)$act->speed, 1) }} <small>كم/س</small></div>
                                </div>
                                <div class="col-4 border-start">
                                    <div class="font-11 text-muted">المسافة</div>
                                    <div class="fw-bold text-success font-13">{{ number_format((float)$act->round_distance_km, 2) }} <small>كم</small></div>
                                </div>
                                <div class="col-4">
                                    <div class="font-11 text-muted">المدة</div>
                                    <div class="fw-bold text-dark font-13">{{ $act->resolved_round_time }}</div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mt-2">
                                <span class="text-muted font-11 text-truncate" style="max-width: 58%;">
                                    <i class="bx bx-map-pin text-danger"></i> {{ $act->location_name ?: 'جاري تحديد الموقع' }}
                                </span>
                                <a href="{{ route('details.training.session', $act->id) }}" class="btn btn-sm btn-success rounded-pill px-3">
                                    <i class="bx bx-radar"></i> متابعة مباشرة
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Quick Filter Pills + Advanced Filter Card -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
    <div class="card-body">
        <!-- Quick Filter Pills -->
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3 pb-3 border-bottom">
            <span class="text-muted font-12 fw-bold me-1"><i class="bx bx-bolt-circle"></i> وصول سريع:</span>
            <a href="{{ route('all.training.sessions') }}"
               class="quick-pill {{ !request()->hasAny(['status', 'owner_id', 'camel_worker_id', 'date_from', 'date_to', 'search']) ? 'active' : '' }}">
                الكل ({{ $stats['total_count'] }})
            </a>
            <a href="{{ route('all.training.sessions', ['status' => 'working']) }}"
               class="quick-pill {{ request('status') === 'working' ? 'active' : '' }}">
                <i class="bx bx-radio-circle-marked"></i> نشطة الآن ({{ $stats['live_now'] }})
            </a>
            <a href="{{ route('all.training.sessions', ['status' => 'active']) }}"
               class="quick-pill {{ request('status') === 'active' ? 'active' : '' }}">
                <i class="bx bx-run"></i> كل غير المنتهية ({{ $stats['live_now'] + $stats['paused_pending'] }})
            </a>
            <a href="{{ route('all.training.sessions', ['status' => 'end']) }}"
               class="quick-pill {{ in_array(request('status'), ['end', 'ended']) ? 'active' : '' }}">
                <i class="bx bx-check-circle"></i> منتهية ({{ $stats['ended_count'] }})
            </a>
            <a href="{{ route('all.training.sessions', ['date_from' => now()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}"
               class="quick-pill {{ request('date_from') === now()->format('Y-m-d') && request('date_to') === now()->format('Y-m-d') ? 'active' : '' }}">
                <i class="bx bx-calendar-star"></i> اليوم ({{ $stats['today_count'] }})
            </a>
        </div>

        <form method="GET" action="{{ route('all.training.sessions') }}" id="sessions-filter-form">
            <div class="row g-3 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <label for="search" class="form-label font-12 fw-bold mb-1">بحث سريع</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="bx bx-search"></i></span>
                        <input type="text" name="search" id="search" class="form-control"
                               value="{{ request('search') }}"
                               placeholder="رقم الجلسة (#151)، اسم العامل/المالك، الموقع...">
                    </div>
                </div>

                <div class="col-md-2 col-sm-6">
                    <label for="owner_id" class="form-label font-12 fw-bold mb-1">المالك</label>
                    <select name="owner_id" id="owner_id" class="form-select form-select-sm select2-filter">
                        <option value="">-- كل الملاك --</option>
                        @foreach($owners as $owner)
                            <option value="{{ $owner->id }}" {{ (string)request('owner_id') === (string)$owner->id ? 'selected' : '' }}>
                                {{ $owner->fname }} {{ $owner->lname }} @if($owner->phone) ({{ $owner->phone }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 col-sm-6">
                    <label for="camel_worker_id" class="form-label font-12 fw-bold mb-1">العامل / المدرب</label>
                    <select name="camel_worker_id" id="camel_worker_id" class="form-select form-select-sm select2-filter">
                        <option value="">-- كل العمال --</option>
                        @foreach($workersList as $worker)
                            <option value="{{ $worker->id }}"
                                    data-owner-id="{{ $worker->owner_id }}"
                                    {{ (string)request('camel_worker_id') === (string)$worker->id ? 'selected' : '' }}>
                                {{ $worker->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 col-sm-6">
                    <label for="status" class="form-label font-12 fw-bold mb-1">الحالة</label>
                    <select name="status" id="status" class="form-select form-select-sm">
                        <option value="">-- كل الحالات --</option>
                        <option value="working" {{ request('status') === 'working' ? 'selected' : '' }}>قيد التدريب (نشطة الآن)</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                        <option value="stop" {{ request('status') === 'stop' ? 'selected' : '' }}>متوقفة مؤقتاً</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>جميع غير المنتهية</option>
                        <option value="end" {{ in_array(request('status'), ['end', 'ended']) ? 'selected' : '' }}>منتهية</option>
                    </select>
                </div>

                <div class="col-md-3 col-sm-12">
                    <div class="row g-2">
                        <div class="col-6">
                            <label for="date_from" class="form-label font-12 fw-bold mb-1">من تاريخ</label>
                            <input type="date" name="date_from" id="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-6">
                            <label for="date_to" class="form-label font-12 fw-bold mb-1">إلى تاريخ</label>
                            <input type="date" name="date_to" id="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                        </div>
                    </div>
                </div>

                <div class="col-md-2 col-sm-6">
                    <label for="per_page" class="form-label font-12 fw-bold mb-1">عدد النتائج</label>
                    <select name="per_page" id="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach([15, 25, 50, 100] as $size)
                            <option value="{{ $size }}" {{ $perPage == $size ? 'selected' : '' }}>{{ $size }} جلسة / صفحة</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-10 col-sm-6 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="bx bx-filter-alt"></i> تطبيق التصفية
                    </button>
                    <a href="{{ route('all.training.sessions') }}" class="btn btn-outline-secondary btn-sm px-3">
                        <i class="bx bx-reset"></i> إعادة ضبط
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Sessions Table Card (Uses dedicated ID to avoid DataTables clashing with server pagination) -->
<div class="card border-0 shadow-sm" style="border-radius: 14px;">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h6 class="mb-0 fw-bold">
                    <i class="bx bx-list-ul text-primary"></i>
                    سجل جلسات التدريب
                    <span class="badge bg-light text-dark border ms-1">{{ number_format($stats['filtered_count']) }} نتيجة</span>
                </h6>
            </div>
            <div class="text-muted font-12">
                @if($sessions->total() > 0)
                    عرض {{ $sessions->firstItem() }} – {{ $sessions->lastItem() }} من أصل {{ number_format($sessions->total()) }} جلسة
                @endif
            </div>
        </div>

        <div class="table-responsive">
            <table id="trainingSessionsTable" class="table table-hover table-bordered align-middle ts-table mb-0" style="width:100%">
                <thead>
                    <tr>
                        <th class="text-center">#الجلسة</th>
                        <th>المالك</th>
                        <th>العامل / المدرب</th>
                        <th>الحالة والاتصال</th>
                        <th>السرعات (كم/س)</th>
                        <th>المسافة والمدة</th>
                        <th>نقاط التتبع والموقع</th>
                        <th>الأداء</th>
                        <th>التوقيت</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $item)
                        @php
                            $isLive = $item->round_status === 'working';
                            $isOnline = $item->worker && ($item->worker->is_online || $item->worker->online_status === 'online');
                            $perf = min(100, max(0, (float)$item->performance));
                            $perfClass = $perf >= 75 ? 'bg-success' : ($perf >= 45 ? 'bg-warning' : 'bg-danger');
                            $maxSpd = max((float)($item->speed_logs_max_speed ?? 0), (float)$item->speed);
                            $logsCount = (int)($item->speed_logs_count ?? $item->logs_count ?? 0);
                        @endphp
                        <tr class="{{ $isLive ? 'table-success bg-opacity-10' : '' }}" id="session-row-{{ $item->id }}">
                            <td class="text-center">
                                <a href="{{ route('details.training.session', $item->id) }}" class="fw-bold text-primary text-decoration-none">
                                    #{{ $item->id }}
                                </a>
                            </td>

                            <td>
                                @if($item->worker && $item->worker->owner)
                                    <div class="fw-bold text-dark">{{ $item->worker->owner->fname }} {{ $item->worker->owner->lname }}</div>
                                    @if($item->worker->owner->phone)
                                        <div class="text-muted font-11" dir="ltr" style="text-align: right;">
                                            <i class="bx bx-phone font-11"></i> {{ $item->worker->owner->phone }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted">غير محدد</span>
                                @endif
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="position-relative flex-shrink-0">
                                        <img src="{{ $item->worker_avatar_url }}" alt="worker" class="worker-avatar-sm"
                                             onerror="this.src='{{ asset('upload/no_image.jpg') }}'">
                                        <span class="online-indicator {{ $isOnline ? 'bg-success' : 'bg-secondary' }}"
                                              title="{{ $isOnline ? 'العامل متصل الآن' : 'العامل غير متصل' }}"></span>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary">{{ $item->worker ? $item->worker->full_name : 'غير معروف' }}</div>
                                        <div class="font-11 {{ $isOnline ? 'text-success' : 'text-muted' }}">
                                            {{ $isOnline ? 'متصل الآن' : 'غير متصل' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex flex-column gap-1 align-items-start" id="status-cell-{{ $item->id }}">
                                    @if($item->round_status == 'working')
                                        <span class="badge bg-success"><i class="bx bx-run"></i> قيد التدريب</span>
                                        @if($item->is_desert_mode)
                                            <span class="badge bg-warning text-dark font-11"><i class="bx bx-wifi-off"></i> وضع صحراوي</span>
                                        @elseif($item->is_weak_connection)
                                            <span class="badge bg-warning text-dark font-11"><i class="bx bx-signal-2"></i> إشارة ضعيفة</span>
                                        @else
                                            <span class="badge bg-light text-success border border-success font-11"><i class="bx bx-radio-circle-marked"></i> إشارة مباشرة</span>
                                        @endif
                                    @elseif($item->round_status == 'pending')
                                        <span class="badge bg-warning text-dark"><i class="bx bx-time"></i> قيد الانتظار</span>
                                    @elseif($item->round_status == 'stop')
                                        <span class="badge bg-danger"><i class="bx bx-pause"></i> متوقفة مؤقتاً</span>
                                    @elseif($item->round_status == 'end')
                                        <span class="badge bg-secondary"><i class="bx bx-check-circle"></i> منتهية</span>
                                    @else
                                        <span class="badge bg-dark">{{ $item->round_status }}</span>
                                    @endif
                                </div>
                            </td>

                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <div>
                                        <span class="speed-chip bg-light-info text-info" title="السرعة الحالية / الأخيرة">
                                            <i class="bx bx-tachometer"></i> {{ number_format((float)$item->speed, 1) }}
                                        </span>
                                        <span class="speed-chip bg-light-danger text-danger" title="أعلى سرعة مسجلة">
                                            <i class="bx bx-trending-up"></i> {{ number_format($maxSpd, 1) }}
                                        </span>
                                    </div>
                                    <div class="text-muted font-11">
                                        المتوسط: <strong>{{ number_format((float)$item->average_speed, 1) }}</strong> كم/س
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="fw-bold text-success">
                                    <i class="bx bx-map-alt"></i> {{ number_format((float)$item->round_distance_km, 2) }} كم
                                </div>
                                <div class="text-dark font-12">
                                    <i class="bx bx-time-five text-primary"></i> {{ $item->resolved_round_time }}
                                </div>
                            </td>

                            <td>
                                <div class="text-dark font-12">
                                    @if($item->location_name)
                                        <i class="bx bx-map-pin text-danger"></i>
                                        <span title="{{ $item->location_name }}">{{ \Illuminate\Support\Str::limit($item->location_name, 26) }}</span>
                                    @elseif($item->latest_lat && $item->latest_lng)
                                        <i class="bx bx-map text-secondary"></i>
                                        <span dir="ltr">{{ number_format($item->latest_lat, 4) }}, {{ number_format($item->latest_lng, 4) }}</span>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </div>
                                <div class="text-muted font-11 mt-1">
                                    <i class="bx bx-radar text-info"></i> {{ number_format($logsCount) }} نقطة GPS
                                </div>
                            </td>

                            <td style="min-width: 110px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 7px;">
                                        <div class="progress-bar {{ $perfClass }}" role="progressbar" style="width: {{ $perf }}%"></div>
                                    </div>
                                    <span class="font-12 fw-bold">{{ number_format($perf, 0) }}%</span>
                                </div>
                            </td>

                            <td>
                                <div class="font-12 text-dark fw-semibold" title="وقت البدء">
                                    <i class="bx bx-play-circle text-success"></i> {{ $item->created_at ? $item->created_at->format('Y-m-d H:i') : '-' }}
                                </div>
                                @if($item->session_ended_at)
                                    <div class="font-11 text-muted" title="وقت الانتهاء">
                                        <i class="bx bx-stop-circle text-secondary"></i> {{ $item->session_ended_at->format('Y-m-d H:i') }}
                                    </div>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1 flex-nowrap">
                                    @if($item->round_status !== 'end')
                                        <a href="{{ route('details.training.session', $item->id) }}"
                                           class="btn btn-success btn-sm px-2" title="فتح خريطة المتابعة المباشرة">
                                            <i class="bx bx-radar"></i> متابعة
                                        </a>
                                        <button type="button"
                                                class="btn btn-outline-warning btn-sm px-2 btn-quick-end"
                                                data-id="{{ $item->id }}"
                                                data-url="{{ route('details.training.session.updateStatus', $item->id) }}"
                                                title="إنهاء الجلسة فوراً">
                                            <i class="bx bx-stop-circle"></i>
                                        </button>
                                    @else
                                        <a href="{{ route('details.training.session', $item->id) }}"
                                           class="btn btn-primary btn-sm px-2" title="عرض التفاصيل وتحليل المسار">
                                            <i class="bx bx-show-alt"></i> تفاصيل
                                        </a>
                                    @endif
                                    <a href="{{ route('delete.training.session', $item->id) }}"
                                       class="btn btn-outline-danger btn-sm px-2" id="delete" title="حذف الجلسة">
                                        <i class="bx bx-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">
                                <i class="bx bx-search-alt font-30 d-block mb-2 opacity-50"></i>
                                لا توجد جلسات تدريب مطابقة لمعايير البحث الحالية.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sessions->hasPages())
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 pt-3 border-top">
                <div class="text-muted font-12">
                    صفحة {{ $sessions->currentPage() }} من {{ $sessions->lastPage() }}
                </div>
                <div>
                    {{ $sessions->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Dynamic Worker dropdown filtering based on selected Owner
    var ownerSelect = document.getElementById('owner_id');
    var workerSelect = document.getElementById('camel_worker_id');
    var allWorkerOptions = [];

    if (workerSelect) {
        Array.from(workerSelect.options).forEach(function (opt) {
            allWorkerOptions.push({
                value: opt.value,
                text: opt.text,
                ownerId: opt.getAttribute('data-owner-id') || ''
            });
        });

        function filterWorkersByOwner(preserveSelection) {
            var selectedOwner = ownerSelect ? ownerSelect.value : '';
            var currentWorker = preserveSelection ? workerSelect.value : '';
            workerSelect.innerHTML = '';

            allWorkerOptions.forEach(function (item) {
                if (item.value === '' || !selectedOwner || item.ownerId === selectedOwner) {
                    var option = document.createElement('option');
                    option.value = item.value;
                    option.text = item.text;
                    if (item.ownerId) option.setAttribute('data-owner-id', item.ownerId);
                    if (item.value === currentWorker) option.selected = true;
                    workerSelect.appendChild(option);
                }
            });

            if (window.jQuery && jQuery(workerSelect).hasClass('select2-hidden-accessible')) {
                jQuery(workerSelect).trigger('change.select2');
            }
        }

        // Initialize Select2 cleanly if available
        if (window.jQuery && typeof jQuery.fn.select2 === 'function') {
            jQuery('.select2-filter').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dir: 'rtl'
            });
            jQuery('#owner_id').on('change', function () {
                filterWorkersByOwner(false);
            });
        } else if (ownerSelect) {
            ownerSelect.addEventListener('change', function () {
                filterWorkersByOwner(false);
            });
        }

        // Apply initial owner filter if owner is pre-selected
        if (ownerSelect && ownerSelect.value) {
            filterWorkersByOwner(true);
        }
    }

    // 2. Quick End Session action with SweetAlert2
    document.querySelectorAll('.btn-quick-end').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var sessionId = this.getAttribute('data-id');
            var url = this.getAttribute('data-url');
            var buttonEl = this;

            var confirmAction = function () {
                buttonEl.disabled = true;
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ status: 'end' })
                })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success) {
                        if (typeof toastr !== 'undefined') {
                            toastr.success('تم إنهاء الجلسة #' + sessionId + ' بنجاح');
                        }
                        var statusCell = document.getElementById('status-cell-' + sessionId);
                        if (statusCell) {
                            statusCell.innerHTML = '<span class="badge bg-secondary"><i class="bx bx-check-circle"></i> منتهية</span>';
                        }
                        var row = document.getElementById('session-row-' + sessionId);
                        if (row) row.classList.remove('table-success', 'bg-opacity-10');
                        buttonEl.remove();
                        refreshLiveOverview();
                    } else {
                        buttonEl.disabled = false;
                        if (typeof toastr !== 'undefined') toastr.error('تعذر إنهاء الجلسة');
                    }
                })
                .catch(function () {
                    buttonEl.disabled = false;
                    if (typeof toastr !== 'undefined') toastr.error('حدث خطأ في الاتصال بالسيرفر');
                });
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'إنهاء الجلسة #' + sessionId + '؟',
                    text: 'سيتم إغلاق الجلسة التدريبية وتحويل حالتها إلى منتهية.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#971048',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'نعم، إنهاء الجلسة',
                    cancelButtonText: 'إلغاء'
                }).then(function (result) {
                    if (result.isConfirmed) confirmAction();
                });
            } else if (confirm('هل أنت متأكد من إنهاء الجلسة #' + sessionId + '؟')) {
                confirmAction();
            }
        });
    });

    // 3. Live Operations Overview Auto-Refresh (every 15 seconds)
    var refreshTimer = null;
    var toggleAuto = document.getElementById('toggle-auto-refresh');
    var btnManual = document.getElementById('btn-manual-refresh');

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderLiveStrip(items) {
        var wrapper = document.getElementById('live-operations-wrapper');
        var grid = document.getElementById('live-strip-grid');
        var countBadge = document.getElementById('live-strip-count');
        if (!wrapper || !grid) return;

        if (!items || items.length === 0) {
            wrapper.classList.add('d-none');
            grid.innerHTML = '';
            if (countBadge) countBadge.textContent = '0';
            return;
        }

        wrapper.classList.remove('d-none');
        if (countBadge) countBadge.textContent = String(items.length);

        var html = '';
        items.forEach(function (act) {
            var cardClass = act.round_status === 'stop' ? 'is-paused' : (act.is_desert_mode ? 'is-desert' : '');
            var statusHtml = '';
            if (act.round_status === 'working') {
                if (act.is_desert_mode) {
                    statusHtml = '<span class="badge bg-warning text-dark"><i class="bx bx-wifi-off"></i> وضع صحراوي</span>';
                } else if (act.is_weak_connection) {
                    statusHtml = '<span class="badge bg-warning text-dark"><i class="bx bx-signal-2"></i> إشارة ضعيفة</span>';
                } else {
                    statusHtml = '<span class="badge bg-success"><i class="bx bx-radio-circle-marked"></i> بث مباشر</span>';
                }
            } else if (act.round_status === 'stop') {
                statusHtml = '<span class="badge bg-danger"><i class="bx bx-pause"></i> متوقفة</span>';
            } else {
                statusHtml = '<span class="badge bg-warning text-dark"><i class="bx bx-time"></i> انتظار</span>';
            }

            html += '<div class="col-12 col-md-6 col-xl-4">' +
                '<div class="live-strip-card p-3 h-100 ' + cardClass + '">' +
                    '<div class="d-flex align-items-start justify-content-between gap-2 mb-2">' +
                        '<div>' +
                            '<div class="fw-bold text-dark font-14">#' + act.id + ' — ' + escapeHtml(act.worker_name) + '</div>' +
                            '<div class="text-muted font-12"><i class="bx bx-user"></i> ' + escapeHtml(act.owner_name) + '</div>' +
                        '</div>' +
                        '<div>' + statusHtml + '</div>' +
                    '</div>' +
                    '<div class="row g-2 text-center my-2 bg-white rounded-3 py-2 border">' +
                        '<div class="col-4 border-start"><div class="font-11 text-muted">السرعة</div><div class="fw-bold text-info font-13">' + parseFloat(act.speed).toFixed(1) + ' <small>كم/س</small></div></div>' +
                        '<div class="col-4 border-start"><div class="font-11 text-muted">المسافة</div><div class="fw-bold text-success font-13">' + parseFloat(act.distance).toFixed(2) + ' <small>كم</small></div></div>' +
                        '<div class="col-4"><div class="font-11 text-muted">المدة</div><div class="fw-bold text-dark font-13">' + escapeHtml(act.round_time) + '</div></div>' +
                    '</div>' +
                    '<div class="d-flex align-items-center justify-content-between mt-2">' +
                        '<span class="text-muted font-11 text-truncate" style="max-width:58%;"><i class="bx bx-map-pin text-danger"></i> ' + escapeHtml(act.location_name) + '</span>' +
                        '<a href="' + escapeHtml(act.details_url) + '" class="btn btn-sm btn-success rounded-pill px-3"><i class="bx bx-radar"></i> متابعة مباشرة</a>' +
                    '</div>' +
                '</div>' +
            '</div>';
        });

        grid.innerHTML = html;
    }

    function refreshLiveOverview() {
        fetch('{{ route('live.training.sessions.overview') }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (res) { return res.ok ? res.json() : null; })
        .then(function (data) {
            if (!data || !data.success) return;
            var elLive = document.getElementById('kpi-live-now');
            var elPaused = document.getElementById('kpi-paused-pending');
            var elToday = document.getElementById('kpi-today-count');
            var elTotal = document.getElementById('kpi-total-count');
            var elUpdated = document.getElementById('live-last-updated');

            if (elLive) elLive.textContent = data.stats.live_now;
            if (elPaused) elPaused.textContent = data.stats.paused_pending;
            if (elToday) elToday.textContent = data.stats.today_count;
            if (elTotal) elTotal.textContent = Number(data.stats.total_count).toLocaleString();
            if (elUpdated) elUpdated.textContent = data.server_time;

            renderLiveStrip(data.active_sessions);
        })
        .catch(function () {});
    }

    function startAutoRefresh() {
        if (refreshTimer) clearInterval(refreshTimer);
        refreshTimer = setInterval(function () {
            if (!document.hidden) {
                refreshLiveOverview();
            }
        }, 15000);
    }

    if (toggleAuto) {
        if (toggleAuto.checked) startAutoRefresh();
        toggleAuto.addEventListener('change', function () {
            if (this.checked) {
                refreshLiveOverview();
                startAutoRefresh();
            } else if (refreshTimer) {
                clearInterval(refreshTimer);
                refreshTimer = null;
            }
        });
    }

    if (btnManual) {
        btnManual.addEventListener('click', function () {
            refreshLiveOverview();
            if (typeof toastr !== 'undefined') toastr.info('تم تحديث بيانات الجلسات المباشرة');
        });
    }
});
</script>

@endsection
