@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div class="col-lg-12">
    <div class="card">
        <div class="card-body">

            <form method="POST" action="{{ route('edit.news.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{ $value->id }}">
                <input type="hidden" name="old_image" value="{{ $value->photo }}">

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">العنوان</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" class="form-control" name="title" value="{{ old('title', $value->title) }}">
                        @error('title') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">Title</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" class="form-control" name="title_en" value="{{ old('title_en', $value->title_en) }}">
                        @error('title_en') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الوصف</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea class="form-control" name="des" rows="4">{{ old('des', $value->des) }}</textarea>
                        @error('des') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">Description</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea class="form-control" name="des_en" rows="4">{{ old('des_en', $value->des_en) }}</textarea>
                        @error('des_en') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>


                {{-- Detailed Description Arabic --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الوصف بالتفصيل</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea id="more_des_ar" name="more_des">{{ old('more_des', $value->more_des) }}</textarea>
                    </div>
                </div>

                {{-- Detailed Description English --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الوصف بالتفصيل بالانجليزية</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea id="more_des_en" name="more_des_en">{{ old('more_des_en', $value->more_des_en) }}</textarea>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الصورة</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="file" name="photo" class="form-control" id="image">
                        <img id="showImage" src="{{ !empty($value->photo) ? url($value->photo) : url('upload/no_image.jpg') }}" width="100" class="mt-2">
                        @error('photo') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="submit" class="btn btn-primary px-4" value="حفظ التغييرات">
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $('#image').change(function(e){
            let reader = new FileReader();
            reader.onload = (e) => $('#showImage').attr('src', e.target.result);
            reader.readAsDataURL(e.target.files[0]);
        });
    });
</script>


<!-- CKEditor 5 Classic -->
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
