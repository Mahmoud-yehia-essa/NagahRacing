@extends('admin.master_admin')
@section('admin')

<div class="page-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">إدارة العمال</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">كل العمال</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            @if($selectedOwnerId)
                <div class="btn-group">
                    <a href="{{ route('add.camel.worker', ['owner_id' => $selectedOwnerId]) }}" class="btn btn-primary">إضافة عامل جديد</a>
                </div>
            @endif
        </div>
    </div>
    <!--end breadcrumb-->

    <hr/>

    <!-- Owner Selection Dropdown -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <h6 class="mb-0">اختر المالك لعرض العمال:</h6>
                </div>
                <div class="col-md-6">
                    <select name="owner_id" id="owner_selector" class="form-select">
                        <option value="">-- اختر المالك --</option>
                        @foreach($owners as $owner)
                            <option value="{{ $owner->id }}" {{ $selectedOwnerId == $owner->id ? 'selected' : '' }}>
                                {{ $owner->fname }} {{ $owner->lname }} ({{ $owner->phone }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Workers List Card -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>الرقم</th>
                            <th>الصورة</th>
                            <th>الاسم الكامل</th>
                            <th>كود الدخول</th>
                            <th>رقم الهاتف</th>
                            <th>الحالة</th>
                            <th>الاتصال</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workers as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    @if($item->photo_path)
                                        <img onclick="showImageModal(this.src)" src="{{ asset($item->photo_path) }}" style="width: 60px; height: 60px; border-radius: 50%; cursor: pointer; object-fit: cover;" >
                                    @else
                                        <img src="{{ asset('upload/no_image.jpg') }}" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;" >
                                    @endif
                                </td>
                                <td>{{ $item->full_name }}</td>
                                <td><span class="badge bg-dark">{{ $item->login_code }}</span></td>
                                <td>{{ $item->phone }}</td>
                                <td>
                                    @if($item->status == 'active')
                                        <span class="badge bg-success">نشط</span>
                                    @else
                                        <span class="badge bg-danger">غير نشط</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->is_online)
                                        <span class="badge bg-success">متصل</span>
                                    @else
                                        <span class="badge bg-secondary">غير متصل</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->status == 'active')
                                        <a href="{{ route('inactive.camel.worker', $item->id) }}" class="btn btn-primary btn-sm" title="إلغاء تنشيط">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('active.camel.worker', $item->id) }}" class="btn btn-primary btn-sm" title="تنشيط">
                                            <i class="fa-solid fa-eye-slash"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('edit.camel.worker', $item->id) }}" class="btn btn-info btn-sm">تعديل</a>
                                    <a href="{{ route('delete.camel.worker', $item->id) }}" class="btn btn-danger btn-sm" id="delete">حذف</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    @if($selectedOwnerId)
                                        لا يوجد عمال مسجلين لهذا المالك.
                                    @else
                                        الرجاء اختيار مالك لعرض العمال.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content position-relative bg-transparent border-0">
            <!-- Rectangular Close Button -->
            <button type="button" class="btn text-white" data-bs-dismiss="modal" aria-label="Close" style="
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
            <img id="modalImage" src="" class="img-fluid rounded shadow" alt="image">
        </div>
    </div>
</div>

<script type="text/javascript">
    // Handle dropdown selection and page redirect
    document.getElementById('owner_selector').addEventListener('change', function() {
        var ownerId = this.value;
        if (ownerId) {
            window.location.href = "{{ route('all.camel.workers') }}?owner_id=" + ownerId;
        } else {
            window.location.href = "{{ route('all.camel.workers') }}";
        }
    });

    // Handle image modal display
    function showImageModal(src) {
        document.getElementById('modalImage').src = src;
        var myModal = new bootstrap.Modal(document.getElementById('imageModal'));
        myModal.show();
    }
</script>

@endsection
