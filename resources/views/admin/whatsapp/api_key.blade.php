@extends('admin.master_admin')
@section('admin')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">إدارة اشتراكات ومفاتيح API للواتساب</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">مفاتيح الـ API واشتراكات بوابة الواتساب</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="{{ $gatewayUrl }}" target="_blank" class="btn btn-success">
            <i class="bx bx-link-external me-1"></i> فتح صفحة الربط المستقلة (QR Code)
        </a>
    </div>
</div>

<hr/>

{{-- Validation Errors --}}
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- KPI Stats Row --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 text-muted small fw-bold">إجمالي مفاتيح الـ API</p>
                        <h4 class="mb-0 fw-bold">{{ $totalAccounts }}</h4>
                    </div>
                    <div class="fs-2 text-primary"><i class="bx bx-key"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 text-muted small fw-bold">الاشتراكات النشطة</p>
                        <h4 class="mb-0 fw-bold text-success">{{ $activeAccounts }}</h4>
                    </div>
                    <div class="fs-2 text-success"><i class="bx bx-check-shield"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-info border-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 text-muted small fw-bold">الأرقام المتصلة حالياً</p>
                        <h4 class="mb-0 fw-bold text-info">{{ $connectedAccounts }}</h4>
                    </div>
                    <div class="fs-2 text-info"><i class="bx bxl-whatsapp"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 text-muted small fw-bold">قيمة الاشتراكات النشطة</p>
                        <h4 class="mb-0 fw-bold text-dark" dir="ltr">${{ number_format($totalActiveRevenue, 0) }}</h4>
                    </div>
                    <div class="fs-2 text-warning"><i class="bx bx-dollar-circle"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Create New Subscription / API Key Card --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h6 class="mb-0 fw-bold text-primary">
            <i class="bx bx-plus-circle fs-5 align-middle me-1"></i>
            إنشاء مفتاح API واشتراك واتساب جديد (QR Code ورقم مستقل)
        </h6>
        <span class="badge bg-light text-dark border">
            الشهري: <strong>$7</strong> &nbsp;|&nbsp; السنوي: <strong>$70</strong>
        </span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.whatsapp.api_key.store') }}">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold">اسم المشترك / الحساب <span class="text-danger">*</span></label>
                    <input type="text"
                           name="label"
                           class="form-control"
                           placeholder="مثال: متجر الخليج أو أحمد محمد"
                           value="{{ old('label') }}"
                           required>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">نوع الاشتراك <span class="text-danger">*</span></label>
                    <select name="plan_type" id="createPlanType" class="form-select" onchange="syncCreatePlanDefaults()" required>
                        <option value="monthly" {{ old('plan_type') == 'monthly' ? 'selected' : '' }}>شهري ($7)</option>
                        <option value="yearly" {{ old('plan_type', 'yearly') == 'yearly' ? 'selected' : '' }}>سنوي ($70)</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">قيمة الاشتراك ($)</label>
                    <input type="number"
                           step="0.01"
                           min="0"
                           dir="ltr"
                           name="subscription_price"
                           id="createPrice"
                           class="form-control"
                           value="{{ old('subscription_price', '70') }}"
                           required>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">تاريخ الانتهاء</label>
                    <input type="date"
                           dir="ltr"
                           name="expires_at"
                           id="createExpiresAt"
                           class="form-control"
                           value="{{ old('expires_at', now()->addYear()->format('Y-m-d')) }}"
                           required>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">ملاحظات (اختياري)</label>
                    <input type="text"
                           name="notes"
                           class="form-control"
                           placeholder="رقم العميل أو أي ملاحظة..."
                           value="{{ old('notes') }}">
                </div>

                <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2">
                    <small class="text-muted">
                        <i class="bx bx-info-circle text-primary me-1"></i>
                        سيتم إنشاء <strong>مفتاح API سري فريد</strong> وجلسة واتساب معزولة بالكامل. يسلم المفتاح للمشترك للدخول إلى <code>{{ $gatewayUrl }}</code> وربط رقمه عبر QR Code.
                    </small>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bx bx-key me-1"></i> إنشاء المفتاح والاشتراك الآن
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- All Subscriptions / API Keys Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold">
            <i class="bx bxl-whatsapp text-success fs-5 align-middle me-1"></i>
            قائمة اشتراكات ومفاتيح API الواتساب ({{ $accounts->count() }})
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>المشترك / الحساب</th>
                        <th>مفتاح الـ API (سري)</th>
                        <th>الاشتراك والقيمة</th>
                        <th>الصلاحية والمدة المتبقية</th>
                        <th>حالة الواتساب</th>
                        <th>حالة الاشتراك</th>
                        <th style="min-width: 240px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($accounts as $idx => $acc)
                        @php
                            $sub = $acc->getSubscriptionSummary();
                            $directLink = $gatewayUrl . '?apikey=' . $acc->api_key;
                        @endphp
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $acc->label }}</div>
                                @if($acc->is_primary_otp)
                                    <span class="badge bg-primary mt-1">الحساب الرئيسي لـ OTP التطبيق</span>
                                @endif
                                @if(!empty($acc->notes))
                                    <div class="text-muted small mt-1">{{ $acc->notes }}</div>
                                @endif
                                <div class="text-muted" style="font-size: 11px;">جلسة: <code>{{ $acc->getSessionKey() }}</code></div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1 mb-1">
                                    <code class="bg-light px-2 py-1 rounded border text-primary small" dir="ltr" style="max-width: 190px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $acc->api_key }}">{{ $acc->api_key }}</code>
                                    <button type="button" class="btn btn-sm btn-outline-primary" title="نسخ مفتاح الـ API" onclick="copyTextValue('{{ $acc->api_key }}', 'تم نسخ مفتاح الـ API!')">
                                        <i class="bx bx-copy"></i>
                                    </button>
                                </div>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-success py-0 px-2" style="font-size: 11px;" onclick="copyTextValue('{{ $directLink }}', 'تم نسخ رابط الدخول المباشر للمشترك!')">
                                        <i class="bx bx-link me-1"></i> نسخ رابط الدخول
                                    </button>
                                    <a href="{{ $directLink }}" target="_blank" class="btn btn-sm btn-light border py-0 px-2" style="font-size: 11px;" title="فتح صفحة الربط لهذا المشترك">
                                        <i class="bx bx-link-external"></i> فتح
                                    </a>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $acc->plan_type === 'monthly' ? 'bg-info text-dark' : 'bg-dark text-warning' }} px-2 py-1">
                                    {{ $sub['plan_label'] }}
                                </span>
                                <div class="fw-bold fs-6 mt-1 text-dark" dir="ltr">{{ $sub['formatted_price'] }}</div>
                            </td>
                            <td>
                                <div class="small text-muted">ينتهي: <strong dir="ltr">{{ $sub['expires_date'] }}</strong></div>
                                @if($sub['is_expired'])
                                    <span class="badge bg-danger mt-1">منتهي الصلاحية</span>
                                @elseif($sub['is_expiring_soon'])
                                    <span class="badge bg-warning text-dark mt-1">متبقي {{ $sub['human_remaining'] }}</span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success-subtle mt-1">متبقي {{ $sub['human_remaining'] }}</span>
                                @endif
                            </td>
                            <td>
                                @if($acc->is_connected && !empty($acc->connected_phone))
                                    <span class="badge bg-success px-2 py-1">متصل</span>
                                    <div class="small fw-bold mt-1" dir="ltr">+{{ $acc->connected_phone }}</div>
                                @else
                                    <span class="badge bg-secondary px-2 py-1">غير مرتبط</span>
                                @endif
                            </td>
                            <td>
                                @if($sub['status_key'] === 'active')
                                    <span class="badge bg-success px-2 py-1">نشط وساري</span>
                                @elseif($sub['status_key'] === 'expired')
                                    <span class="badge bg-danger px-2 py-1">منتهي</span>
                                @else
                                    <span class="badge bg-warning text-dark px-2 py-1">موقوف</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    {{-- تجديد +شهر ($7) --}}
                                    <form method="POST" action="{{ route('admin.whatsapp.api_key.renew', $acc->id) }}" class="d-inline" onsubmit="return confirm('تجديد اشتراك {{ $acc->label }} لمدة شهر ($7)؟');">
                                        @csrf
                                        <input type="hidden" name="plan_type" value="monthly">
                                        <button type="submit" class="btn btn-sm btn-outline-info" title="تجديد شهر ($7)">+شهر ($7)</button>
                                    </form>

                                    {{-- تجديد +سنة ($70) --}}
                                    <form method="POST" action="{{ route('admin.whatsapp.api_key.renew', $acc->id) }}" class="d-inline" onsubmit="return confirm('تجديد اشتراك {{ $acc->label }} لمدة سنة ($70)؟');">
                                        @csrf
                                        <input type="hidden" name="plan_type" value="yearly">
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="تجديد سنة ($70)">+سنة ($70)</button>
                                    </form>

                                    {{-- تعديل --}}
                                    <button type="button"
                                            class="btn btn-sm btn-info text-white"
                                            title="تعديل الاشتراك"
                                            onclick="openEditModal({{ $acc->id }}, @js($acc->label), @js($acc->plan_type), {{ (float) $acc->subscription_price }}, @js($acc->expires_at ? $acc->expires_at->format('Y-m-d') : now()->addYear()->format('Y-m-d')), @js($acc->notes ?? ''))">
                                        <i class="bx bx-edit"></i>
                                    </button>

                                    {{-- إيقاف / تفعيل --}}
                                    <form method="POST" action="{{ route('admin.whatsapp.api_key.toggle', $acc->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $acc->is_active ? 'btn-warning' : 'btn-success' }}" title="{{ $acc->is_active ? 'إيقاف الاشتراك مؤقتاً' : 'تفعيل الاشتراك' }}">
                                            <i class="bx {{ $acc->is_active ? 'bx-pause' : 'bx-play' }}"></i>
                                        </button>
                                    </form>

                                    {{-- تجديد مفتاح الـ API --}}
                                    <form method="POST" action="{{ route('admin.whatsapp.api_key.regenerate', $acc->id) }}" class="d-inline" onsubmit="return confirm('سيتم إلغاء مفتاح الـ API القديم فوراً وتوليد مفتاح جديد لحساب {{ $acc->label }}. هل تريد المتابعة؟');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-secondary" title="توليد مفتاح API جديد">
                                            <i class="bx bx-refresh"></i>
                                        </button>
                                    </form>

                                    {{-- تعيين كحساب رئيسي للـ OTP --}}
                                    @if(!$acc->is_primary_otp)
                                        <form method="POST" action="{{ route('admin.whatsapp.api_key.set_primary', $acc->id) }}" class="d-inline" onsubmit="return confirm('تعيين {{ $acc->label }} كحساب رئيسي لإرسال OTP لتطبيق نجاح ريسنج؟');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="تعيين كحساب رئيسي للـ OTP">
                                                <i class="bx bx-star"></i>
                                            </button>
                                        </form>

                                        {{-- حذف الاشتراك --}}
                                        <form method="POST" action="{{ route('admin.whatsapp.api_key.delete', $acc->id) }}" class="d-inline" onsubmit="return confirm('تحذير: سيتم حذف اشتراك {{ $acc->label }} وفصل رقمه عن الواتساب نهائياً. هل أنت متأكد؟');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger" title="حذف الاشتراك">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Edit Subscription Modal --}}
<div class="modal fade" id="editSubscriptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="editSubscriptionForm" action="">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">تعديل بيانات وصلاحية الاشتراك</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم المشترك / الحساب</label>
                        <input type="text" name="label" id="editLabel" class="form-control" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">نوع الاشتراك</label>
                            <select name="plan_type" id="editPlanType" class="form-select" onchange="syncEditPlanPrice()" required>
                                <option value="monthly">شهري ($7)</option>
                                <option value="yearly">سنوي ($70)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">قيمة الاشتراك ($)</label>
                            <input type="number" step="0.01" min="0" dir="ltr" name="subscription_price" id="editPrice" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">تاريخ انتهاء الصلاحية</label>
                        <input type="date" dir="ltr" name="expires_at" id="editExpiresAt" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold">ملاحظات</label>
                        <input type="text" name="notes" id="editNotes" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function copyTextValue(text, successMsg) {
        navigator.clipboard.writeText(text).then(function() {
            alert(successMsg || 'تم النسخ بنجاح!');
        }, function() {
            prompt('انسخ النص التالي:', text);
        });
    }

    function addMonthsToDate(date, months) {
        var d = new Date(date.getTime());
        d.setMonth(d.getMonth() + months);
        return d.toISOString().split('T')[0];
    }

    function syncCreatePlanDefaults() {
        var plan = document.getElementById('createPlanType').value;
        var priceInput = document.getElementById('createPrice');
        var expiresInput = document.getElementById('createExpiresAt');
        if (plan === 'monthly') {
            priceInput.value = '7';
            expiresInput.value = addMonthsToDate(new Date(), 1);
        } else {
            priceInput.value = '70';
            expiresInput.value = addMonthsToDate(new Date(), 12);
        }
    }

    function syncEditPlanPrice() {
        var plan = document.getElementById('editPlanType').value;
        document.getElementById('editPrice').value = (plan === 'monthly') ? '7' : '70';
    }

    function openEditModal(id, label, planType, price, expiresAt, notes) {
        var baseAction = "{{ url('/admin/whatsapp-api') }}";
        document.getElementById('editSubscriptionForm').action = baseAction + '/' + id + '/update';
        document.getElementById('editLabel').value = label;
        document.getElementById('editPlanType').value = planType;
        document.getElementById('editPrice').value = price;
        document.getElementById('editExpiresAt').value = expiresAt;
        document.getElementById('editNotes').value = notes || '';

        var modal = new bootstrap.Modal(document.getElementById('editSubscriptionModal'));
        modal.show();
    }
</script>

@endsection
