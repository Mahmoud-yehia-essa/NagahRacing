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
                    <li class="breadcrumb-item active" aria-current="page">باقات الاشتراك</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a href="{{ route('add.subscription.plan') }}" class="btn btn-primary px-4 d-flex align-items-center gap-1">
                <i class="bx bx-plus-circle font-18"></i> إضافة باقة جديدة
            </a>
        </div>
    </div>
    <!--end breadcrumb-->

    <hr/>

    <!-- Quick Stats Row -->
    <div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
        <div class="col">
            <div class="card radius-10 border-start border-0 border-4 border-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">إجمالي الباقات</p>
                            <h4 class="my-1 text-info">{{ $plans->count() }}</h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-gradient-scooter text-white ms-auto">
                            <i class="bx bx-list-ul"></i>
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
                            <p class="mb-0 text-secondary">الباقات النشطة</p>
                            <h4 class="my-1 text-success">{{ $plans->where('status', 'active')->count() }}</h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto">
                            <i class="bx bx-check-double"></i>
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
                            <p class="mb-0 text-secondary">الباقات التجريبية</p>
                            <h4 class="my-1 text-warning">{{ $plans->where('is_trial', true)->count() }}</h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-gradient-orange text-white ms-auto">
                            <i class="bx bx-gift"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Plans Table Card -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-bold mb-4"><i class="bx bx-credit-card-front text-primary"></i> باقات وخطط الاشتراك المتوفرة</h5>
            
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th>الرقم</th>
                            <th>اسم الباقة</th>
                            <th>السعر</th>
                            <th>المدة والصلاحية</th>
                            <th>نوع الباقة</th>
                            <th>حالة النشاط</th>
                            <th>المشتركون الحاليون</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plans as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->name }}</div>
                                    @if($item->description)
                                        <small class="text-muted text-wrap d-block mt-1" style="max-width: 250px;">{{ Str::limit($item->description, 60) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($item->price == 0.00)
                                        <span class="badge bg-success font-13 px-3 py-2">مجانية</span>
                                    @else
                                        <strong class="text-primary font-15">{{ number_format($item->price, 2) }} ر.س</strong>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light-info text-info font-13 px-3 py-2 fw-bold">
                                        {{ $item->plan_duration }} 
                                        @if($item->plan_interval == 'day')
                                            يوم
                                        @elseif($item->plan_interval == 'month')
                                            شهر
                                        @elseif($item->plan_interval == 'year')
                                            سنة
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    @if($item->is_trial)
                                        <span class="badge bg-warning text-dark font-12"><i class="bx bx-gift"></i> تجريبية</span>
                                    @else
                                        <span class="badge bg-secondary font-12">أساسية</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->status == 'active')
                                        <a href="{{ route('toggle.subscription.plan', $item->id) }}" class="badge bg-success font-12 text-decoration-none px-3 py-2 cursor-pointer" title="اضغط لتعطيل الباقة">
                                            <i class="bx bx-show-alt me-1"></i> نشطة
                                        </a>
                                    @else
                                        <a href="{{ route('toggle.subscription.plan', $item->id) }}" class="badge bg-danger font-12 text-decoration-none px-3 py-2 cursor-pointer" title="اضغط لتفعيل الباقة">
                                            <i class="bx bx-hide me-1"></i> غير نشطة
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-dark font-13 px-2.5 py-1.5 fw-bold">{{ $item->subscriptions_count }} مالك</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="{{ route('edit.subscription.plan', $item->id) }}" class="btn btn-info btn-sm px-3 d-flex align-items-center gap-1 text-white">
                                            <i class="bx bx-edit-alt"></i> تعديل
                                        </a>
                                        <a href="{{ route('delete.subscription.plan', $item->id) }}" class="btn btn-danger btn-sm px-3 d-flex align-items-center gap-1" id="delete">
                                            <i class="bx bx-trash"></i> حذف
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">لا يوجد باقات اشتراك مضافة حتى الآن.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
