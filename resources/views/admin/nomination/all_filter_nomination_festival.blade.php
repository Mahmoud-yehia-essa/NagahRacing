@extends('admin.master_admin')
@section('admin')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="pe-3">
        <h3>نتائج الفرز</h3>
    </div>
    <div class="ms-auto">
        <a href="{{route('filter.users.fesitval')}}" class="btn btn-primary">فرز جديد</a>
    </div>
</div>

<hr/>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered text-center">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم المستخدم</th>
                        <th>رقم الهاتف</th>
                        <th>النقاط الإجمالية</th>
                        <th>التفاصيل</th>
                    </tr>
                </thead>

                {{-- <tbody>
                @foreach($getNomination as $key => $item)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $item->user->fname }} {{ $item->user->lname }}</td>
                        <td>{{ $item->user->phone }} </td>
                        <td>{{ $item->total_points }}</td>
                        <td>
                            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailsModal{{ $item->user_id }}">
                                <i class="fa fa-eye"></i>
                            </button>
                        </td>
                    </tr>

                @endforeach
                </tbody> --}}
                <tbody>
@foreach($getNomination as $key => $item)
<tr>
    <td>{{ $key + 1 }}</td>
    <td>{{ $item->user->fname }} {{ $item->user->lname }}</td>
    <td dir="ltr">{{ $item->user->country_code }} {{ $item->user->phone }} </td>
    <td>{{ $item->total_points }}</td>
    <td>
        @php
            // // تحويل الرقم لمناسب واتساب
            // $phone = preg_replace('/^0/', '20', $item->user->phone); // 20 = كود مصر
            // $whatsappUrl = "https://wa.me/$phone";

    // إضافة كود الدولة من الداتا
    $fullPhone = $item->user->country_code . $item->user->phone;

    // رابط الواتساب
    $whatsappUrl = "https://wa.me/$fullPhone";
        @endphp

        <a href="{{ $whatsappUrl }}" target="_blank" class="btn btn-success btn-sm">
            <i class="fab fa-whatsapp"></i> واتساب
        </a>
    </td>
</tr>
@endforeach
</tbody>

            </table>
        </div>
    </div>
</div>

@endsection
