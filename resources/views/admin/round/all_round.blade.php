@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3"> كل الأشواط</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
        </nav>
    </div>
    <div class="ms-auto">
        <div class="btn-group">
            <a href="{{ route('add.round') }}">
                <button type="button" class="btn btn-primary">
                    إضافة شوط جديدة
                </button>
            </a>
        </div>
    </div>
</div>
<!--end breadcrumb-->

<hr/>
<div class="card">

    <div class="card-body">

{{--

                        <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">اختر المهرجان</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">


                                        <select  name="role" class="form-select" aria-label="Default select example">
                                            <option selected="all" value="non">الكل</option>

                                        <option value="owner">مالك</option>
                                         <option value="admin">مدير</option>
                                        <option  value="user">مستخدم</option>




                                        </select>

                                        @error('role') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div> --}}
        <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>الرقم</th>
                        <th>اسم الشوط</th>
                        <th>المهرجان</th>
                        <th>نوع الشوط</th>
                        <th>تاريخ البداية</th>
                        <th>تاريخ النهاية</th>
                        <th>رقم الشوط</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($values as $key => $round)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $round->name }}</td>
                            <td>{{ $round->festival ? $round->festival->name : '---' }}</td>
                            <td>{{ $round->round_type }}</td>
                            <td>{{ $round->start->diffForHumans() }}</td>
                            <td>{{ $round->end->diffForHumans() }}</td>
                            <td>{{ $round->round_number ?? '-' }}</td>
                            <td>


@if($round->open_nomination == 'open')
    {{-- الترشيح مفتوح – الزر لإغلاقه --}}
    <a href="{{ route('close.round', $round->id) }}" class="btn btn-success" title="إغلاق الترشيح">
        <i class="fa-solid fa-lock-open"></i> {{-- 🔓 فتح القفل --}}
    </a>
@else
    {{-- الترشيح مغلق – الزر لفتحه --}}
    <a href="{{ route('open.round', $round->id) }}" class="btn btn-danger" title="فتح الترشيح">

     <i class="fa-solid fa-lock"></i> {{-- 🔒 قفل = إغلاق --}}

    </a>
@endif




                                <a href="{{ route('edit.round', $round->id) }}" class="btn btn-info">تعديل</a>
                                <a href="{{ route('delete.round', $round->id) }}" class="btn btn-danger" id="delete">حذف</a>




                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                       <th>الرقم</th>
                        <th>اسم الشوط</th>
                        <th>المهرجان</th>
                        <th>نوع الشوط</th>
                        <th>تاريخ البداية</th>
                        <th>تاريخ النهاية</th>
                        <th>رقم الشوط</th>
                        <th>الإجراء</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@endsection
