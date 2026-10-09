@extends('admin.master_admin')
@section('admin')

<div class="page-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">تعديل بيانات المستفيد</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('all.sub.users', ['owner_id' => $subUser->owner_id]) }}">إدارة المستفيدين</a></li>
                    <li class="breadcrumb-item active" aria-current="page">تعديل مستفيد</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="container">
        <div class="main-body">
            <div class="row">
                <div class="col-lg-10">
                    <div class="card">
                        <div class="card-body">
                            <!-- Display Validation Errors -->
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="post" action="{{ route('update.sub.user') }}">
                                @csrf
                                
                                <input type="hidden" name="id" value="{{ $subUser->id }}" />

                                <!-- Select Owner -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">المالك</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <select name="owner_id" class="form-select @error('owner_id') is-invalid @enderror">
                                            <option value="">-- اختر المالك --</option>
                                            @foreach($owners as $owner)
                                                <option value="{{ $owner->id }}" {{ $subUser->owner_id == $owner->id ? 'selected' : '' }}>
                                                    {{ $owner->fname }} {{ $owner->lname }} ({{ $owner->phone }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('owner_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Beneficiary Full Name -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">الاسم الكامل</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name', $subUser->full_name) }}" placeholder="أدخل اسم المستفيد الكامل" />
                                        @error('full_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Login Code -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">كود الدخول</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" class="form-control" value="{{ $subUser->login_code }}" readonly disabled />
                                    </div>
                                </div>

                                <!-- Phone Number -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">رقم الهاتف</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $subUser->phone) }}" placeholder="أدخل رقم هاتف المستفيد (اختياري)" />
                                        @error('phone')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">الحالة</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                                            <option value="active" {{ old('status', $subUser->status) == 'active' ? 'selected' : '' }}>نشط</option>
                                            <option value="inactive" {{ old('status', $subUser->status) == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                                        </select>
                                        @error('status')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="row">
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="submit" class="btn btn-primary px-4" value="تعديل البيانات" />
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
