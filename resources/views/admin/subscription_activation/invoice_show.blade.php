@extends('admin.master_admin')
@section('admin')

<style>
@media print {
    .sidebar-wrapper, header, .page-breadcrumb, .no-print, footer {
        display: none !important;
    }
    .page-wrapper {
        margin: 0 !important;
        padding: 0 !important;
    }
    .invoice-card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>

<div class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3 no-print">
        <div class="breadcrumb-title pe-3">سجل الفواتير والمدفوعات</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.subscription.invoices') }}">سجل الفواتير</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $invoice->invoice_number }}</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto d-flex gap-2">
            <button type="button" onclick="window.print()" class="btn btn-primary px-4">
                <i class="bx bx-printer"></i> طباعة الفاتورة
            </button>
            <a href="{{ route('admin.subscription.invoices') }}" class="btn btn-secondary px-3">
                <i class="bx bx-arrow-back"></i> عودة للسجل
            </a>
        </div>
    </div>

    <div class="card invoice-card mx-auto" style="max-width: 820px;">
        <div class="card-body p-4 p-md-5">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 pb-4 border-bottom">
                <div>
                    <h3 class="fw-bold text-dark mb-1">فاتورة تأكيد اشتراك</h3>
                    <div class="text-muted">منصة نجاح لسباقات وتدريب الهجن</div>
                    @if($settings->admin_whatsapp)
                        <small class="text-muted d-block mt-1">دعم الإدارة: {{ $settings->admin_whatsapp }}</small>
                    @endif
                </div>
                <div class="text-md-end">
                    <div class="badge bg-success fs-6 px-3 py-2 mb-2">
                        <i class="bx bx-check-circle"></i>
                        {{ $invoice->status === 'confirmed' ? 'فاتورة مؤكدة' : $invoice->status }}
                    </div>
                    <div class="fw-bold text-primary fs-5">{{ $invoice->invoice_number }}</div>
                    <div class="text-muted small">تاريخ الإصدار: {{ ($invoice->issued_at ?? $invoice->created_at)->format('Y-m-d H:i') }}</div>
                </div>
            </div>

            <!-- Owner & Payment Info -->
            <div class="row g-4 py-4 border-bottom">
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">بيانات العضو (المالك)</h6>
                    <div class="fw-bold fs-5 text-dark">
                        {{ $invoice->owner ? ($invoice->owner->fname . ' ' . $invoice->owner->lname) : 'غير متوفر' }}
                    </div>
                    @if($invoice->owner)
                        <div class="text-secondary">رقم الحساب: #{{ $invoice->owner->id }}</div>
                        <div class="text-secondary">الهاتف: {{ $invoice->owner->phone }}</div>
                        @if($invoice->owner->email)
                            <div class="text-secondary">البريد: {{ $invoice->owner->email }}</div>
                        @endif
                    @endif
                </div>
                <div class="col-md-6 text-md-end">
                    <h6 class="text-muted mb-2">تفاصيل الدفع والتفعيل</h6>
                    <div><strong>نوع العملية:</strong> {{ $invoice->action_label }}</div>
                    <div><strong>وسيلة الدفع:</strong> {{ $invoice->payment_method ?: 'تفعيل من الإدارة' }}</div>
                    @if($invoice->activation_code_used)
                        <div><strong>كود التفعيل:</strong> <code>{{ $invoice->activation_code_used }}</code></div>
                    @endif
                    @if($invoice->payment_reference)
                        <div><strong>الرقم المرجعي:</strong> <code>{{ $invoice->payment_reference }}</code></div>
                    @endif
                    @if($invoice->issuedByAdmin)
                        <div class="small text-muted mt-1">معتمدة بواسطة: {{ $invoice->issuedByAdmin->fname ?? $invoice->issuedByAdmin->name }}</div>
                    @endif
                </div>
            </div>

            <!-- Line Items -->
            <div class="table-responsive my-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>الوصف / الباقة</th>
                            <th class="text-center">المدة المضافة</th>
                            <th class="text-center">جلسات التدريب</th>
                            <th class="text-center">المستخدمون الفرعيون</th>
                            <th class="text-end">المبلغ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $invoice->title }}</div>
                                @if($invoice->plan_name)
                                    <small class="text-muted">الباقة: {{ $invoice->plan_name }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                {{ $invoice->days_added > 0 ? ($invoice->days_added . ' يوم') : 'مفتوحة' }}
                            </td>
                            <td class="text-center">
                                {{ $invoice->training_sessions_added > 0 ? ($invoice->training_sessions_added . ' جلسة') : 'مفتوح / بدون تغيير' }}
                            </td>
                            <td class="text-center">
                                {{ $invoice->sub_users_added > 0 ? ($invoice->sub_users_added . ' مستخدم') : 'مفتوح / بدون تغيير' }}
                            </td>
                            <td class="text-end fw-bold fs-6">
                                {{ number_format($invoice->amount_paid, 2) }} {{ $invoice->currency }}
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <th colspan="4" class="text-end fs-5">الإجمالي المدفوع</th>
                            <th class="text-end fs-5 text-success">{{ number_format($invoice->amount_paid, 2) }} {{ $invoice->currency }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Validity & Notes -->
            <div class="row g-3 bg-light p-3 rounded">
                <div class="col-md-6">
                    <strong>بداية صلاحية الاشتراك:</strong>
                    {{ $invoice->period_start ? $invoice->period_start->format('Y-m-d H:i') : 'غير محدد' }}
                </div>
                <div class="col-md-6">
                    <strong>نهاية صلاحية الاشتراك:</strong>
                    {{ $invoice->period_end ? $invoice->period_end->format('Y-m-d H:i') : 'فترة مفتوحة' }}
                </div>
                @if($invoice->notes)
                    <div class="col-12 border-top pt-2 mt-2">
                        <strong>ملاحظات:</strong> {{ $invoice->notes }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
