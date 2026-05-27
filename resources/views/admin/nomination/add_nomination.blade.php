@extends('admin.master_admin')
@section('admin')

<div class="col-lg-12">
    <div class="card">
        <div class="card-body">
            <h4 class="mb-4">إضافة ترشيح جديد</h4>

            <form action="{{ route('store.nomination') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">المستخدم</label>
                    <select name="user_id" class="form-control" required>
                        <option value="">اختر المستخدم</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->fname }}</option>
                        @endforeach
                    </select>
                    @error('user_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">المطية</label>
                    <select name="camal_id" class="form-control" required>
                        <option value="">اختر المطية</option>
                        @foreach($camals as $camal)
                            <option value="{{ $camal->id }}">{{ $camal->name }}</option>
                        @endforeach
                    </select>
                    @error('camal_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">المهرجان</label>
                    <select name="festival_id" id="festival_id" class="form-control" required>
                        <option value="">اختر المهرجان</option>
                        @foreach($festivals as $festival)
                            <option value="{{ $festival->id }}">{{ $festival->name }}</option>
                        @endforeach
                    </select>
                    @error('festival_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">الشوط</label>
                    <select name="round_id" id="round_id" class="form-control" required>
                        <option value="">اختر الشوط</option>
                    </select>
                    @error('round_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">فائز؟</label>
                    <input type="checkbox" name="is_winner" value="1">
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-success">إضافة الترشيح</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
{{-- <script>
$(document).ready(function() {
    $('#festival_id').on('change', function() {
        var festivalId = $(this).val();
        if(festivalId) {
            $.ajax({
                url: '/get-rounds/' + festivalId, // يجب إنشاء هذا الراوت في web.php
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('#round_id').empty();
                    $('#round_id').append('<option value="">اختر الجولة</option>');
                    $.each(data, function(key, value) {
                        $('#round_id').append('<option value="'+ value.id +'">'+ value.name +'</option>');
                    });
                }
            });
        } else {
            $('#round_id').empty();
            $('#round_id').append('<option value="">اختر الجولة</option>');
        }
    });
});
</script> --}}


<script>
$(document).ready(function() {
    $('#festival_id').on('change', function() {
        var festivalId = $(this).val();

        // عرض رسالة الانتظار
        $('#round_id').html('<option>جاري التحميل...</option>');

        if(festivalId) {
            $.ajax({
                url: '/get-rounds/' + festivalId,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('#round_id').empty();
                    $('#round_id').append('<option value="">اختر الجولة</option>');
                    $.each(data, function(key, value) {
                        $('#round_id').append('<option value="'+ value.id +'">'+ value.name +'</option>');
                    });
                },
                error: function() {
                    $('#round_id').html('<option>حدث خطأ أثناء جلب البيانات</option>');
                }
            });
        } else {
            $('#round_id').empty();
            $('#round_id').append('<option value="">اختر الجولة</option>');
        }
    });
});
</script>


@endsection
