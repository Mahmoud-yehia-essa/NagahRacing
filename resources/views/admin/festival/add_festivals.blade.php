@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div class="page-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">اضافة مهرجان جديد</div>
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
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="post" action="{{ route('add.festival.store') }}" enctype="multipart/form-data">
                                @csrf

                                <!--  Name -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">اسم المهرجان</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" />
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>



                                <!--  Name -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">اسم المهرجان بالانجليزية</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" name="name_en" class="form-control" value="{{ old('name_en') }}" />
                                        @error('name_en')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!--  Description -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">الوصف</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" name="des" class="form-control" value="{{ old('des') }}" />
                                        @error('des')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>


                                 <!--  Description -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">الوصف بالانجليزية</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" name="des_en" class="form-control" value="{{ old('des_en') }}" />
                                        @error('des_en')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>



                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">الموقع</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" name="location" class="form-control" value="{{ old('location') }}" />
                                        @error('location')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>


    <!--  Name -->
                          <div class="row mb-3">
    <div class="col-sm-3">
        <h6 class="mb-0">عدد مرات استخدام دبل النقاط</h6>
    </div>
    <div class="col-sm-9 text-secondary">
        <input
            type="number"
            name="count_double_used"
            class="form-control"
            value="{{ old('count_double_used') }}"
            min="0"
            step="1"
            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
        />

        <small class="text-muted d-block mt-1">
            اترك الحقل فارغًا إذا لم يتم استخدام دبل النقاط في هذا المهرجان.
        </small>

        @error('count_double_used')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>


                                  <div class="row mb-3">
				<div class="col-sm-3">
					<h6 class="mb-0">بداية المهرجان</h6>
				</div>
				<div class="form-group col-sm-9 text-secondary">

					{{-- <input type="date" min="{{Carbon\Carbon::now()}}" name="coupon_validity" class="form-control"   /> --}}
                    <input type="date" min="{{ Carbon\Carbon::now()->format('Y-m-d') }}" name="start" class="form-control"   />

                      @error('start')
                        <span class="text-danger">{{ $message }}</span>
                      @enderror
				</div>
			</div>

                         <div class="row mb-3">
				<div class="col-sm-3">
					<h6 class="mb-0">نهاية المهرجان</h6>
				</div>
				<div class="form-group col-sm-9 text-secondary">

					{{-- <input type="date" min="{{Carbon\Carbon::now()}}" name="coupon_validity" class="form-control"   /> --}}
                    <input type="date" min="{{ Carbon\Carbon::now()->format('Y-m-d') }}" name="end" class="form-control"   />

                      @error('end')
                        <span class="text-danger">{{ $message }}</span>
                      @enderror
				</div>
			</div>



{{--
                                   <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">slatitude</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" name="latitude" class="form-control" value="{{ old('latitude') }}" />
                                        @error('latitude')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>


                                 <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">longitude</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" name="longitude" class="form-control" value="{{ old('longitude') }}" />
                                        @error('longitude')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div> --}}


                                <!--  Photo -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">الصورة</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="file" name="photo" class="form-control" id="image" />
                                        @error('photo')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Image Preview -->
                                <div class="row mb-3">
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9 text-secondary">
                                        <img id="showImage" src="{{ url('upload/no_image.jpg') }}" alt="Preview" style="width:100px; height: 100px;">
                                    </div>
                                </div>






                                <!-- Submit Button -->
                                <div class="row">
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="submit" class="btn btn-primary px-4" value="اضافة مهرجان جديد" />
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
