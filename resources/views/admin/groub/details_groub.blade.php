@extends('admin.master_admin')
@section('admin')

<div class="container-fluid">

    <!-- بيانات المجموعة -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <img
    src="{{ asset('upload/group/'.$groub->photo) }}"
    class="rounded-circle shadow"
    width="120"
    height="120"
    style="object-fit: cover;"
>
                    {{-- <img src="{{ asset("upload/group/".$groub->photo) }}" class="rounded-circle shadow" width="120" height="120"> --}}
                </div>
                <div class="col-md-6">
                    <h3 class="fw-bold">{{ $groub->name }}</h3>
                    <h5 class="text-muted">كود الانضمام: <span class="badge bg-primary fs-6">{{ $groub->code_number }}</span></h5>
                    <p class="mb-1">منشئ المجموعة: <strong>{{ $groub->user->fname }} {{ $groub->user->lname }}</strong></p>
                    <p class="text-muted">تاريخ الانشاء: {{ $groub->created_at ? $groub->created_at->diffForHumans() : 'غير محدد' }}</p>
                    <p class="text-muted"> رابط المشاركة:  <a href="http://localhost:8888/group/invitation/{{$groub->code_number}}">اضغط للانتقال الى الرابط</a></p>

                </div>
                <div class="col-md-4">
                    <div class="row text-center">
                        <div class="col">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h4>{{ $groub->groupUsers->count() }}</h4>
                                    <span class="text-muted">الأعضاء</span>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h4>1</h4>
                                    <span class="text-muted">المهرجانات</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="card shadow-sm">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="groupTabs">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#members">أعضاء المجموعة</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#nominations">ترشيحات الأعضاء</a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">

                <!-- Tab 1: أعضاء المجموعة -->
                <div class="tab-pane fade show active" id="members">
                    <div class="row">
                        @foreach($groub->groupUsers as $member)
                            <div class="col-md-4 mb-3">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <img src="{{ (!empty($member->user->photo) && $member->user->photo != 'non')
                                                    ? asset('upload/user_images/'.$member->user->photo)
                                                    : asset('upload/no_image.jpg') }}"
                                             class="rounded-circle mb-2" width="60" height="60">
                                        <h6>{{ $member->user->fname }} {{ $member->user->lname }}</h6>
                                        <p class="text-muted small">{{ $member->user->email }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Tab 2: ترشيحات الأعضاء -->
                <div class="tab-pane fade" id="nominations">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>العضو</th>
                                    <th>الدرجة الكلية</th>
                                    <th>التفاصيل</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($nominations as $nom)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $nom->user->fname }} {{ $nom->user->lname }}</td>
                                        <td>{{ $nom->points }}</td>
                                        <td>
                                            <!-- زر التفاصيل -->
                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailsModal{{ $nom->user->id }}">
                                                التفاصيل
                                            </button>

                                            <!-- Modal لكل عضو -->
                                            <div class="modal fade" id="detailsModal{{ $nom->user->id }}" tabindex="-1" aria-labelledby="detailsModalLabel{{ $nom->user->id }}" aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="detailsModalLabel{{ $nom->user->id }}">
                                                                تفاصيل الترشيحات: {{ $nom->user->fname }} {{ $nom->user->lname }}
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>#</th>
                                                                            <th>الشوط</th>
                                                                            <th>المطية</th>
                                                                            <th>مالك المطية</th>
                                                                            <th>الفئة العمرية</th>
                                                                            <th>الدرجة</th>
                                                                            <th>حالة الترشيح</th>
                                                                            <th>منذ</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($nom->nominations as $userNom)
                                                                            <tr>
                                                                                <td>{{ $loop->iteration }}</td>
                                                                                <td>{{ $userNom->round->name ?? 'غير محدد' }}</td>
                                                                                <td>{{ $userNom->camelRoundParticipation?->camel_name ?? 'غير محدد' }}</td>
                                                                                <td>{{ $userNom->camelRoundParticipation?->camel_owner_name ?? 'غير محدد' }}</td>
                                                                                <td>{{ $userNom->camelRoundParticipation?->camel_age_name ?? 'غير محدد' }}</td>
                                                                                <td>{{ $userNom->points ?? 0 }}</td>
                                                           <td>
    @if($userNom->is_winner == 0)
        <span class="text-warning fw-bold">
            <i class="fa fa-clock"></i> انتظار النتائج
        </span>
    @elseif ($userNom->is_winner == 1)
        <span class="text-success fw-bold"><i class="fa fa-check"></i> ترشيح صحيح</span>
    @elseif ($userNom->is_winner == 2)
        <span class="text-danger fw-bold"><i class="fa fa-times"></i> ترشيح خاطئ</span>
    @endif
</td>
                                                                                <td>{{ $userNom->created_at ? $userNom->created_at->diffForHumans() : 'غير محدد' }}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- نهاية Modal -->
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- نهاية Tabs -->
            </div>
        </div>
    </div>

</div>
@endsection
