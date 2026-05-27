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
                    <li class="breadcrumb-item active" aria-current="page">تعديل باقة: {{ $plan->name }}</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <hr/>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card border-top border-0 border-4 border-info shadow-sm">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold mb-4"><i class="bx bx-edit text-info"></i> تعديل باقة الاشتراك</h5>
                    
                    <form action="{{ route('update.subscription.plan') }}" method="POST" class="row g-3">
                        @csrf
                        <input type="hidden" name="id" value="{{ $plan->id }}">
                        
                        <!-- Plan Name -->
                        <div class="col-12">
                            <label for="name" class="form-label fw-bold">اسم الباقة <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="مثال: الباقة السنوية" value="{{ old('name', $plan->name) }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Price -->
                        <div class="col-12">
                            <label for="price" class="form-label fw-bold">سعر الباقة (ر.س) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-dollar-circle"></i></span>
                                <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror" id="price" placeholder="0.00 للمجانية" value="{{ old('price', $plan->price) }}">
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">أدخل السعر بالعملة المحلية، القيمة 0 تعني باقة مجانية.</small>
                        </div>

                        <!-- Duration and Interval -->
                        <div class="col-md-6">
                            <label for="plan_duration" class="form-label fw-bold">مدة الصلاحية <span class="text-danger">*</span></label>
                            <input type="number" name="plan_duration" class="form-control @error('plan_duration') is-invalid @enderror" id="plan_duration" placeholder="مثال: 1, 3, 6, 12" value="{{ old('plan_duration', $plan->plan_duration) }}">
                            @error('plan_duration')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="plan_interval" class="form-label fw-bold">الوحدة الزمنية <span class="text-danger">*</span></label>
                            <select name="plan_interval" id="plan_interval" class="form-select @error('plan_interval') is-invalid @enderror">
                                <option value="day" {{ old('plan_interval', $plan->plan_interval) == 'day' ? 'selected' : '' }}>يوم</option>
                                <option value="month" {{ old('plan_interval', $plan->plan_interval) == 'month' ? 'selected' : '' }}>شهر</option>
                                <option value="year" {{ old('plan_interval', $plan->plan_interval) == 'year' ? 'selected' : '' }}>سنة</option>
                            </select>
                            @error('plan_interval')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label for="description" class="form-label fw-bold">الوصف والمميزات</label>
                            <textarea name="description" class="form-control" id="description" rows="4" placeholder="ادخل شرح للباقة ومميزاتها فقط">{{ old('description', $plan->description) }}</textarea>
                        </div>

                        <!-- Switches -->
                        <div class="col-md-6 mt-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_trial" id="is_trial" value="1" {{ old('is_trial', $plan->is_trial) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_trial">باقة تجريبية مجانية (Trial)</label>
                            </div>
                        </div>

                        <div class="col-md-6 mt-4">
                            <label class="form-label fw-bold d-block">حالة النشاط</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="status_active" value="active" {{ old('status', $plan->status) == 'active' ? 'checked' : '' }}>
                                <label class="form-check-label" for="status_active">نشطة</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="status_inactive" value="inactive" {{ old('status', $plan->status) == 'inactive' ? 'checked' : '' }}>
                                <label class="form-check-label" for="status_inactive">معطلة</label>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="col-12 mt-4">
                            <hr>
                            <div class="d-flex align-items-center gap-3">
                                <button type="submit" class="btn btn-info px-4 text-white"><i class="bx bx-save"></i> حفظ التغييرات</button>
                                <a href="{{ route('all.subscription.plans') }}" class="btn btn-secondary px-4">إلغاء</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
