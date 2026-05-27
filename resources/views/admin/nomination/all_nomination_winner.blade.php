@extends('admin.master_admin')
@section('admin')

<div class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">عرض الترشيحات</div>
    </div>

    <div class="card">
        <div class="card-body">
            <h4 class="mb-3">جميع الترشيحات</h4>
            <a href="{{ route('add.nomination') }}" class="btn btn-success mb-3">إضافة ترشيح جديد</a>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>المستخدم</th>
                        <th>المطية</th>
                        <th>المهرجان</th>
                        <th>الجولة</th>
                        <th>فائز؟</th>
                        <th>فائز؟</th>

                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($nominations as $nomination)
                        <tr>
                            <td>{{ $nomination->user->fname ?? '-' }}</td>
                            <td>{{ $nomination->camal->name ?? '-' }}</td>
                            <td>{{ $nomination->festival->name ?? '-' }}</td>
                            <td>{{ $nomination->round->name ?? '-' }}</td>
                            <td>{{ $nomination->is_winner ? 'نعم' : 'لا' }}</td>

                              <td>{{ $nomination->is_winner ? 'نعم' : 'لا' }}</td>

                            <td>
                                <a href="{{ route('edit.nomination', $nomination->id) }}" class="btn btn-primary btn-sm">تعديل</a>
                                <a href="{{ route('delete.nomination', $nomination->id) }}" class="btn btn-danger btn-sm"
                                   onclick="return confirm('هل أنت متأكد من حذف الترشيح؟')">حذف</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
