@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">إعدادات التطبيق</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">إصدارات وتحديثات التطبيق (iOS / Android)</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-4">

            {{-- Display Success Message --}}
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Display Validation Errors --}}
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="alert alert-info border-0 bg-light-info mb-4">
                <i class="bx bx-info-circle font-18 me-1"></i>
                <strong>التحكم المنفصل لكل منصة:</strong>
                يمكنك تفعيل تحديث جديد لـ <strong>iOS (الآيفون)</strong> فقط مع إيقافه أو إبقاء الإصدار السابق على <strong>Android (الأندرويد)</strong>، أو العكس. يظهر التنبيه في التطبيق فقط عندما يكون رقم الإصدار المسجل هنا <strong>أحدث</strong> من الإصدار المثبّت على جهاز المستخدم.
            </div>

            <form method="POST" action="{{ route('update.versions.store') }}">
                @csrf

                <div class="row g-4 mb-4">
                    {{-- Apple iOS Card --}}
                    <div class="col-lg-6">
                        <div class="card border border-2 border-primary h-100 mb-0">
                            <div class="card-header bg-light-primary d-flex align-items-center justify-content-between py-3">
                                <h6 class="mb-0 fw-bold text-primary">
                                    <i class="bx bxl-apple font-20 me-1"></i> تحديثات الآيفون (iOS / App Store)
                                </h6>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           role="switch"
                                           id="ios_update_enabled"
                                           name="ios_update_enabled"
                                           value="1"
                                           {{ old('ios_update_enabled', $appVersion->ios_update_enabled ?? true) ? 'checked' : '' }}
                                           style="width: 2.8em; height: 1.4em; cursor: pointer;">
                                    <label class="form-check-label fw-bold ms-1" for="ios_update_enabled" style="cursor: pointer;">مفعّل</label>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">إصدار iOS المطلوب</label>
                                    <input type="text"
                                           dir="ltr"
                                           class="form-control @error('ios_version') is-invalid @enderror"
                                           name="ios_version"
                                           placeholder="1.0.1"
                                           value="{{ old('ios_version', $appVersion->ios_version ?: $appVersion->version) }}">
                                    <small class="text-muted">مثال: 1.0.1 — إذا كان مطابقاً لإصدار التطبيق المثبّت لن يظهر تنبيه.</small>
                                    @error('ios_version')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">هل تحديث iOS إلزامي؟</label>
                                    @php
                                        $iosReq = old('ios_update_required', $appVersion->ios_update_required ?: ($appVersion->update_required ?? 'no'));
                                    @endphp
                                    <select name="ios_update_required" class="form-select">
                                        <option value="no" {{ $iosReq !== 'yes' ? 'selected' : '' }}>لا (اختياري — يمكن الضغط على لاحقاً)</option>
                                        <option value="yes" {{ $iosReq === 'yes' ? 'selected' : '' }}>نعم (إلزامي — يجبر المستخدم على التحديث)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">رابط التطبيق على App Store</label>
                                    <input type="text"
                                           dir="ltr"
                                           class="form-control @error('ios') is-invalid @enderror"
                                           name="ios"
                                           placeholder="https://apps.apple.com/app/id..."
                                           value="{{ old('ios', $appVersion->ios) }}">
                                    @error('ios')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-0">
                                    <label class="form-label fw-bold">رسالة / مميزات تحديث iOS</label>
                                    <textarea name="ios_des"
                                              class="form-control @error('ios_des') is-invalid @enderror"
                                              rows="3"
                                              placeholder="يتوفر إصدار جديد يتضمن تحسينات في الأداء وإصلاحات مهمة...">{{ old('ios_des', $appVersion->ios_des ?: $appVersion->des) }}</textarea>
                                    @error('ios_des')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Android Google Play Card --}}
                    <div class="col-lg-6">
                        <div class="card border border-2 border-success h-100 mb-0">
                            <div class="card-header bg-light-success d-flex align-items-center justify-content-between py-3">
                                <h6 class="mb-0 fw-bold text-success">
                                    <i class="bx bxl-android font-20 me-1"></i> تحديثات الأندرويد (Android / Google Play)
                                </h6>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           role="switch"
                                           id="android_update_enabled"
                                           name="android_update_enabled"
                                           value="1"
                                           {{ old('android_update_enabled', $appVersion->android_update_enabled ?? true) ? 'checked' : '' }}
                                           style="width: 2.8em; height: 1.4em; cursor: pointer;">
                                    <label class="form-check-label fw-bold ms-1" for="android_update_enabled" style="cursor: pointer;">مفعّل</label>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">إصدار Android المطلوب</label>
                                    <input type="text"
                                           dir="ltr"
                                           class="form-control @error('android_version') is-invalid @enderror"
                                           name="android_version"
                                           placeholder="1.0.1"
                                           value="{{ old('android_version', $appVersion->android_version ?: $appVersion->version) }}">
                                    <small class="text-muted">مثال: 1.0.1 — إذا كان مطابقاً لإصدار التطبيق المثبّت لن يظهر تنبيه.</small>
                                    @error('android_version')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">هل تحديث Android إلزامي؟</label>
                                    @php
                                        $andReq = old('android_update_required', $appVersion->android_update_required ?: ($appVersion->update_required ?? 'no'));
                                    @endphp
                                    <select name="android_update_required" class="form-select">
                                        <option value="no" {{ $andReq !== 'yes' ? 'selected' : '' }}>لا (اختياري — يمكن الضغط على لاحقاً)</option>
                                        <option value="yes" {{ $andReq === 'yes' ? 'selected' : '' }}>نعم (إلزامي — يجبر المستخدم على التحديث)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">رابط التطبيق على Google Play</label>
                                    <input type="text"
                                           dir="ltr"
                                           class="form-control @error('android') is-invalid @enderror"
                                           name="android"
                                           placeholder="https://play.google.com/store/apps/details?id=..."
                                           value="{{ old('android', $appVersion->android) }}">
                                    @error('android')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-0">
                                    <label class="form-label fw-bold">رسالة / مميزات تحديث Android</label>
                                    <textarea name="android_des"
                                              class="form-control @error('android_des') is-invalid @enderror"
                                              rows="3"
                                              placeholder="يتوفر إصدار جديد يتضمن تحسينات في الأداء وإصلاحات مهمة...">{{ old('android_des', $appVersion->android_des ?: $appVersion->des) }}</textarea>
                                    @error('android_des')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="app_type" value="{{ $appVersion->app_type }}">

                <hr class="my-4">
                <h5 class="mb-3 text-primary">إعدادات تسجيل الملاك الجدد والتواصل عبر الواتساب</h5>

                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">وقف التفعيل التلقائي للملاك الجدد</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   role="switch"
                                   id="stop_owner_registration"
                                   name="stop_owner_registration"
                                   value="1"
                                   {{ old('stop_owner_registration', $appVersion->stop_owner_registration) ? 'checked' : '' }}
                                   style="width: 3em; height: 1.5em; cursor: pointer;">
                            <label class="form-check-label ms-2" for="stop_owner_registration" style="cursor: pointer;">
                                عند تفعيل هذا الخيار، أي مالك جديد غير مسجل يقوم بالتسجيل في التطبيق سيكون حسابه (غير مفعل) حتى يتم تفعيله من لوحة التحكم (عرض الملاك).
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">رقم الواتساب للتواصل لتفعيل الحساب</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text"
                               dir="ltr"
                               class="form-control @error('whatsapp_contact') is-invalid @enderror"
                               name="whatsapp_contact"
                               placeholder="+96551673464"
                               value="{{ old('whatsapp_contact', $appVersion->whatsapp_contact ?? '+96551673464') }}">
                        <small class="text-muted">أدخل الرقم مع رمز الدولة (مثال: +96551673464)</small>
                        @error('whatsapp_contact')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">رسالة الحساب غير المفعل (اختياري)</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="inactive_owner_message"
                                  class="form-control @error('inactive_owner_message') is-invalid @enderror"
                                  rows="2"
                                  placeholder="حسابك غير مفعل حالياً، الرجاء التواصل مع الإدارة عبر الواتساب لتفعيل الحساب.">{{ old('inactive_owner_message', $appVersion->inactive_owner_message) }}</textarea>
                        @error('inactive_owner_message')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9 text-secondary">
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">
                            <i class="bx bx-save me-1"></i> حفظ الإعدادات
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
