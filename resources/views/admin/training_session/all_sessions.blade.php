@extends('admin.master_admin')
@section('admin')

<div class="page-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">إدارة الجلسات</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">كل جلسات التدريب</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <hr/>

    <!-- Filter Card -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('all.training.sessions') }}">
                <div class="row align-items-end g-3">
                    <!-- Filter by Owner -->
                    <div class="col-md-3">
                        <label for="owner_id" class="form-label">تصفية حسب المالك:</label>
                        <select name="owner_id" id="owner_id" class="form-select">
                            <option value="">-- كل الملاك --</option>
                            @foreach($owners as $owner)
                                <option value="{{ $owner->id }}" {{ request('owner_id') == $owner->id ? 'selected' : '' }}>
                                    {{ $owner->fname }} {{ $owner->lname }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter by Worker -->
                    <div class="col-md-3">
                        <label for="camel_worker_id" class="form-label">تصفية حسب العامل:</label>
                        <select name="camel_worker_id" id="camel_worker_id" class="form-select">
                            <option value="">-- كل العمال --</option>
                            @foreach($workersList as $worker)
                                <option value="{{ $worker->id }}" {{ request('camel_worker_id') == $worker->id ? 'selected' : '' }}>
                                    {{ $worker->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter by Status -->
                    <div class="col-md-3">
                        <label for="status" class="form-label">تصفية حسب الحالة:</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">-- كل الحالات --</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشطة (تعمل حالياً / متوقفة مؤقتاً)</option>
                            <option value="ended" {{ request('status') == 'ended' ? 'selected' : '' }}>منتهية</option>
                        </select>
                    </div>

                    <!-- Filter Buttons -->
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt"></i> تصفية</button>
                        <a href="{{ route('all.training.sessions') }}" class="btn btn-secondary w-100"><i class="bx bx-refresh"></i> إعادة ضبط</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sessions Table Card -->
    <div class="card mt-4">
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>الرقم</th>
                            <th>المالك</th>
                            <th>العامل / المدرب</th>
                            <th>الموقع الحالي</th>
                            <th>حالة الجلسة</th>
                            <th>السرعة الحالية</th>
                            <th>متوسط السرعة</th>
                            <th>المسافة</th>
                            <th>المدة</th>
                            <th>الأداء</th>
                            <th>تاريخ البدء</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    @if($item->worker && $item->worker->owner)
                                        <span class="fw-bold">{{ $item->worker->owner->fname }} {{ $item->worker->owner->lname }}</span>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->worker)
                                        <span class="text-primary fw-bold">{{ $item->worker->full_name }}</span>
                                    @else
                                        <span class="text-muted">غير معروف</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->location_name)
                                        <i class="bx bx-map text-danger"></i> {{ Str::limit($item->location_name, 20) }}
                                    @elseif($item->latitude && $item->longitude)
                                        <i class="bx bx-map text-secondary"></i> ({{ $item->latitude }}, {{ $item->longitude }})
                                    @else
                                        <span class="text-muted">لا يوجد موقع</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->round_status == 'pending')
                                        <span class="badge bg-warning text-dark"><i class="bx bx-time"></i> قيد الانتظار</span>
                                    @elseif($item->round_status == 'working')
                                        <span class="badge bg-success"><i class="bx bx-run"></i> قيد التدريب</span>
                                    @elseif($item->round_status == 'stop')
                                        <span class="badge bg-danger"><i class="bx bx-pause"></i> متوقفة مؤقتاً</span>
                                    @elseif($item->round_status == 'end')
                                        <span class="badge bg-secondary"><i class="bx bx-check-circle"></i> منتهية</span>
                                    @else
                                        <span class="badge bg-dark">{{ $item->round_status }}</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-info text-dark font-13 fw-bold">{{ number_format($item->speed, 2) }} كم/س</span></td>
                                <td><span class="badge bg-light text-dark font-13">{{ number_format($item->average_speed, 2) }} كم/س</span></td>
                                <td><span class="badge bg-light text-dark font-13">{{ number_format($item->round_distance_km, 2) }} كم</span></td>
                                <td>
                                    @if($item->round_time)
                                        <span class="text-dark"><i class="bx bx-timer text-primary"></i> {{ $item->round_time }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <div class="progress w-100" style="height: 6px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ min(100, max(0, $item->performance)) }}%"></div>
                                        </div>
                                        <span class="font-12 fw-bold">{{ number_format($item->performance, 0) }}%</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted font-12">{{ $item->created_at->format('Y-m-d H:i') }}</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('details.training.session', $item->id) }}" class="btn btn-info btn-sm" title="عرض التفاصيل والمسار">
                                            <i class="bx bx-show-alt"></i> تفاصيل
                                        </a>
                                        <a href="{{ route('delete.training.session', $item->id) }}" class="btn btn-danger btn-sm" id="delete" title="حذف الجلسة">
                                            <i class="bx bx-trash"></i> حذف
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted">لا يوجد جلسات تدريب مطابقة لمعايير البحث حالياً.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
