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
                    <li class="breadcrumb-item active" aria-current="page">إعدادات التواصل والتفعيل</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto d-flex gap-2">
            <a href="{{ route('admin.subscription.codes') }}" class="btn btn-outline-primary px-3">
                <i class="bx bx-key"></i> أكواد التفعيل
            </a>
            <a href="{{ route('admin.subscription.invoices') }}" class="btn btn-outline-success px-3">
                <i class="bx bx-receipt"></i> سجل الفواتير
            </a>
        </div>
    </div>
    <!--end breadcrumb-->

    <hr/>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold mb-1">
                        <i class="bx bx-cog text-primary"></i> إعدادات التواصل مع الإدارة وتفعيل الباقات
                    </h5>
                    <p class="text-muted mb-4">
                        تحكم في رقم الواتساب ورقم الهاتف والرسالة التلقائية التي تظهر للأعضاء عند انتهاء باقتهم أو الرغبة في التجديد.
                    </p>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.subscription.settings.update') }}" method="POST">
                        @csrf

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">رقم واتساب الإدارة (مع كود الدولة) <span class="text-success"><i class="bx bxl-whatsapp"></i></span></label>
                                <input type="text" name="admin_whatsapp" class="form-control" dir="ltr"
                                       placeholder="+96594959092"
                                       value="{{ old('admin_whatsapp', $settings->admin_whatsapp) }}">
                                <small class="text-muted">مثال: +96594959092 أو +966501234567 — يُفتح مباشرة في واتساب بضغطة واحدة.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">رقم هاتف الاتصال المباشر (اختياري)</label>
                                <input type="text" name="admin_phone" class="form-control" dir="ltr"
                                       placeholder="+96594959092"
                                       value="{{ old('admin_phone', $settings->admin_phone) }}">
                                <small class="text-muted">يظهر كزر اتصال مباشر بجانب زر الواتساب عند تعبئته.</small>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">رمز العملة في الفواتير والباقات</label>
                                <input type="text" name="currency_label" class="form-control"
                                       placeholder="ر.س"
                                       value="{{ old('currency_label', $settings->currency_label ?: 'ر.س') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">نص رسالة واتساب التلقائية</label>
                            <textarea name="whatsapp_message_template" rows="4" class="form-control">{{ old('whatsapp_message_template', $settings->whatsapp_message_template) }}</textarea>
                            <div class="mt-2 d-flex flex-wrap gap-2">
                                <span class="badge bg-light-primary text-primary"><code>{owner_name}</code> اسم المالك</span>
                                <span class="badge bg-light-primary text-primary"><code>{owner_id}</code> رقم حساب المالك</span>
                                <span class="badge bg-light-primary text-primary"><code>{owner_phone}</code> هاتف المالك</span>
                                <span class="badge bg-light-primary text-primary"><code>{plan_name}</code> اسم الباقة المطلوبة</span>
                                <span class="badge bg-light-success text-success"><code>{admin_link}</code> رابط التفعيل السريع في لوحة التحكم</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">رسالة التوجيه للمالك داخل شاشة الباقات</label>
                            <textarea name="support_note" rows="3" class="form-control"
                                      placeholder="عند انتهاء الباقة يمكنك التواصل مع الإدارة للحصول على كود التفعيل...">{{ old('support_note', $settings->support_note) }}</textarea>
                        </div>

                        <hr class="my-4"/>
                        <h6 class="fw-bold mb-2"><i class="bx bx-show-alt text-primary"></i> التحكم في إظهار وإخفاء الباقات والاشتراكات داخل التطبيق</h6>
                        <p class="text-muted font-13 mb-3">
                            عند إخفاء الباقات والاشتراكات، يتم إخفاء قائمة «باقات الاشتراكات» و«سجل الفواتير» ونوافذ الباقة الافتراضية وتنبيهات حدود الباقة تلقائياً داخل التطبيق (مفيد لمراجعة متجر أبل أو فترات الإتاحة المجانية).
                        </p>

                        @php
                            $showSubsAll = array_key_exists('show_subscriptions_in_app', $settings->getAttributes())
                                ? (bool) $settings->show_subscriptions_in_app
                                : true;
                            $showSubsAndroid = array_key_exists('show_subscriptions_on_android', $settings->getAttributes())
                                ? (bool) $settings->show_subscriptions_on_android
                                : true;
                            $showSubsIos = array_key_exists('show_subscriptions_on_ios', $settings->getAttributes())
                                ? (bool) $settings->show_subscriptions_on_ios
                                : true;
                        @endphp

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="form-check form-switch p-3 border border-2 rounded {{ $showSubsAll ? 'border-success bg-light' : 'border-danger bg-light' }}">
                                    <input class="form-check-input" type="checkbox" id="show_subscriptions_in_app" name="show_subscriptions_in_app"
                                           {{ old('show_subscriptions_in_app', $showSubsAll) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="show_subscriptions_in_app">إظهار الباقات والاشتراكات في التطبيق</label>
                                    <small class="d-block text-muted mt-1">المفتاح الرئيسي لإظهار أو إخفاء نظام الباقات بالكامل عن جميع الأجهزة.</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch p-3 border rounded bg-light">
                                    <input class="form-check-input" type="checkbox" id="show_subscriptions_on_android" name="show_subscriptions_on_android"
                                           {{ old('show_subscriptions_on_android', $showSubsAndroid) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="show_subscriptions_on_android">إظهار الباقات على Android</label>
                                    <small class="d-block text-muted mt-1">التحكم المستقل في ظهور الباقات والاشتراكات لأجهزة أندرويد.</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch p-3 border rounded bg-light">
                                    <input class="form-check-input" type="checkbox" id="show_subscriptions_on_ios" name="show_subscriptions_on_ios"
                                           {{ old('show_subscriptions_on_ios', $showSubsIos) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="show_subscriptions_on_ios">إظهار الباقات على iOS (آيفون)</label>
                                    <small class="d-block text-muted mt-1">يمكنك إخفاء الباقات على آيفون مؤقتاً أثناء مراجعة App Store دون التأثير على أندرويد.</small>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4"/>
                        <h6 class="fw-bold mb-3"><i class="bx bx-sliders text-info"></i> خيارات التواصل وأكواد التفعيل</h6>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="form-check form-switch p-3 border rounded bg-light">
                                    <input class="form-check-input" type="checkbox" id="enable_activation_codes" name="enable_activation_codes"
                                           {{ old('enable_activation_codes', $settings->enable_activation_codes) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="enable_activation_codes">تفعيل نظام أكواد التفعيل</label>
                                    <small class="d-block text-muted mt-1">السماح للملاك بإدخال وتفعيل الأكواد داخل التطبيق.</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch p-3 border rounded bg-light">
                                    <input class="form-check-input" type="checkbox" id="show_whatsapp_on_android" name="show_whatsapp_on_android"
                                           {{ old('show_whatsapp_on_android', $settings->show_whatsapp_on_android) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="show_whatsapp_on_android">إظهار التواصل على Android</label>
                                    <small class="d-block text-muted mt-1">إظهار زر التواصل عبر واتساب على أجهزة أندرويد.</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch p-3 border rounded bg-light">
                                    <input class="form-check-input" type="checkbox" id="show_whatsapp_on_ios" name="show_whatsapp_on_ios"
                                           {{ old('show_whatsapp_on_ios', $settings->show_whatsapp_on_ios) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="show_whatsapp_on_ios">إظهار التواصل على iOS</label>
                                    <small class="d-block text-muted mt-1">يمكنك إخفاؤه مؤقتاً أثناء مراجعة متجر أبل إن لزم.</small>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">
                            <i class="bx bx-save me-1"></i> حفظ الإعدادات
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-start border-4 border-success">
                <div class="card-body">
                    <h6 class="fw-bold text-success mb-3"><i class="bx bx-bulb"></i> كيف تعمل دورة التفعيل؟</h6>
                    <ol class="ps-3 mb-0 lh-lg text-secondary">
                        <li>عند انتهاء باقة العضو أو استنفاد جلساته، تظهر له رسالة واضحة مع زر <strong>التواصل عبر واتساب</strong>.</li>
                        <li>يُفتح واتساب تلقائياً برسالة جاهزة فيها <strong>اسم العضو ورقم حسابه وهاتفه والباقة المطلوبة</strong>.</li>
                        <li>بعد استلام الدفع من أي وسيلة خارجية، تختار الإدارة أحد طريقين:
                            <ul>
                                <li>توليد <strong>كود تفعيل</strong> وإرساله للعضو ليضعه في التطبيق.</li>
                                <li>أو <strong>التفعيل المباشر بضغطة زر</strong> من لوحة التحكم.</li>
                            </ul>
                        </li>
                        <li>تُصدر المنصة تلقائياً <strong>فاتورة تأكيد رسمية</strong> تُحفظ في سجل الإدارة وتظهر فوراً في سجل فواتير العضو داخل التطبيق.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
