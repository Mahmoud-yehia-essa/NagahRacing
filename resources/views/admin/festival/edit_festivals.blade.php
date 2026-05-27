@extends('admin.master_admin')
@section('admin')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div class="page-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">تعديل المهرجان</div>
    </div>
    <!--end breadcrumb-->

    <div class="container">
        <div class="main-body">
            <div class="row">
                <div class="col-lg-10">
                    <div class="card">
                        <div class="card-body">

                            <!-- Validation Errors -->
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="post" action="{{ route('update.festival') }}" enctype="multipart/form-data">
                                @csrf

                                <!-- Hidden Inputs -->
                                <input type="hidden" name="id" value="{{ $festival->id }}">
                                <input type="hidden" name="old_image" value="{{ $festival->photo }}">

                                <!--  Name -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">اسم المهرجان</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" name="name" class="form-control" value="{{ old('name', $festival->name) }}" />
                                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!--  Name English -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">اسم المهرجان بالانجليزية</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" name="name_en" class="form-control" value="{{ old('name_en', $festival->name_en) }}" />
                                        @error('name_en') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!--  Description -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">الوصف</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" name="des" class="form-control" value="{{ old('des', $festival->des) }}" />
                                        @error('des') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!--  Description English -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">الوصف بالانجليزية</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" name="des_en" class="form-control" value="{{ old('des_en', $festival->des_en) }}" />
                                        @error('des_en') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!--  Location -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">الموقع</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" name="location" class="form-control" value="{{ old('location', $festival->location) }}" />
                                        @error('location') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>


                                  <div class="row mb-3">
                                    <div class="col-sm-3">
        <h6 class="mb-0">عدد مرات استخدام دبل النقاط</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input disabled type="text"  name="count_double_used" class="form-control" value="{{ old('count_double_used', $festival->count_double_used == null ? 'لا يوجد استخدام لدبل النقاط في هذا المهرجان' : $festival->count_double_used) }}" />
                                        @error('count_double_used') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- Start Date -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">بداية المهرجان</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="date" name="start" class="form-control"
       value="{{ old('start', optional($festival->start)->format('Y-m-d')) }}" />

                                        @error('start') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- End Date -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">نهاية المهرجان</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">

                                             <input type="date" name="end" class="form-control"
       value="{{ old('end', optional($festival->end)->format('Y-m-d')) }}" />
                                        @error('end') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- <!-- Latitude -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Latitude</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" name="latitude" class="form-control" value="{{ old('latitude', $festival->latitude) }}" />
                                        @error('latitude') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- Longitude -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">Longitude</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" name="longitude" class="form-control" value="{{ old('longitude', $festival->longitude) }}" />
                                        @error('longitude') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div> --}}

                                <!--  Photo -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">الصورة</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="file" name="photo" class="form-control" id="image" />
                                        @error('photo') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- Image Preview -->
                                <div class="row mb-3">
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9 text-secondary">
                                        <img id="showImage" src="{{ (!empty($festival->photo)) ? url($festival->photo) : url('upload/no_image.jpg') }}" alt="Preview" style="width:100px; height:100px;">
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="row">
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="submit" class="btn btn-success px-4" value="تحديث المهرجان" />
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
