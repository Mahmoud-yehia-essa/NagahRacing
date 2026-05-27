@extends('admin.master_admin')
@section('admin')

<div class="page-content">
    <div class="container-fluid">

        <!-- الرسائل -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- عنوان الصفحة -->
        <div class="row mb-4">
            <div class="col-sm-6">
                <h4 class="mb-0">جميع نقاط المهرجان</h4>
            </div>
            {{-- <div class="col-sm-6 text-end">
                <a href="{{ route('add.festival.points') }}" class="btn btn-primary">إضافة نقاط جديدة</a>
            </div> --}}
        </div>

        <!-- جدول العرض -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                {{-- <th>المهرجان</th> --}}
                                <th>الفئة العمرية</th>
                                <th>النقاط</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($points as $key => $point)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    {{-- <td>{{ $point->festival ? $point->festival->name : '---' }}</td> --}}
                                    <td>{{ $point->age_name }}</td>
                                    <td>{{ $point->points }}</td>
                                    <td>
                                        <a href="{{ route('edit.festival.points', $point->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                                        {{-- <a href="{{ route('delete.festival.points', $point->id) }}" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</a> --}}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">لا توجد نقاط مضافة بعد</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
