@extends('admin.master_admin')
@section('admin')

<div class="page-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">إدارة المستفيدين</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">كل المستفيدين</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            @if($selectedOwnerId)
                <div class="btn-group">
                    <a href="{{ route('add.sub.user', ['owner_id' => $selectedOwnerId]) }}" class="btn btn-primary">إضافة مستفيد جديد</a>
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
                    <h6 class="mb-0">اختر المالك لعرض المستفيدين:</h6>
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
                            <th>الاسم الكامل</th>
                            <th>كود الدخول</th>
                            <th>رقم الهاتف</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subUsers as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $item->full_name }}</td>
                                <td><span class="badge bg-dark">{{ $item->login_code }}</span></td>
                                <td>{{ $item->phone ?? 'لا يوجد' }}</td>
                                <td>
                                    @if($item->status == 'active')
                                        <span class="badge bg-success">نشط</span>
                                    @else
                                        <span class="badge bg-danger">غير نشط</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->status == 'active')
                                        <a href="{{ route('inactive.sub.user', $item->id) }}" class="btn btn-primary btn-sm" title="إلغاء تنشيط">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('active.sub.user', $item->id) }}" class="btn btn-primary btn-sm" title="تنشيط">
                                            <i class="fa-solid fa-eye-slash"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('edit.sub.user', $item->id) }}" class="btn btn-info btn-sm">تعديل</a>
                                    <a href="{{ route('delete.sub.user', $item->id) }}" class="btn btn-danger btn-sm" id="delete">حذف</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    @if($selectedOwnerId)
                                        لا يوجد مستفيدين مسجلين لهذا المالك.
                                    @else
                                        الرجاء اختيار مالك لعرض المستفيدين.
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

<script type="text/javascript">
    // Handle dropdown selection and page redirect
    document.getElementById('owner_selector').addEventListener('change', function() {
        var ownerId = this.value;
        if (ownerId) {
            window.location.href = "{{ route('all.sub.users') }}?owner_id=" + ownerId;
        } else {
            window.location.href = "{{ route('all.sub.users') }}";
        }
    });
</script>

@endsection
