@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div class="page-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">تعديل بيانات العامل</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('all.camel.workers', ['owner_id' => $worker->owner_id]) }}">إدارة العمال</a></li>
                    <li class="breadcrumb-item active" aria-current="page">تعديل عامل</li>
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

                            <form method="post" action="{{ route('update.camel.worker') }}" enctype="multipart/form-data">
                                @csrf
                                
                                <input type="hidden" name="id" value="{{ $worker->id }}" />

                                <!-- Select Owner -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">المالك</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <select name="owner_id" class="form-select @error('owner_id') is-invalid @enderror">
                                            <option value="">-- اختر المالك --</option>
                                            @foreach($owners as $owner)
                                                <option value="{{ $owner->id }}" {{ $worker->owner_id == $owner->id ? 'selected' : '' }}>
                                                    {{ $owner->fname }} {{ $owner->lname }} ({{ $owner->phone }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('owner_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Worker Full Name -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">الاسم الكامل</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name', $worker->full_name) }}" placeholder="أدخل اسم العامل الكامل" />
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
                                        <input type="text" class="form-control" value="{{ $worker->login_code }}" readonly disabled />
                                    </div>
                                </div>

                                <!-- Phone Number -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">رقم الهاتف</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $worker->phone) }}" placeholder="أدخل رقم هاتف العامل" />
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
                                            <option value="active" {{ old('status', $worker->status) == 'active' ? 'selected' : '' }}>نشط</option>
                                            <option value="inactive" {{ old('status', $worker->status) == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                                        </select>
                                        @error('status')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Connection Status -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">حالة الاتصال</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <select name="is_online" class="form-select @error('is_online') is-invalid @enderror">
                                            <option value="0" {{ old('is_online', $worker->is_online) == '0' ? 'selected' : '' }}>غير متصل</option>
                                            <option value="1" {{ old('is_online', $worker->is_online) == '1' ? 'selected' : '' }}>متصل</option>
                                        </select>
                                        @error('is_online')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Photo -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">صورة العامل</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" id="image" />
                                        @error('photo')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Image Preview -->
                                <div class="row mb-3">
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9 text-secondary">
                                        @if($worker->photo_path)
                                            <img id="showImage" src="{{ asset($worker->photo_path) }}" alt="Preview" style="width:100px; height: 100px; border-radius: 50%; object-fit: cover;">
                                        @else
                                            <img id="showImage" src="{{ url('upload/no_image.jpg') }}" alt="Preview" style="width:100px; height: 100px; border-radius: 50%; object-fit: cover;">
                                        @endif
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

                    <!-- jQuery for Image Preview -->
                    <script type="text/javascript">
                        $(document).ready(function(){
                            $('#image').change(function(e){
                                var reader = new FileReader();
                                reader.onload = function(e){
                                    $('#showImage').attr('src', e.target.result);
                                }
                                reader.readAsDataURL(e.target.files[0]);
                            });
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
