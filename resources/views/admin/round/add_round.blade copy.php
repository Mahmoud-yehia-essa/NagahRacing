@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div class="col-lg-16">
    <div class="card">
        <div class="card-body">
            <h4 class="mb-4">إضافة جولة جديدة</h4>

            <form method="POST" action="{{ route('add.round.store') }}">
                @csrf

                {{-- اختيار المهرجان المرتبط --}}
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">المهرجان</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <select name="festival_id" class="form-control" required>
                            <option value="">اختر المهرجان</option>
                            @foreach($festivals as $festival)
                                <option value="{{ $festival->id }}">{{ $festival->name }}</option>
                            @endforeach
                        </select>
                        @error('festival_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- اسم الجولة --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">اسم الجولة</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" name="name" class="form-control" />
                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- اسم الجولة انجليزي --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">اسم الجولة (EN)</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" name="name_en" class="form-control" />
                        @error('name_en') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- الوصف --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الوصف</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="des" class="form-control"></textarea>
                        @error('des') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- الوصف انجليزي --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الوصف (EN)</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="des_en" class="form-control"></textarea>
                        @error('des_en') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- رقم الجولة --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">رقم الجولة</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="number" name="round_number" class="form-control" />
                        @error('round_number') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- بداية الجولة --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">تاريخ البداية</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="date" name="start" class="form-control" />
                        @error('start') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- نهاية الجولة --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">تاريخ النهاية</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="date" name="end" class="form-control" />
                        @error('end') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- نوع الجولة --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">نوع الجولة</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <select name="round_type" id="round_type" class="form-control" required>
                            <option value="">اختر النوع</option>
                            <option value="بكار">بكار</option>
                            <option value="قعدان">قعدان</option>
                        </select>
                        @error('round_type') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>



                {{-- <div class="row mb-3">
    <div class="col-sm-3"><h6 class="mb-0">الفئة العمرية</h6></div>
    <div class="col-sm-9 text-secondary">
        <select name="age_name" id="age_name" class="form-control">
            <option value="">اختر الفئة العمرية</option>
            <option value="الحقايق">الحقايق</option>
            <option value="اللقايا">اللقايا</option>
            <option value="الجذاع">الجذاع</option>
            <option value="الثنايا">الثنايا</option>
            <option value="زمول">زمول</option>
            <option value="الحيل">الحيل</option>
        </select>
        @error('age_name') <span class="text-danger">{{ $message }}</span> @enderror
    </div>
</div> --}}


<div class="row mb-3">
    <div class="col-sm-3"><h6 class="mb-0">الفئة العمرية</h6></div>
    <div class="col-sm-9 text-secondary">
        {{-- <select name="age_name" id="age_name" class="form-control">
            <option value="all" selected>الكل</option>
            <option value="الحقايق">الحقايق</option>
            <option value="اللقايا">اللقايا</option>
            <option value="الجذاع">الجذاع</option>
            <option value="الثنايا">الثنايا</option>
            <option value="زمول">زمول</option>
            <option value="الحيل">الحيل</option>
        </select> --}}

        <select name="age_name" id="age_name" class="form-control">
    <option value="all" selected>الكل</option>
    <option value="الحقايق">الحقايق</option>
    <option value="اللقايا">اللقايا</option>
    <option value="الجذاع">الجذاع</option>
    <option value="الثنايا">الثنايا</option>
    <option value="زمول">زمول</option>
    <option value="الحيل">الحيل</option>
</select>
        @error('age_name') <span class="text-danger">{{ $message }}</span> @enderror
    </div>
</div>


                {{-- جدول المطايا --}}
                {{-- <div class="row mb-3">
                    <div class="col-sm-12">
                        <h6 class="mb-0">المطايا المشاركة</h6>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>اختيار</th>
                                    <th>اسم المطية</th>
                                    <th>رقم</th>
                                </tr>
                            </thead>
                            <tbody id="camalsTableBody">
                                <tr>
                                    <td colspan="3" class="text-center">اختر نوع الجولة أولاً</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div> --}}


                <div class="row mb-3">
    <div class="col-sm-3">
        <h6 class="mb-0">المطايا المشاركة</h6>
    </div>
    <div class="col-sm-9 text-secondary">
        {{-- <table class="table table-bordered">
            <thead>
                <tr>
                    <th>اختيار</th>
                    <th>اسم المطية</th>
                    <th>رقم</th>
                </tr>
            </thead>
            <tbody id="camalsTableBody">
                <tr>
                    <td colspan="3" class="text-center">اختر نوع الجولة أولاً</td>
                </tr>
            </tbody>
        </table> --}}

        <table class="table table-bordered">
    <thead>
        <tr>
            <th>
                <input type="checkbox" id="selectAllCamals">
                اختيار الكل
            </th>
            <th>اسم المطية</th>
            <th>المالك</th>
            <th>الفئة العمرية</th>


            <th>رقم التسجيل</th>
        </tr>
    </thead>
    <tbody id="camalsTableBody">
        <tr>
            <td colspan="5" class="text-center">اختر نوع الجولة أولاً</td>
        </tr>
    </tbody>
</table>
    </div>
</div>

                {{-- زر الإرسال --}}
                <div class="row">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="submit" class="btn btn-primary px-4" value="إضافة الجولة">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- AJAX لجلب المطايا --}}


{{-- <script>
$(document).ready(function() {

    // عند اختيار نوع الجولة -> جلب المطايا
    $('#round_type').on('change', function() {
        var gender = $(this).val();
        if (gender) {
            $.ajax({
                url: "{{ url('/get-camals') }}/" + gender,
                type: "GET",
                dataType: "json",
                success: function(data) {
                    $('#camalsTableBody').empty();
                    if (data.length > 0) {
                        $.each(data, function(key, camal) {
                            $('#camalsTableBody').append(
                                '<tr>'+
                                    '<td><input type="checkbox" class="camalCheckbox" name="camals['+ camal.id +'][selected]" value="1"></td>'+
                                    '<td>'+ camal.name +'</td>'+
                                   '<td>'+ (camal.user ? camal.user.fname : 'غير محدد') +'</td>'+

                                                                        '<td>'+ camal.age_name +'</td>'+


                                    '<td><input type="text" name="camals['+ camal.id +'][number]" class="form-control"></td>'+
                                '</tr>'
                            );
                        });
                    } else {
                        $('#camalsTableBody').append('<tr><td colspan="5" class="text-center">لا توجد مطايا لهذا النوع</td></tr>');
                    }
                }
            });
        } else {
            $('#camalsTableBody').html('<tr><td colspan="4" class="text-center">اختر نوع الجولة أولاً</td></tr>');
        }
    });

    // زر اختيار الكل
    $(document).on('change', '#selectAllCamals', function () {
        var isChecked = $(this).is(':checked');
        $('#camalsTableBody input[type="checkbox"]').prop('checked', isChecked);
    });

    // تحديث حالة اختيار الكل عند تغيير أي مطية
    $(document).on('change', '.camalCheckbox', function () {
        var allChecked = $('.camalCheckbox').length === $('.camalCheckbox:checked').length;
        $('#selectAllCamals').prop('checked', allChecked);
    });

});
</script> --}}


{{-- <script>
$(document).ready(function() {

    function loadCamals() {
        var gender = $('#round_type').val();
        var age = $('#age_name').val();

        if (gender && age) {
            $.ajax({
                url: "{{ url('/get-camals') }}",
                type: "GET",
                data: { gender: gender, age: age },
                dataType: "json",
                success: function(data) {
                    $('#camalsTableBody').empty();
                    if (data.length > 0) {
                        $.each(data, function(key, camal) {
                            $('#camalsTableBody').append(
                                '<tr>'+
                                    '<td><input type="checkbox" class="camalCheckbox" name="camals['+ camal.id +'][selected]" value="1"></td>'+
                                    '<td>'+ camal.name +'</td>'+
                                    '<td>'+ camal.user.fname +'</td>'+
                                    '<td>'+ camal.age_name +'</td>'+
                                    '<td><input type="text" name="camals['+ camal.id +'][number]" class="form-control"></td>'+
                                '</tr>'
                            );
                        });
                    } else {
                        $('#camalsTableBody').append('<tr><td colspan="5" class="text-center">لا توجد مطايا لهذا النوع</td></tr>');
                    }
                }
            });
        } else {
            $('#camalsTableBody').html('<tr><td colspan="5" class="text-center">اختر النوع والفئة العمرية أولاً</td></tr>');
        }
    }

    // استدعاء عند تغيير النوع أو الفئة
    $('#round_type, #age_name').on('change', loadCamals);

});
</script> --}}


{{-- <script>
$(document).ready(function() {

    function loadCamals() {
        var gender = $('#round_type').val();
        var age = $('#age_name').val();

        if (gender) {
            $.ajax({
                url: "{{ url('/get-camals') }}",
                type: "GET",
                data: { gender: gender, age: age },
                dataType: "json",
                success: function(data) {
                    $('#camalsTableBody').empty();
                    if (data.length > 0) {
                        $.each(data, function(key, camal) {
                            $('#camalsTableBody').append(
                                '<tr>'+
                                    '<td><input type="checkbox" class="camalCheckbox" name="camals['+ camal.id +'][selected]" value="1"></td>'+
                                    '<td>'+ camal.name +'</td>'+
                                    '<td>'+ (camal.user ? camal.user.fname : '') +'</td>'+
                                    '<td>'+ camal.age_name +'</td>'+
                                    '<td><input type="text" name="camals['+ camal.id +'][number]" class="form-control"></td>'+
                                '</tr>'
                            );
                        });
                    } else {
                        $('#camalsTableBody').append('<tr><td colspan="5" class="text-center">لا توجد مطايا لهذا النوع</td></tr>');
                    }
                }
            });
        } else {
            $('#camalsTableBody').html('<tr><td colspan="5" class="text-center">اختر نوع الجولة أولاً</td></tr>');
        }
    }

    // استدعاء عند تغيير النوع أو الفئة
    $('#round_type, #age_name').on('change', loadCamals);

});
</script> --}}


<script>
$(document).ready(function() {

    function loadCamals() {
        var gender = $('#round_type').val();
        var age = $('#age_name').val();

        if (!gender) {
            $('#camalsTableBody').html('<tr><td colspan="5" class="text-center">اختر نوع الجولة أولاً</td></tr>');
            return;
        }

        $.ajax({
            url: "{{ url('/get-camals') }}/" + gender,
            type: "GET",
            data: { age: age }, // إرسال العمر كـ باراميتر
            dataType: "json",
            success: function(data) {
                $('#camalsTableBody').empty();
                if (data.length > 0) {
                    $.each(data, function(key, camal) {
                        $('#camalsTableBody').append(
                            '<tr>'+
                                '<td><input type="checkbox" class="camalCheckbox" name="camals['+ camal.id +'][selected]" value="1"></td>'+
                                '<td>'+ camal.name +'</td>'+
                                '<td>'+ (camal.user ? camal.user.fname : '') +'</td>'+
                                '<td>'+ camal.age_name +'</td>'+
                                '<td><input type="text" name="camals['+ camal.id +'][number]" class="form-control"></td>'+
                            '</tr>'
                        );
                    });
                } else {
                    $('#camalsTableBody').append('<tr><td colspan="5" class="text-center">لا توجد مطايا لهذا النوع</td></tr>');
                }
            },
            error: function(err) {
                console.log(err);
                $('#camalsTableBody').html('<tr><td colspan="5" class="text-center">حدث خطأ أثناء جلب البيانات</td></tr>');
            }
        });
    }

    // استدعاء عند تغيير النوع أو الفئة
    $('#round_type, #age_name').on('change', loadCamals);

    // زر اختيار الكل
    $(document).on('change', '#selectAllCamals', function () {
        var isChecked = $(this).is(':checked');
        $('#camalsTableBody input[type="checkbox"]').prop('checked', isChecked);
    });

    // تحديث حالة اختيار الكل عند تغيير أي مطية
    $(document).on('change', '.camalCheckbox', function () {
        var allChecked = $('.camalCheckbox').length === $('.camalCheckbox:checked').length;
        $('#selectAllCamals').prop('checked', allChecked);
    });

});
</script>




<style>
    /* عرض صغير لخانة الاختيار */
    table th:first-child,
    table td:first-child {
        width: 120px;
        /* text-align: center; */
    }

    /* عرض صغير لخانة الرقم */
    table th:last-child,
    table td:last-child {
        width: 200px;
        text-align: center;
    }
</style>

@endsection
