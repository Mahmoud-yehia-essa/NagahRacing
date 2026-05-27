@extends('admin.master_admin')
@section('admin')

{{-- Select2 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
/* شكل select مثل Bootstrap */
.select2-container--default .select2-selection--single {
    height: 38px;
    padding: 6px 12px;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    background-color: #fff;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 24px;
    color: #495057;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}

/* focus */
.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #86b7fe;
    box-shadow: 0 0 0 .25rem rgba(13,110,253,.25);
}

/* عناصر القائمة */
.select2-container--default .select2-results__option {
    padding: 10px;
}

/* Hover */
.select2-container--default .select2-results__option--highlighted {
    background-color: #0d6efd;
    color: #fff;
}

/* لون الهاتف الطبيعي */
.select2-container--default .select2-results__option small {
    color: #6c757d !important;
}

/* لون الهاتف عند hover */
.select2-container--default
.select2-results__option--highlighted small {
    color: #ffffff !important;
}
</style>

<div class="col-lg-12">
    <div class="card">
        <div class="card-body">
            <h4 class="mb-4">تحديد ترتيب المستخدم</h4>

            <form action="{{ route('search.position.result') }}" method="POST">
                @csrf


                {{-- المهرجان --}}
                <div class="mb-3">
                    <label class="form-label">المهرجان</label>
                    <div class="col-sm-9 text-secondary">
                        <select name="festival_id" class="form-control" required>
                            <option value="">اختر المهرجان</option>
                            @foreach($festivals as $festival)
                                <option value="{{ $festival->id }}">{{ $festival->name }}</option>
                            @endforeach
                        </select>

                        @error('festival_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>







                {{-- زر الإرسال --}}
                <div class="mb-3">
                    <button type="submit" class="btn btn-success">
                           عرض ترتيب المستخدمين في هذا المهرجان
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- jQuery --}}
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

{{-- Select2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function () {

    $('#user_id').select2({
        width: '100%',
        dir: "rtl",
        placeholder: 'اختر المستخدم',
        allowClear: true,

        templateResult: function (data) {
            if (!data.id) return data.text;

            let name  = $(data.element).data('name');
            let phone = $(data.element).data('phone');

            return $(`
                <div>
                    <strong>${name}</strong><br>
                                <small dir="ltr" class="phone-ltr">${phone}</small>

                </div>
            `);
        },

        templateSelection: function (data) {
            if (!data.id) return data.text;
            return $(data.element).data('name');
        }
    });

});
</script>

@endsection
