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
                    <li class="breadcrumb-item active" aria-current="page">اشتراكات الملاك</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a href="{{ route('add.user.subscription') }}" class="btn btn-primary px-4 d-flex align-items-center gap-1">
                <i class="bx bx-plus-circle font-18"></i> تسجيل اشتراك جديد
            </a>
        </div>
    </div>
    <!--end breadcrumb-->

    <hr/>

    <!-- Quick Stats Row -->
    <div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
        <div class="col">
            <div class="card radius-10 border-start border-0 border-4 border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">الاشتراكات النشطة</p>
                            <h4 class="my-1 text-success">{{ $subscriptions->where('status', 'active')->count() }}</h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto">
                            <i class="bx bx-check-shield"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 border-start border-0 border-4 border-danger">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">الاشتراكات المنتهية/الملغاة</p>
                            <h4 class="my-1 text-danger">{{ $subscriptions->whereIn('status', ['expired', 'canceled'])->count() }}</h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-gradient-bloody text-white ms-auto">
                            <i class="bx bx-x-circle"></i>
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
                            <p class="mb-0 text-secondary">إجمالي الإيرادات المحصلة</p>
                            <h4 class="my-1 text-info">{{ number_format($subscriptions->sum('amount_paid'), 2) }} ر.س</h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-gradient-scooter text-white ms-auto">
                            <i class="bx bx-money"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('all.user.subscriptions') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="owner_id" class="form-label font-13 fw-bold">تصفية حسب المالك:</label>
                    <select name="owner_id" id="owner_id" class="form-select">
                        <option value="">-- كل الملاك --</option>
                        @foreach($owners as $owner)
                            <option value="{{ $owner->id }}" {{ request('owner_id') == $owner->id ? 'selected' : '' }}>
                                {{ $owner->fname }} {{ $owner->lname }} ({{ $owner->phone }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="subscription_plan_id" class="form-label font-13 fw-bold">تصفية حسب الباقة:</label>
                    <select name="subscription_plan_id" id="subscription_plan_id" class="form-select">
                        <option value="">-- كل الباقات --</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ request('subscription_plan_id') == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} ({{ number_format($plan->price, 2) }} ر.س)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label font-13 fw-bold">تصفية حسب الحالة:</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">-- كل الحالات --</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                        <option value="pending_payment" {{ request('status') == 'pending_payment' ? 'selected' : '' }}>بانتظار الدفع</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>منتهٍ</option>
                        <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>ملغى</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-3 w-100"><i class="bx bx-filter-alt"></i> تصفية</button>
                    <a href="{{ route('all.user.subscriptions') }}" class="btn btn-secondary px-3 w-100"><i class="bx bx-refresh"></i> إعادة ضبط</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Subscriptions Table Card -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-bold mb-4"><i class="bx bx-check-shield text-success"></i> سجل اشتراكات الملاك المعتمدة</h5>
            
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th>الرقم</th>
                            <th>المالك</th>
                            <th>الباقة المشترك بها</th>
                            <th>تاريخ البدء</th>
                            <th>تاريخ الانتهاء</th>
                            <th>القيمة المدفوعة</th>
                            <th>حالة الاشتراك</th>
                            <th>رقم العملية (Transaction)</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscriptions as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-dark">
                                        @if($item->owner)
                                            {{ $item->owner->fname }} {{ $item->owner->lname }}
                                        @else
                                            <span class="text-danger">مالك محذوف</span>
                                        @endif
                                    </div>
                                    @if($item->owner)
                                        <small class="text-muted"><i class="bx bx-phone"></i> {{ $item->owner->phone }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light-primary text-primary font-13 px-3 py-1.5 fw-bold">
                                        @if($item->plan)
                                            {{ $item->plan->name }}
                                        @else
                                            <span class="text-danger">باقة محذوفة</span>
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <small><i class="bx bx-calendar"></i> {{ $item->start_date->format('Y-m-d') }}</small>
                                </td>
                                <td>
                                    @if($item->end_date)
                                        @php
                                            $isExpired = $item->status === 'expired' || $item->end_date->isPast();
                                            $daysRemaining = (int)now()->diffInDays($item->end_date, false);
                                            $isNearExpiry = !$isExpired && $daysRemaining <= 7 && $daysRemaining >= 0;
                                        @endphp
                                        
                                        @if($isExpired)
                                            <span class="badge bg-light-danger text-danger font-12 px-2.5 py-1.5" title="الاشتراك منتهي الصلاحية">
                                                <i class="bx bx-calendar-x"></i> {{ $item->end_date->format('Y-m-d') }} (منتهٍ)
                                            </span>
                                            @if($item->status === 'canceled')
                                                <div class="text-secondary font-11 mt-1"><i class="bx bx-time-five"></i> ملغى بالكامل</div>
                                            @else
                                                <div class="text-danger font-11 mt-1"><i class="bx bx-time-five"></i> منتهٍ منذ {{ abs($daysRemaining) }} يوم</div>
                                            @endif
                                        @elseif($isNearExpiry)
                                            <span class="badge bg-light-warning text-dark font-12 px-2.5 py-1.5" title="ينتهي قريباً (أقل من 7 أيام)">
                                                <i class="bx bx-error"></i> {{ $item->end_date->format('Y-m-d') }} (قريباً)
                                            </span>
                                            <div class="text-warning font-11 mt-1 fw-bold"><i class="bx bx-time-five"></i> متبقي {{ $daysRemaining }} يوم فقط!</div>
                                        @else
                                            <span class="badge bg-light-success text-success font-12 px-2.5 py-1.5" title="الاشتراك ساري وصالح">
                                                <i class="bx bx-calendar-check"></i> {{ $item->end_date->format('Y-m-d') }}
                                            </span>
                                            <div class="text-success font-11 mt-1"><i class="bx bx-time-five"></i> متبقي {{ $daysRemaining }} يوم</div>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <strong class="text-dark font-14">{{ number_format($item->amount_paid, 2) }} ر.س</strong>
                                </td>
                                <td>
                                    @if($item->status == 'active')
                                        @if($item->end_date && $item->end_date->isPast())
                                            <span class="badge bg-danger">منتهى تلقائياً</span>
                                        @else
                                            <span class="badge bg-success">نشط وساري</span>
                                        @endif
                                    @elseif($item->status == 'expired')
                                        <span class="badge bg-danger">منتهى</span>
                                    @elseif($item->status == 'canceled')
                                        <span class="badge bg-secondary">ملغى</span>
                                    @elseif($item->status == 'pending_payment')
                                        <span class="badge bg-warning text-dark">بانتظار الدفع</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->transaction_id)
                                        <code class="text-dark font-12 bg-light px-2 py-1 rounded">{{ $item->transaction_id }}</code>
                                    @else
                                        <span class="text-muted font-12">تسجيل يدوي</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($item->status == 'active' && (!$item->end_date || $item->end_date->isFuture()))
                                            <a href="{{ route('cancel.user.subscription', $item->id) }}" class="btn btn-warning btn-sm px-2.5 d-flex align-items-center gap-1 text-dark" title="إلغاء الاشتراك الفعلي">
                                                <i class="bx bx-block"></i> إلغاء
                                            </a>
                                        @endif
                                        <a href="{{ route('delete.user.subscription', $item->id) }}" class="btn btn-danger btn-sm px-2.5 d-flex align-items-center gap-1" id="delete">
                                            <i class="bx bx-trash"></i> حذف
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">لا يوجد سجلات اشتراكات مطابقة للتصفية الحالية.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
