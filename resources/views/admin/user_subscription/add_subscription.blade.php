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
                    <li class="breadcrumb-item"><a href="{{ route('all.user.subscriptions') }}">اشتراكات الملاك</a></li>
                    <li class="breadcrumb-item active" aria-current="page">تسجيل اشتراك جديد</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <hr/>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card border-top border-0 border-4 border-success shadow-sm">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold mb-4"><i class="bx bx-plus-circle text-success"></i> تسجيل اشتراك جديد لمالك</h5>
                    
                    <form action="{{ route('store.user.subscription') }}" method="POST" class="row g-3">
                        @csrf
                        
                        <!-- Owner Selector -->
                        <div class="col-12">
                            <label for="owner_id" class="form-label fw-bold">اختر المالك <span class="text-danger">*</span></label>
                            <select name="owner_id" id="owner_id" class="form-select @error('owner_id') is-invalid @enderror">
                                <option value="">-- اختر المالك المشترك --</option>
                                @foreach($owners as $owner)
                                    <option value="{{ $owner->id }}" {{ old('owner_id') == $owner->id ? 'selected' : '' }}>
                                        {{ $owner->fname }} {{ $owner->lname }} ({{ $owner->phone }})
                                    </option>
                                @endforeach
                            </select>
                            @error('owner_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Subscription Plan Selector -->
                        <div class="col-12">
                            <label for="subscription_plan_id" class="form-label fw-bold">باقة الاشتراك المحددة <span class="text-danger">*</span></label>
                            <select name="subscription_plan_id" id="subscription_plan_id" class="form-select @error('subscription_plan_id') is-invalid @enderror" onchange="updatePlanPrice()">
                                <option value="">-- اختر الباقة --</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" data-price="{{ $plan->price }}" data-duration="{{ $plan->plan_duration }}" data-interval="{{ $plan->plan_interval }}" {{ old('subscription_plan_id') == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }} (السعر: {{ number_format($plan->price, 2) }} ر.س - المدة: {{ $plan->plan_duration }} {{ $plan->plan_interval == 'day' ? 'يوم' : ($plan->plan_interval == 'month' ? 'شهر' : 'سنة') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('subscription_plan_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Start Date -->
                        <div class="col-md-6">
                            <label for="start_date" class="form-label fw-bold">تاريخ بداية الاشتراك <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" value="{{ old('start_date', date('Y-m-d')) }}">
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Amount Paid -->
                        <div class="col-md-6">
                            <label for="amount_paid" class="form-label fw-bold">المبلغ الفعلي المدفوع (ر.س) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-wallet"></i></span>
                                <input type="number" step="0.01" name="amount_paid" class="form-control @error('amount_paid') is-invalid @enderror" id="amount_paid" placeholder="0.00" value="{{ old('amount_paid') }}">
                                @error('amount_paid')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Transaction ID -->
                        <div class="col-12">
                            <label for="transaction_id" class="form-label fw-bold">رقم مرجع العملية / رقم الحوالة (Transaction ID)</label>
                            <input type="text" name="transaction_id" class="form-control" id="transaction_id" placeholder="مثال: TXN9876543210 (اختياري)" value="{{ old('transaction_id') }}">
                            <small class="text-muted">أدخل الرقم المرجعي للدفع لحفظ وتتبع سجلات التسوية المالية.</small>
                        </div>

                        <!-- Status Selector -->
                        <div class="col-12">
                            <label for="status" class="form-label fw-bold">حالة الاشتراك المبدئية <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>نشط وساري</option>
                                <option value="pending_payment" {{ old('status') == 'pending_payment' ? 'selected' : '' }}>بانتظار الدفع</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">ملاحظة: تفعيل الاشتراك إلى "نشط" سيقوم تلقائياً بتعطيل أي اشتراك نشط سابق لنفس المالك تجنباً للتداخل.</small>
                        </div>

                        <!-- Form Actions -->
                        <div class="col-12 mt-4">
                            <hr>
                            <div class="d-flex align-items-center gap-3">
                                <button type="submit" class="btn btn-success px-4"><i class="bx bx-save"></i> تسجيل وتفعيل الاشتراك</button>
                                <a href="{{ route('all.user.subscriptions') }}" class="btn btn-secondary px-4">إلغاء</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    /**
     * Update the price input field dynamically when selecting a plan
     */
    function updatePlanPrice() {
        var selector = document.getElementById('subscription_plan_id');
        var selectedOption = selector.options[selector.selectedIndex];
        if (selectedOption && selectedOption.value !== "") {
            var price = selectedOption.getAttribute('data-price');
            document.getElementById('amount_paid').value = parseFloat(price).toFixed(2);
        } else {
            document.getElementById('amount_paid').value = '';
        }
    }
</script>

@endsection
