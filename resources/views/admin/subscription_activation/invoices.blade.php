@extends('admin.master_admin')
@section('admin')

<div class="page-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">الباقات والاشتراكات</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('all.subscription.plans') }}">باقات الاشتراك</a></li>
                    <li class="breadcrumb-item active" aria-current="page">سجل الفواتير والمدفوعات</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto d-flex gap-2">
            <button type="button" class="btn btn-success px-4 d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#directActivateModal">
                <i class="bx bx-bolt-circle font-18"></i> تفعيل / شحن مباشر لمالك
            </button>
            <a href="{{ route('admin.subscription.codes') }}" class="btn btn-outline-primary px-3">
                <i class="bx bx-key"></i> أكواد التفعيل
            </a>
        </div>
    </div>
    <!--end breadcrumb-->

    <hr/>

    <!-- Stats Row -->
    <div class="row row-cols-1 row-cols-md-4 g-3 mb-4">
        <div class="col">
            <div class="card radius-10 border-start border-0 border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">إجمالي الفواتير</p>
                            <h4 class="my-1 text-primary">{{ $stats['total_count'] }}</h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto">
                            <i class="bx bx-receipt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 border-start border-0 border-4 border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">الفواتير المؤكدة</p>
                            <h4 class="my-1 text-success">{{ $stats['confirmed_count'] }}</h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto">
                            <i class="bx bx-check-double"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 border-start border-0 border-4 border-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">إجمالي التحصيل (المطابق للتصفية)</p>
                            <h4 class="my-1 text-info">{{ number_format($stats['total_revenue'], 2) }} {{ $settings->currency_label }}</h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-gradient-scooter text-white ms-auto">
                            <i class="bx bx-money"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 border-start border-0 border-4 border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">تحصيل هذا الشهر</p>
                            <h4 class="my-1 text-warning">{{ number_format($stats['this_month_revenue'], 2) }} {{ $settings->currency_label }}</h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-gradient-orange text-white ms-auto">
                            <i class="bx bx-trending-up"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.subscription.invoices') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label font-13 fw-bold">رقم الفاتورة / الكود:</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="INV- / NGH-">
                </div>
                <div class="col-md-3">
                    <label class="form-label font-13 fw-bold">المالك:</label>
                    <select name="owner_id" class="form-select">
                        <option value="">-- كل الملاك --</option>
                        @foreach($owners as $owner)
                            <option value="{{ $owner->id }}" {{ request('owner_id') == $owner->id ? 'selected' : '' }}>
                                {{ $owner->fname }} {{ $owner->lname }} ({{ $owner->phone }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label font-13 fw-bold">نوع العملية:</label>
                    <select name="action_type" class="form-select">
                        <option value="">-- الكل --</option>
                        @foreach($actionLabels as $key => $label)
                            <option value="{{ $key }}" {{ request('action_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label font-13 fw-bold">من تاريخ:</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label font-13 fw-bold">إلى تاريخ:</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary w-100" title="تصفية"><i class="bx bx-filter-alt"></i></button>
                    <a href="{{ route('admin.subscription.invoices') }}" class="btn btn-secondary" title="إعادة ضبط"><i class="bx bx-refresh"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-bold mb-4"><i class="bx bx-receipt text-success"></i> سجل فواتير وتأكيدات الاشتراكات</h5>
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>المالك</th>
                            <th>البيان والباقة</th>
                            <th>ما تم إضافته</th>
                            <th>المبلغ ووسيلة الدفع</th>
                            <th>الكود / المرجع</th>
                            <th>التاريخ والوقت</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                            <tr>
                                <td>
                                    <code class="fw-bold text-primary bg-light px-2 py-1 rounded">{{ $inv->invoice_number }}</code>
                                    <div class="small text-muted mt-1">{{ $inv->action_label }}</div>
                                </td>
                                <td>
                                    @if($inv->owner)
                                        <div class="fw-bold">{{ $inv->owner->fname }} {{ $inv->owner->lname }}</div>
                                        <small class="text-muted">#{{ $inv->owner->id }} | {{ $inv->owner->phone }}</small>
                                    @else
                                        <span class="text-danger">مالك محذوف</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $inv->title }}</div>
                                    @if($inv->plan_name)
                                        <small class="text-muted">الباقة: {{ $inv->plan_name }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @if($inv->days_added > 0)
                                            <span class="badge bg-light-info text-info">+{{ $inv->days_added }} يوم</span>
                                        @endif
                                        @if($inv->training_sessions_added > 0)
                                            <span class="badge bg-light-success text-success">+{{ $inv->training_sessions_added }} جلسة</span>
                                        @endif
                                        @if($inv->sub_users_added > 0)
                                            <span class="badge bg-light-warning text-dark">+{{ $inv->sub_users_added }} مستخدم</span>
                                        @endif
                                        @if($inv->days_added == 0 && $inv->training_sessions_added == 0 && $inv->sub_users_added == 0)
                                            <span class="badge bg-light-secondary text-secondary">صلاحية مفتوحة</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <strong class="text-dark font-14">{{ number_format($inv->amount_paid, 2) }} {{ $inv->currency }}</strong>
                                    <div class="small text-muted">{{ $inv->payment_method ?: 'تفعيل من الإدارة' }}</div>
                                </td>
                                <td>
                                    @if($inv->activation_code_used)
                                        <span class="badge bg-dark font-12">{{ $inv->activation_code_used }}</span>
                                    @endif
                                    @if($inv->payment_reference)
                                        <div class="small text-muted mt-1">{{ $inv->payment_reference }}</div>
                                    @endif
                                </td>
                                <td>
                                    <small class="fw-bold">{{ ($inv->issued_at ?? $inv->created_at)->format('Y-m-d') }}</small>
                                    <small class="d-block text-muted">{{ ($inv->issued_at ?? $inv->created_at)->format('H:i') }}</small>
                                </td>
                                <td>
                                    @if($inv->status === 'confirmed')
                                        <span class="badge bg-success">مؤكدة</span>
                                    @elseif($inv->status === 'refunded')
                                        <span class="badge bg-warning text-dark">مستردة</span>
                                    @else
                                        <span class="badge bg-danger">ملغاة</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.subscription.invoices.show', $inv->id) }}"
                                       class="btn btn-primary btn-sm px-3 d-inline-flex align-items-center gap-1">
                                        <i class="bx bx-printer"></i> عرض / طباعة
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">لا توجد فواتير مسجلة مطابقة للبحث.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('admin.subscription_activation._direct_activate_modal')

@endsection
