@extends('admin.master_admin')
@section('admin')

<div class="col-lg-12">
    <div class="card">
        <div class="card-body">
            <h4 class="mb-4">إضافة بث مباشر جديد</h4>

            <form method="POST" action="{{ route('add.live.broadcast.store') }}">
                @csrf

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">العنوان</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" class="form-control" name="title" value="{{ old('title') }}">
                        @error('title') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">Title</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" class="form-control" name="title_en" value="{{ old('title_en') }}">
                        @error('title_en') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- الوصف --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">رابط البث المباشر</h6></div>
                    <div class="col-sm-9 text-secondary">
                        {{-- <textarea id="more_des_ar" name="more_des">{{ old('more_des') }}</textarea> --}}

                         <input type="text" class="form-control" name="more_des" value="{{ old('more_des') }}">
                        @error('more_des') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">Description</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea id="more_des_en" name="more_des_en">{{ old('more_des_en') }}</textarea>
                    </div>
                </div> --}}

                <div class="row">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="submit" class="btn btn-primary px-4" value="إضافة">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CKEditor -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    ClassicEditor.create(document.querySelector('#more_des_ar'), {
        toolbar: ['bold','italic','underline','link','bulletedList','numberedList','blockQuote'],
    }).then(editor => {
        editor.editing.view.change(writer => {
            writer.setAttribute('dir', 'rtl', editor.editing.view.document.getRoot());
        });
    }).catch(error => { console.error(error); });

    ClassicEditor.create(document.querySelector('#more_des_en'), {
        toolbar: ['bold','italic','underline','link','bulletedList','numberedList','blockQuote'],
    }).then(editor => {
        editor.editing.view.change(writer => {
            writer.setAttribute('dir', 'ltr', editor.editing.view.document.getRoot());
        });
    }).catch(error => { console.error(error); });

});
</script>

@endsection
