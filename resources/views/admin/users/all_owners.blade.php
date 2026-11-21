@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">كل الملاك</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">

        </nav>
    </div>
    <div class="ms-auto">
        <div class="btn-group">
            <a href="{{route('add.user')}}" >

<button type="button" class="btn btn-primary">

    اضافة مالك

</button>
</a>


        </div>
    </div>
</div>
<!--end breadcrumb-->

<hr/>

{{-- إعدادات تسجيل الملاك الجدد والتواصل عبر الواتساب --}}
<div class="card mb-4 border-primary">
    <div class="card-header bg-light d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h6 class="mb-0 fw-bold">
            <i class="bx bx-shield-quarter me-1 text-primary"></i>
            إعدادات تسجيل الملاك الجدد في التطبيق
        </h6>
        @if(!empty($appSettings->stop_owner_registration))
            <span class="badge bg-warning text-dark px-3 py-2">التفعيل التلقائي موقوف — الملاك الجدد بحاجة لتفعيل الإدارة</span>
        @else
            <span class="badge bg-success px-3 py-2">التسجيل مفتوح — يتم تفعيل الملاك الجدد تلقائياً</span>
        @endif
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('owners.registration.settings') }}">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <div class="form-check form-switch p-3 border rounded bg-light">
                        <input class="form-check-input ms-0 me-2"
                               type="checkbox"
                               role="switch"
                               id="stop_owner_registration"
                               name="stop_owner_registration"
                               value="1"
                               {{ !empty($appSettings->stop_owner_registration) ? 'checked' : '' }}
                               style="width: 3em; height: 1.5em; cursor: pointer;">
                        <label class="form-check-label fw-bold ms-2" for="stop_owner_registration" style="cursor: pointer;">
                            وقف التفعيل التلقائي للملاك الجدد
                        </label>
                        <div class="text-muted small mt-1">
                            عند تفعيل هذا الخيار، أي مالك جديد غير مسجل يقوم بالتسجيل في التطبيق سيكون حسابه <strong>غير مفعل</strong> حتى يتم تفعيله من الجدول أدناه.
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="whatsapp_contact" class="form-label fw-bold">رقم الواتساب للتواصل (لتفعيل الحساب)</label>
                    <input type="text"
                           dir="ltr"
                           class="form-control @error('whatsapp_contact') is-invalid @enderror"
                           id="whatsapp_contact"
                           name="whatsapp_contact"
                           placeholder="+96551673464"
                           value="{{ old('whatsapp_contact', $appSettings->whatsapp_contact ?? '+96551673464') }}">
                    @error('whatsapp_contact')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="inactive_owner_message" class="form-label fw-bold">رسالة الحساب غير المفعل (اختياري)</label>
                    <input type="text"
                           class="form-control @error('inactive_owner_message') is-invalid @enderror"
                           id="inactive_owner_message"
                           name="inactive_owner_message"
                           placeholder="حسابك غير مفعل حالياً، الرجاء التواصل مع الإدارة عبر الواتساب لتفعيل الحساب."
                           value="{{ old('inactive_owner_message', $appSettings->inactive_owner_message ?? '') }}">
                    @error('inactive_owner_message')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-save me-1"></i> حفظ الإعدادات
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered" style="width:100%">
                <thead>
<tr>
<th>الرقم</th>
<th>الإسم</th>
<th>رقم الهاتف</th>
<th>الدولة</th>
<th>الحالة</th>

<th>تاريخ التسجيل</th>

<th> الصورة</th>
<th>الاجراء</th>
</tr>
</thead>
<tbody>
@foreach($users as $key => $item)
<tr>
<td> {{ $key+1 }} </td>
<td>{{ $item->fname }}</td>
<td>{{ $item->phone }}</td>
<td style="font-size: 32px; " >
    <p >{{ $item->country_flag }}</p>
</td>
<td>
    @if($item->status == 'active')
        <span class="badge bg-success px-3 py-2">مفعل</span>
    @else
        <span class="badge bg-danger px-3 py-2">غير مفعل</span>
    @endif
</td>

<td>{{ $item->created_at ? $item->created_at->diffForHumans() : 'لم يتم التحديد' }}</td>


<td> <img onclick="showImageModal(this.src)" class="rounded-circle"  src="{{  (!empty($item->photo) && $item->photo != 'non' )  ? url('upload/user_images/'.$item->photo):url('upload/no_image.jpg') }}" style="width: 50px; height:50px; cursor: pointer; border: 2px solid #0aa2dd;" >  </td>

<td>

@if($item->status == 'active')
<a href="{{ route('inactive.user',$item->id) }}" class="btn btn-warning" title="ايقاف التفعيل"> <i class="fa-solid fa-thumbs-down"></i> </a>
@else
<a href="{{ route('active.user',$item->id) }}" class="btn btn-success" title="تفعيل الحساب"> <i class="fa-solid fa-thumbs-up"></i> </a>
@endif
<a href="{{ route('edit.user',$item->id) }}" class="btn btn-info" title="Edit Data"> <i class="fa fa-pencil"></i> </a>

<a href="{{ route('delete.user',$item->id) }}" class="btn btn-danger" id="delete" title="Delete Data" ><i class="fa fa-trash"></i></a>

</td>
</tr>
@endforeach


</tbody>
<tfoot>
<tr>
    <th>الرقم</th>
    <th>الإسم</th>
    <th>رقم الهاتف</th>
    <th>الدولة</th>
    <th>الحالة</th>
    <th>تاريخ التسجيل</th>
    <th> الصورة</th>
    <th>الاجراء</th>
</tr>
</tfoot>
</table>
        </div>
    </div>
</div>



<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content position-relative bg-transparent border-0">

        <!-- Rectangular Close Button -->
        <button type="button"
                class="btn text-white"
                data-bs-dismiss="modal"
                aria-label="Close"
                style="
                  position: absolute;
                  top: 15px;
                  right: 15px;
                  background-color: black;
                  font-size: 30px;
                  padding: 1px 10px;
                  border-radius: 8px;
                  z-index: 1055;
                ">
            &times;
        </button>

        <!-- Image -->
        <img id="modalImage" src="" class="img-fluid rounded shadow"  alt="image">
      </div>
    </div>
  </div>



  <script>
    function showImageModal(src) {
        document.getElementById('modalImage').src = src;
        var myModal = new bootstrap.Modal(document.getElementById('imageModal'));
        myModal.show();
    }
</script>

@endsection
