<!-- Direct Admin Activation / Top-Up Modal -->
<div class="modal fade" id="directActivateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bx bx-bolt-circle text-success"></i> تفعيل / شحن اشتراك مباشر لمالك</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.subscription.direct.activate') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info py-2 font-13">
                        <i class="bx bx-info-circle"></i>
                        يتم تطبيق التفعيل فوراً على حساب المالك وإصدار <strong>فاتورة تأكيد رسمية</strong> تظهر مباشرةً في سجل المالك والإدارة.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">اختر المالك <span class="text-danger">*</span></label>
                            <select name="owner_id" class="form-select" required>
                                <option value="">-- اختر المالك --</option>
                                @foreach($owners as $owner)
                                    <option value="{{ $owner->id }}">
                                        #{{ $owner->id }} - {{ $owner->fname }} {{ $owner->lname }} ({{ $owner->phone }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">نوع العملية <span class="text-danger">*</span></label>
                            <select name="code_type" id="direct_code_type" class="form-select" required onchange="toggleDirectFields()">
                                <option value="plan">تفعيل / تجديد باقة كاملة</option>
                                <option value="custom_addon">إضافة مدة أو جلسات تدريب للاشتراك الحالي</option>
                                <option value="plan_plus_addon">تفعيل باقة + إضافات مخصصة</option>
                            </select>
                        </div>

                        <div class="col-md-12" id="direct_plan_wrapper">
                            <label class="form-label fw-bold">الباقة <span class="text-danger">*</span></label>
                            <select name="subscription_plan_id" id="direct_plan_select" class="form-select" onchange="autoFillDirectPrice()">
                                <option value="">-- اختر الباقة --</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" data-price="{{ $plan->price }}">
                                        {{ $plan->name }} — {{ number_format($plan->price, 2) }} {{ $settings->currency_label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12" id="direct_addons_wrapper" style="display:none;">
                            <div class="p-3 border rounded bg-light">
                                <div class="fw-bold mb-2 text-success"><i class="bx bx-plus-circle"></i> الإضافات المخصصة</div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">أيام إضافية</label>
                                        <input type="number" name="extra_days" class="form-control" min="0" value="0">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">جلسات تدريب إضافية</label>
                                        <input type="number" name="extra_training_sessions" class="form-control" min="0" value="0">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">مستخدمون فرعيون إضافيون</label>
                                        <input type="number" name="extra_sub_users" class="form-control" min="0" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">المبلغ المحصّل ({{ $settings->currency_label }})</label>
                            <input type="number" step="0.01" min="0" name="amount_paid" id="direct_amount_input" class="form-control" value="0.00">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">وسيلة الدفع</label>
                            <select name="payment_method" class="form-select">
                                @foreach($paymentMethods as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">مرجع الحوالة / الإيصال</label>
                            <input type="text" name="payment_reference" class="form-control" placeholder="اختياري">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">ملاحظات الفاتورة</label>
                            <textarea name="notes" rows="2" class="form-control" placeholder="مثال: تم تأكيد التحويل عبر واتساب"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold"><i class="bx bx-check-circle"></i> تأكيد التفعيل وإصدار الفاتورة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleDirectFields() {
    const type = document.getElementById('direct_code_type').value;
    const planWrap = document.getElementById('direct_plan_wrapper');
    const addonsWrap = document.getElementById('direct_addons_wrapper');
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
function autoFillDirectPrice() {
    const sel = document.getElementById('direct_plan_select');
    const opt = sel.options[sel.selectedIndex];
    const price = opt ? opt.getAttribute('data-price') : null;
    if (price !== null) {
        document.getElementById('direct_amount_input').value = parseFloat(price).toFixed(2);
    }
}
</script>
