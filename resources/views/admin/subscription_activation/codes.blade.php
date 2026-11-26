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
                    <li class="breadcrumb-item active" aria-current="page">أكواد التفعيل</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto d-flex gap-2">
            <button type="button" class="btn btn-primary px-4 d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#generateCodeModal">
                <i class="bx bx-plus-circle font-18"></i> توليد كود تفعيل جديد
            </button>
            <a href="{{ route('admin.subscription.invoices') }}" class="btn btn-outline-success px-3">
                <i class="bx bx-receipt"></i> سجل الفواتير
            </a>
            <a href="{{ route('admin.subscription.settings') }}" class="btn btn-outline-secondary px-3">
                <i class="bx bx-cog"></i> الإعدادات
            </a>
        </div>
    </div>
    <!--end breadcrumb-->

    <hr/>

    @if(session('last_generated_code'))
        <div class="alert alert-success border-0 bg-light-success d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
            <div>
                <i class="bx bx-check-circle font-20 me-1"></i>
                <strong>تم توليد الكود بنجاح:</strong>
                <code class="fs-5 fw-bold text-dark bg-white px-3 py-1 rounded ms-2">{{ session('last_generated_code') }}</code>
            </div>
            <button type="button" class="btn btn-sm btn-success" onclick="copyActivationCode('{{ session('last_generated_code') }}')">
                <i class="bx bx-copy"></i> نسخ الكود
            </button>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row row-cols-1 row-cols-md-4 g-3 mb-4">
        <div class="col">
            <div class="card radius-10 border-start border-0 border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">إجمالي الأكواد</p>
                            <h4 class="my-1 text-primary">{{ $stats['total'] }}</h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto">
                            <i class="bx bx-key"></i>
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
                            <p class="mb-0 text-secondary">أكواد جاهزة للاستخدام</p>
                            <h4 class="my-1 text-success">{{ $stats['active'] }}</h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto">
                            <i class="bx bx-check-circle"></i>
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
                            <p class="mb-0 text-secondary">أكواد تم تفعيلها</p>
                            <h4 class="my-1 text-info">{{ $stats['used'] }}</h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-gradient-scooter text-white ms-auto">
                            <i class="bx bx-badge-check"></i>
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
                            <p class="mb-0 text-secondary">إيرادات الأكواد المفعّلة</p>
                            <h4 class="my-1 text-warning">{{ number_format($stats['revenue'], 2) }} {{ $settings->currency_label }}</h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-gradient-orange text-white ms-auto">
                            <i class="bx bx-wallet"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.subscription.codes') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label font-13 fw-bold">بحث بالكود أو المرجع:</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="NGH-XXXX-XXXX">
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
                    <label class="form-label font-13 fw-bold">الحالة:</label>
                    <select name="status" class="form-select">
                        <option value="">-- الكل --</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>جاهز للاستخدام</option>
                        <option value="used" {{ request('status') == 'used' ? 'selected' : '' }}>مستخدم</option>
                        <option value="revoked" {{ request('status') == 'revoked' ? 'selected' : '' }}>ملغى</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>منتهي الصلاحية</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label font-13 fw-bold">نوع الكود:</label>
                    <select name="code_type" class="form-select">
                        <option value="">-- الكل --</option>
                        <option value="plan" {{ request('code_type') == 'plan' ? 'selected' : '' }}>تفعيل باقة</option>
                        <option value="custom_addon" {{ request('code_type') == 'custom_addon' ? 'selected' : '' }}>إضافات مخصصة</option>
                        <option value="plan_plus_addon" {{ request('code_type') == 'plan_plus_addon' ? 'selected' : '' }}>باقة + إضافات</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt"></i> تصفية</button>
                    <a href="{{ route('admin.subscription.codes') }}" class="btn btn-secondary"><i class="bx bx-refresh"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Codes Table -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-bold mb-4"><i class="bx bx-key text-primary"></i> سجل أكواد التفعيل المُصدرة</h5>
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>كود التفعيل</th>
                            <th>محتوى الكود</th>
                            <th>مخصص لـ</th>
                            <th>الدفع والمبلغ</th>
                            <th>الحالة والاستخدام</th>
                            <th>الصلاحية / الإنشاء</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($codes as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <code class="fw-bold text-dark bg-light px-2 py-1 rounded font-14">{{ $item->code }}</code>
                                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1.5" title="نسخ الكود"
                                                onclick="copyActivationCode('{{ $item->code }}')">
                                            <i class="bx bx-copy"></i>
                                        </button>
                                    </div>
                                    @if($item->notes)
                                        <small class="text-muted d-block mt-1">{{ Str::limit($item->notes, 45) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($item->plan)
                                        <span class="badge bg-light-primary text-primary px-2 py-1 mb-1 d-inline-block">
                                            <i class="bx bx-crown"></i> {{ $item->plan->name }}
                                        </span>
                                    @endif
                                    <div class="d-flex flex-wrap gap-1">
                                        @if($item->extra_days > 0)
                                            <span class="badge bg-light-info text-info">+{{ $item->extra_days }} يوم</span>
                                        @endif
                                        @if($item->extra_training_sessions > 0)
                                            <span class="badge bg-light-success text-success">+{{ $item->extra_training_sessions }} جلسة</span>
                                        @endif
                                        @if($item->extra_sub_users > 0)
                                            <span class="badge bg-light-warning text-dark">+{{ $item->extra_sub_users }} مستخدم فرعي</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($item->assignedOwner)
                                        <div class="fw-bold">{{ $item->assignedOwner->fname }} {{ $item->assignedOwner->lname }}</div>
                                        <small class="text-muted">{{ $item->assignedOwner->phone }}</small>
                                    @else
                                        <span class="badge bg-secondary">عام (لأي مالك مرة واحدة)</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ number_format($item->amount_paid, 2) }} {{ $settings->currency_label }}</div>
                                    <small class="text-muted d-block">{{ \App\Models\SubscriptionActivationCode::paymentMethodLabel($item->payment_method) }}</small>
                                    @if($item->payment_reference)
                                        <small class="text-primary">مرجع: {{ $item->payment_reference }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($item->status === 'active')
                                        <span class="badge bg-success">جاهز للاستخدام</span>
                                    @elseif($item->status === 'used')
                                        <span class="badge bg-info">مستخدم</span>
                                        @if($item->usedByOwner)
                                            <div class="small fw-bold mt-1">{{ $item->usedByOwner->fname }} {{ $item->usedByOwner->lname }}</div>
                                        @endif
                                        @if($item->used_at)
                                            <small class="text-muted">{{ $item->used_at->format('Y-m-d H:i') }}</small>
                                        @endif
                                    @elseif($item->status === 'revoked')
                                        <span class="badge bg-danger">ملغى</span>
                                    @else
                                        <span class="badge bg-secondary">منتهي</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="d-block text-muted">إنشاء: {{ $item->created_at->format('Y-m-d H:i') }}</small>
                                    @if($item->expires_at)
                                        <small class="d-block text-warning">ينتهي: {{ $item->expires_at->format('Y-m-d') }}</small>
                                    @else
                                        <small class="d-block text-success">بدون تاريخ انتهاء</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @if($item->status === 'active' && $item->assignedOwner && $item->assignedOwner->phone)
                                            @php
                                                $waDigits = preg_replace('/[^0-9]/', '', ($item->assignedOwner->country_code ?? '') . $item->assignedOwner->phone);
                                                $waText = urlencode("مرحباً {$item->assignedOwner->fname}، إليك كود تفعيل اشتراكك في تطبيق نجاح:\n{$item->code}\nيرجى إدخاله في صفحة باقات الاشتراكات لتفعيل باقتك فوراً.");
                                            @endphp
                                            <a href="https://wa.me/{{ $waDigits }}?text={{ $waText }}" target="_blank"
                                               class="btn btn-success btn-sm px-2" title="إرسال الكود للعضو عبر واتساب">
                                                <i class="bx bxl-whatsapp"></i>
                                            </a>
                                        @endif
                                        @if($item->invoice)
                                            <a href="{{ route('admin.subscription.invoices.show', $item->invoice->id) }}"
                                               class="btn btn-info btn-sm text-white px-2" title="عرض فاتورة التأكيد">
                                                <i class="bx bx-receipt"></i> فاتورة
                                            </a>
                                        @endif
                                        @if($item->status === 'active')
                                            <a href="{{ route('admin.subscription.codes.revoke', $item->id) }}"
                                               class="btn btn-warning btn-sm text-dark px-2" title="إلغاء الكود">
                                                <i class="bx bx-block"></i>
                                            </a>
                                        @endif
                                        @if($item->status !== 'used')
                                            <a href="{{ route('admin.subscription.codes.delete', $item->id) }}"
                                               class="btn btn-danger btn-sm px-2" id="delete" title="حذف الكود">
                                                <i class="bx bx-trash"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">لا توجد أكواد تفعيل حتى الآن. اضغط على "توليد كود تفعيل جديد" للبدء.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Generate Code Modal -->
<div class="modal fade" id="generateCodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bx bx-key text-primary"></i> توليد كود تفعيل جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.subscription.codes.generate') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">نوع الكود <span class="text-danger">*</span></label>
                            <select name="code_type" id="code_type_select" class="form-select" required onchange="toggleCodeFields()">
                                <option value="plan">تفعيل باقة كاملة</option>
                                <option value="custom_addon">إضافة مدة / جلسات تدريب / مستخدمين فرعيين فقط</option>
                                <option value="plan_plus_addon">تفعيل باقة + إضافات مخصصة</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">تخصيص لمالك معيّن (اختياري)</label>
                            <select name="owner_id" id="code_owner_select" class="form-select" onchange="toggleQuantityField()">
                                <option value="">-- عام (يعمل لأي مالك مرة واحدة) --</option>
                                @foreach($owners as $owner)
                                    <option value="{{ $owner->id }}">
                                        #{{ $owner->id }} - {{ $owner->fname }} {{ $owner->lname }} ({{ $owner->phone }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12" id="plan_select_wrapper">
                            <label class="form-label fw-bold">الباقة المطلوبة <span class="text-danger">*</span></label>
                            <select name="subscription_plan_id" id="code_plan_select" class="form-select" onchange="autoFillPlanPrice()">
                                <option value="">-- اختر الباقة --</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" data-price="{{ $plan->price }}">
                                        {{ $plan->name }} — {{ number_format($plan->price, 2) }} {{ $settings->currency_label }}
                                        (المدة: {{ $plan->plan_duration == 0 ? 'مفتوحة' : $plan->plan_duration . ' ' . $plan->plan_interval }} | الجلسات: {{ $plan->number_of_training_sessions ?: 'مفتوح' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12" id="addons_wrapper" style="display:none;">
                            <div class="p-3 border rounded bg-light">
                                <div class="fw-bold mb-2 text-primary"><i class="bx bx-plus-circle"></i> الإضافات المخصصة (أيام / جلسات / مستخدمين)</div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">إضافة مدة (بالأيام)</label>
                                        <input type="number" name="extra_days" class="form-control" min="0" value="0">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">إضافة عدد جلسات تدريب</label>
                                        <input type="number" name="extra_training_sessions" class="form-control" min="0" value="0">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">إضافة مستخدمين فرعيين</label>
                                        <input type="number" name="extra_sub_users" class="form-control" min="0" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">المبلغ المدفوع ({{ $settings->currency_label }})</label>
                            <input type="number" step="0.01" min="0" name="amount_paid" id="code_amount_input" class="form-control" value="0.00">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">وسيلة الدفع الخارجية</label>
                            <select name="payment_method" class="form-select">
                                @foreach($paymentMethods as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">مرجع الإيصال / التحويل (اختياري)</label>
                            <input type="text" name="payment_reference" class="form-control" placeholder="رقم الحوالة">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">تاريخ انتهاء صلاحية الكود (اختياري)</label>
                            <input type="date" name="expires_at" class="form-control">
                        </div>

                        <div class="col-md-6" id="quantity_wrapper">
                            <label class="form-label fw-bold">عدد الأكواد المطلوب توليدها (1 - 20)</label>
                            <input type="number" name="quantity" class="form-control" min="1" max="20" value="1">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">ملاحظات الفاتورة (اختياري)</label>
                            <textarea name="notes" rows="2" class="form-control" placeholder="مثال: تم التحويل البنكي بتاريخ اليوم"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="bx bx-key"></i> توليد الكود</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleCodeFields() {
    const type = document.getElementById('code_type_select').value;
    const planWrap = document.getElementById('plan_select_wrapper');
    const addonsWrap = document.getElementById('addons_wrapper');
    if (type === 'plan') {
        planWrap.style.display = 'block';
        addonsWrap.style.display = 'none';
    } else if (type === 'custom_addon') {
        planWrap.style.display = 'none';
        addonsWrap.style.display = 'block';
    } else {
        planWrap.style.display = 'block';
        addonsWrap.style.display = 'block';
    }
}
function toggleQuantityField() {
    const ownerId = document.getElementById('code_owner_select').value;
    document.getElementById('quantity_wrapper').style.display = ownerId ? 'none' : 'block';
}
function autoFillPlanPrice() {
    const sel = document.getElementById('code_plan_select');
    const opt = sel.options[sel.selectedIndex];
    const price = opt ? opt.getAttribute('data-price') : null;
    if (price !== null) {
        document.getElementById('code_amount_input').value = parseFloat(price).toFixed(2);
    }
}
function copyActivationCode(code) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(code).then(() => {
            alert('تم نسخ الكود: ' + code);
        });
    } else {
        prompt('انسخ الكود:', code);
    }
}
</script>

@endsection
