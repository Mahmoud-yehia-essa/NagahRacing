@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div class="col-lg-16">
    <div class="card">
        <div class="card-body">

            {{-- Display Success Message --}}
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Display Validation Errors --}}
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('update.versions.store') }}">
                @csrf

                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">اصدار التطبيق الحالي</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" class="form-control @error('version') is-invalid @enderror"
                               name="version" value="{{ old('version', $appVersion->version) }}">
                        @error('version')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">App Store رابط التطبيق على</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" class="form-control @error('ios') is-invalid @enderror"
                               name="ios" value="{{ old('ios', $appVersion->ios) }}">
                        @error('ios')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">Google Play  رابط التطبيق على</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" class="form-control @error('android') is-invalid @enderror"
                               name="android" value="{{ old('android', $appVersion->android) }}">
                        @error('android')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">الوصف</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="des" class="form-control @error('des') is-invalid @enderror"
                                  id="input11" placeholder="Description ..." rows="3">{{ old('des', $appVersion->des) }}</textarea>
                        @error('des')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>



                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">التحديث في التطبيق الزامي ؟</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">


                        <select  name="update_required" class="form-select" aria-label="Default select example">



                            <option value="yes" {{ old('update_required',$appVersion->update_required) == 'yes' ? 'selected' : '' }} >نعم</option>

                            <option value="no" {{ old('update_required',$appVersion->update_required) == 'no' ? 'selected' : '' }} >لا</option>




                        </select>

                        @error('special') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>


                {{-- <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">الألعاب في التطبيق ؟</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">


                        <select  name="app_type" class="form-select" aria-label="Default select example">



                            <option value="free" {{ old('app_type',$appVersion->app_type) == 'free' ? 'selected' : '' }} >مجانية</option>

                            <option value="paid" {{ old('app_type',$appVersion->app_type) == 'paid' ? 'selected' : '' }} >مدفوعة</option>

                            <option value="off" {{ old('app_type',$appVersion->app_type) == 'off' ? 'selected' : '' }} >موقوفة (إيقاف الألعاب)</option>




                        </select>

                        @error('special') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div> --}}
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
                        <input type="submit" class="btn btn-primary px-4" value="تحديث">
                    </div>
                </div>



            </form>
        </div>
    </div>
</div>
@endsection
