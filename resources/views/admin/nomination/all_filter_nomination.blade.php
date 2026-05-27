@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class=" pe-3">

        <h3> نتائج الفرز</h3>
                {{-- <h2>{{$getNomination[0]->festival->name}}</h2>
                                <h2>{{$getNomination[0]->round->name}}</h2> --}}



    </div>

    <div class="ps-3">
        <nav aria-label="breadcrumb">

        </nav>
    </div>
    <div class="ms-auto">
        <div class="btn-group">
            <a href="{{route('filter.users')}}" >

<button type="button" class="btn btn-primary">

فرز جديد
</button>
</a>


        </div>
    </div>
</div>
<!--end breadcrumb-->

<hr/>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered" style="width:100%">
                <thead>
<tr>
<th>الرقم</th>
<th>الإسم</th>
<th>المطية المرشحة</th>
<th>حالة الترشيح</th>

<th> النقاط الحاصل عليها</th>

<th>وقت الترشيح</th>


{{-- <th> الصورة</th> --}}
<th>الاجراء</th>
</tr>
</thead>
<tbody>
@foreach($getNomination as $key => $item)
<tr>
<td> {{ $key+1 }} </td>
<td class="question-column text-wrap">{{ $item->user->fname }} {{ $item->user->lname }} </td>
<td class="question-column text-wrap">{{ $item->camelRoundParticipation->camel_name }} - {{ $item->camelRoundParticipation->camel_age_name }}</td>
<td>
    @if ($item->is_winner == 0)
        <span class="badge bg-secondary" style="font-size: 12px; padding: 4px 10px;">
            لم تعلن النتيجة
        </span>
    @elseif ($item->is_winner == 1)
        <span class="badge bg-success" style="font-size: 12px; padding: 4px 10px;">
            ✔ ترشيح صحيح
        </span>
    @else
        <span class="badge bg-danger" style="font-size: 12px; padding: 4px 10px;">
            ✘ ترشيح خاطئ
        </span>
    @endif
</td>

<td>{{ $item->points }}</td>

{{-- <td>{{ $item->user->phone }}</td> --}}
{{-- <td style="font-size: 32px; " >
    <p >{{ $item->country_flag }}</p>
</td> --}}

<td>{{ $item->created_at ? $item->created_at->diffForHumans() : 'لم يتم التحديد' }}</td>


{{-- <td> <img onclick="showImageModal(this.src)" class="rounded-circle"  src="{{  (!empty($item->photo) && $item->photo != 'non' )  ? url('upload/user_images/'.$item->photo):url('upload/no_image.jpg') }}" style="width: 50px; height:50px; cursor: pointer; border: 2px solid #0aa2dd;" >  </td> --}}

<td>

    <!-- زر التفاصيل -->
    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailsModal{{ $item->id }}">
        <i class="fa fa-eye"></i>
    </button>
{{-- @if($item->status == 'active')
<a href="{{ route('inactive.user',$item->id) }}" class="btn btn-primary" title="ايقاف التفعيل"> <i class="fa-solid fa-thumbs-down"></i> </a>
@else
<a href="{{ route('active.user',$item->id) }}" class="btn btn-primary" title="تفعيل"> <i class="fa-solid fa-thumbs-up"></i> </a>
@endif
<a href="{{ route('edit.user',$item->id) }}" class="btn btn-info" title="Edit Data"> <i class="fa fa-pencil"></i> </a>

<a href="{{ route('delete.user',$item->id) }}" class="btn btn-danger" id="delete" title="Delete Data" ><i class="fa fa-trash"></i></a> --}}

</td>
<!-- Modal Details -->
<div class="modal fade" id="detailsModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">تفاصيل الترشيح</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- Profile Card -->
        <div class="text-center mb-4">

            <!-- User Image -->
            <img src="{{ (!empty($item->user->photo) && $item->user->photo != 'non')
                    ? url('upload/user_images/'.$item->user->photo)
                    : url('upload/no_image.jpg') }}"
                class="rounded-circle shadow"
                style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #0aa2dd;">

            <h4 class="mt-3">
                {{ $item->user->fname }} {{ $item->user->lname }}
            </h4>

            <!-- Phone + Country Flag + Total Points -->
         <div class="d-flex justify-content-around align-items-center mt-3 px-4"
     style="font-size: 14px;">

    <div class="d-flex align-items-center" style="gap: 5px;">
        <span style="font-size: 16px;">📞</span>
        <strong>الهاتف:</strong>
        <span>{{ $item->user->phone ?? 'غير متوفر' }}</span>
    </div>

    <div class="d-flex align-items-center" style="gap: 5px;">
        <span style="font-size: 16px;">🌍</span>
        <strong>الدولة:</strong>

        <!-- نضبط العلم هنا بحيث يكون بنفس ارتفاع النص -->
        <span style="
            display: inline-flex;
            align-items: center;
            font-size: 20px;
            line-height: 1;
        ">
            {{ $item->user->country_flag }}
        </span>
    </div>

    <div class="d-flex align-items-center" style="gap: 5px;">
        <span style="font-size: 16px;">🏆</span>
        <strong>إجمالي النقاط في كافة المهرجانات:</strong>

        <span>{{ $item->user->nominations->sum('points') ?? 0 }}</span>
    </div>

</div>


        </div>

        <hr>

        <!-- Nomination Details -->
        <div class="mt-4">

            <p><strong>المطية المرشحة:</strong>
                {{ $item->camelRoundParticipation->camel_name }}
                - {{ $item->camelRoundParticipation->camel_age_name }}
            </p>

            <p><strong>حالة الترشيح:</strong>
                @if ($item->is_winner == 0)
                    <span class="badge bg-secondary">لم تعلن النتيجة</span>
                @elseif ($item->is_winner == 1)
                    <span class="badge bg-success">✔ ترشيح صحيح</span>
                @else
                    <span class="badge bg-danger">✘ ترشيح خاطئ</span>
                @endif
            </p>

            <p><strong>النقاط:</strong> {{ $item->points }}</p>

            <p><strong>وقت الترشيح:</strong>
                {{ $item->created_at ? $item->created_at->format('Y-m-d H:i') : 'لم يتم التحديد' }}
            </p>

        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
      </div>

    </div>
  </div>
</div>

</tr>
@endforeach


</tbody>
<tfoot>
<tr>
<th>الرقم</th>
<th>الإسم</th>
<th>المطية المرشحة</th>
<th>حالة الترشيح</th>

<th> النقاط الحاصل عليها</th>
<th>وقت الترشيح</th>

{{-- <th> الصورة</th> --}}
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
