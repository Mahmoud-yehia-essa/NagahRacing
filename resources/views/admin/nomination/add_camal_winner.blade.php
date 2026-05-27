@extends('admin.master_admin')
@section('admin')

<div class="col-lg-12">
    <div class="card">
        <div class="card-body">
            <h4 class="mb-4"> تحديد المطية الفائزة</h4>

            <form action="{{ route('add.round.winner.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">المهرجان</label>
                    <div class="col-sm-9 text-secondary">
                        <select name="festival_id" id="festival_id" class="form-control" required>
                            <option value="">اختر المهرجان</option>
                            @foreach($festivals as $festival)
                                <option value="{{ $festival->id }}">{{ $festival->name }}</option>
                            @endforeach
                        </select>
                        @error('festival_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">الشوط</label>
                    <div class="col-sm-9 text-secondary">
                        <select name="round_id" id="round_id" class="form-control" required>
                            <option value="">اختر الشوط</option>
                        </select>
                        @error('round_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">المطية</label>
                    <div class="col-sm-9 text-secondary">
                        <select name="camal_id" id="camal_id" class="form-control" required>
                            <option value="">اختر المطية</option>
                        </select>
                        @error('camal_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-success">إضافة المطية الفائزة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
$(document).ready(function() {
    const days = ['الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت'];

    // عند اختيار المهرجان
    $('#festival_id').on('change', function() {
        var festivalId = $(this).val();

        $('#round_id').html('<option>جاري التحميل...</option>');
        $('#camal_id').html('<option value="">اختر المطية</option>');

        if(festivalId) {
            $.ajax({
                url: '/get-rounds/' + festivalId,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('#round_id').empty();
                    $('#round_id').append('<option value="">اختر الجولة</option>');

                    $.each(data, function(key, value) {
                        // تنسيق التاريخ بالشكل المطلوب
                        let date = new Date(value.start);
                        let dayName = days[date.getDay()];
                        let formattedDate = ("0" + date.getDate()).slice(-2) + "-" +
                                            ("0" + (date.getMonth() + 1)).slice(-2) + "-" +
                                            date.getFullYear();

                        let displayText = `${dayName} ${formattedDate} - ${value.name} - ${value.round_type}`;
                        $('#round_id').append(`<option value="${value.id}">${displayText}</option>`);
                    });
                },
                error: function() {
                    $('#round_id').html('<option>حدث خطأ أثناء جلب البيانات</option>');
                }
            });
        } else {
            $('#round_id').empty().append('<option value="">اختر الجولة</option>');
            $('#camal_id').empty().append('<option value="">اختر المطية</option>');
        }
    });

    // عند اختيار الجولة
    $('#round_id').on('change', function() {
        var roundId = $(this).val();
        $('#camal_id').html('<option>جاري التحميل...</option>');

        if(roundId) {
            $.ajax({
                url: '/get-camals-by-round/' + roundId,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('#camal_id').empty();
                    $('#camal_id').append('<option value="">اختر المطية</option>');
                    $.each(data, function(key, value) {
                        $('#camal_id').append('<option value="'+ value.id +'">'+ value.name +' (  المالك ' + value.camel_owner_name + ')'+ '</option>');
                    });
                },
                error: function() {
                    $('#camal_id').html('<option>حدث خطأ أثناء جلب المطايا</option>');
                }
            });
        } else {
            $('#camal_id').empty().append('<option value="">اختر المطية</option>');
        }
    });
});
</script>

@endsection
