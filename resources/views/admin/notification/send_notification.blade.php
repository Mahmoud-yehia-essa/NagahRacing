@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div class="col-lg-16">
    <div class="card">

           <form action="{{ route('send.notification.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
        <div class="card-body">



            <div class="row mb-3">
                <div class="col-sm-3">
                    <h6 class="mb-0">عنوان الاشعار</h6>
                </div>
                <div class="col-sm-9 text-secondary">
                    <input type="text" class="form-control" name="title" value="{{ old('title') }}">
                 @error('title') <span class="text-danger">{{ $message }}</span> @enderror

                </div>
            </div>

                 <!-- Profile Picture -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">الصورة</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="Photo" type="file" id="image" class="form-control" />
                                    @error('Photo') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Profile Picture Preview -->
                            <div class="row mb-3">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    <img id="showImage" src="{{ url('upload/no_image.jpg') }}" alt="Admin" width="110">
                                </div>
                            </div>


            <div class="row mb-3">
                <div class="col-sm-3">
                    <h6 class="mb-0">الوصف</h6>
                </div>
                <div class="col-sm-9 text-secondary">
                    <textarea id="elm1" name="description"></textarea>
                    @error('description') <span class="text-danger">{{ $message }}</span> @enderror


                </div>
            </div>


<div class="row mb-3">
    <div class="col-sm-3">
        <h6 class="mb-0">ارسال الاشعار الى</h6>
    </div>
    <div class="col-sm-9 text-secondary">
        {{-- <select name="user_select" class="form-select" aria-label="Default select example">
            <option selected value="all">الكل</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}">{{ $user->Name }}</option>
            @endforeach
        </select> --}}

        		<select class="form-select select2-hidden-accessible" name="user_select[]" id="multiple-select-field" data-placeholder="اختر" multiple="" data-select2-id="select2-data-multiple-select-field" tabindex="-1" aria-hidden="true">


                                     <option  value="all">الكل</option>
            @foreach ($users as $user)
                									<option value="{{ $user->id }}"  data-select2-id="{{ $user->id }}">{{ $user->fname }} {{ $user->lname }} - {{ $user->phone }}</option>

            @endforeach


								</select>
                                @error('user_select')
    <div class="text-danger">{{ $message }}</div>
@enderror
    </div>
</div>












            <div class="row">
                <div class="col-sm-3"></div>
                <div class="col-sm-9 text-secondary">
                    {{-- <input type="submit" class="btn btn-primary px-4" value="ارسال الاشعار"> --}}

                    <button type="submit" id="sendButton" class="btn btn-primary px-4">
    ارسال الاشعار
</button>
                </div>
            </div>




        </div>
           </form>
    </div>

</div>

<script type="text/javascript">
    $(document).ready(function(){
        $('#image').change(function(e){
            var reader = new FileReader();
            reader.onload = function(e){
                $('#showImage').attr('src',e.target.result);
            }
            reader.readAsDataURL(e.target.files['0']);
        });
    });
</script>

<script>
    $(document).ready(function () {
        const $select = $('#multiple-select-field');

        // Initialize Select2
        $select.select2({
            theme: 'bootstrap-5',
            placeholder: "اختر المستخدمين"
        });

        // Listen to change event
        $select.on('change', function () {
            const selected = $(this).val(); // Get selected values

            // إذا تم اختيار "الكل"
            if (selected.includes("all") && selected.length > 1) {
                // اجعل القيمة فقط "الكل"
                $(this).val(["all"]).trigger("change");
            }

            // إذا كان "الكل" محدد، واختر خيار آخر → احذف "الكل"
            if (!selected.includes("all") && selected.length > 0) {
                const newValues = selected.filter(value => value !== "all");
                if (newValues.length !== selected.length) {
                    $(this).val(newValues).trigger("change");
                }
            }
        });
    });




    $("form").on("submit", function() {

        // تعطيل الزر بعد الضغط مباشرة
        $("#sendButton").prop("disabled", true);
        $("#sendButton").text("جاري الإرسال...");

    });


</script>



@endsection
