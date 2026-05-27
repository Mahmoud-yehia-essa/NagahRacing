@extends('admin.master_admin')
@section('admin')

<div class="page-content">

    <!-- breadcrumb -->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">عرض الترشيحات</div>
    </div>

    <div class="card">
        <div class="card-body">
            <h4 class="mb-3">جميع الترشيحات</h4>
            <a href="{{ route('add.nomination.user') }}" class="btn btn-success mb-3">
                <i class="bx bx-plus"></i> إضافة ترشيح جديد
            </a>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>#</th>
                            <th>رقم الشوط</th>
                            <th>تاريخ بداية الشوط</th>
                            <th>المستخدم</th>
                            <th>المطية المرشحة</th>
                            <th>المهرجان</th>
                            <th>الشوط</th>
                            <th>تاريخ انتهاء الشوط</th>
                            <th>الحالة</th>
                            <th>الإجراء</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @forelse($nominations as $key => $nomination)
                            <tr>
                                <td>{{ $nominations->firstItem() + $key }}</td>
                                <td>{{ $nomination->round->round_number ?? '-' }}</td>

                                <td>
                                    @if ($nomination->round->status !== 'pending')
                                        <span class="badge bg-secondary">تم إعلان النتيجة</span>
                                    @else
                                        <span>{{ $nomination->round->start->diffForHumans() }}</span><br>
                                        <small class="text-muted">{{ $nomination->round->start->format('Y-m-d H:i:s') }}</small><br>
                                        <span id="countdown-start-{{ $nomination->id }}" class="text-primary fw-bold"></span>
                                    @endif
                                </td>

                                <td>{{ $nomination->user->fname ?? '-' }}</td>
                                <td>{{ $nomination->camelRoundParticipation->camel_name ?? '-' }}</td>
                                <td>{{ $nomination->festival->name ?? '-' }}</td>
                                <td>{{ $nomination->round->name ?? '-' }}</td>

                                <td>
                                    @if ($nomination->round->status !== 'pending')
                                        <span class="badge bg-secondary">تم إعلان النتيجة</span>
                                    @else
                                        <span>{{ $nomination->round->end->diffForHumans() }}</span><br>
                                        <small class="text-muted">{{ $nomination->round->end->format('Y-m-d H:i:s') }}</small><br>
                                        <span id="countdown-end-{{ $nomination->id }}" class="text-danger fw-bold"></span>
                                    @endif
                                </td>

                                <td>
                                    @if ($nomination->camelRoundParticipation->is_winner === 1 && $nomination->camel_round_participations_id === $nomination->camelRoundParticipation->id)
                                        <span class="badge bg-success">ترشيح صحيح</span>
                                    @elseif ($nomination->camelRoundParticipation->is_winner === 0 && $nomination->camel_round_participations_id === $nomination->camelRoundParticipation->id && $nomination->round->status !== 'pending')
                                        <span class="badge bg-danger">ترشيح خاطئ</span>
                                    @else
                                        <span class="badge bg-warning text-dark">النتائج لم تُعلن بعد</span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('delete.nomination', $nomination->id) }}"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('هل أنت متأكد من حذف الترشيح؟')">
                                        <i class="bx bx-trash"></i> حذف
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">
                                    لا توجد ترشيحات
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="mt-3 d-flex justify-content-center">
                    {{ $nominations->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS لتصغير Pagination -->
<style>
.pagination .page-link,
.pagination .page-item span {
    padding: 2px 6px !important;
    font-size: 12px !important;
    line-height: 1 !important;
}

.pagination svg {
    width: 10px !important;
    height: 10px !important;
}

.pagination .page-link {
    min-height: auto !important;
}
</style>

<!-- العد التنازلي -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    @foreach($nominations as $nomination)
        // عداد البداية
        var startTime{{ $nomination->id }} = new Date("{{ $nomination->round->start }}").getTime();
        var countdownStart{{ $nomination->id }} = setInterval(function() {
            var now = new Date().getTime();
            var distance = startTime{{ $nomination->id }} - now;

            if(distance < 0){
                clearInterval(countdownStart{{ $nomination->id }});
                document.getElementById("countdown-start-{{ $nomination->id }}").innerHTML = "بدأت الجولة";
            } else {
                var days = Math.floor(distance / (1000*60*60*24));
                var hours = Math.floor((distance % (1000*60*60*24))/(1000*60*60));
                var minutes = Math.floor((distance % (1000*60*60))/(1000*60));
                var seconds = Math.floor((distance % (1000*60))/1000);
                document.getElementById("countdown-start-{{ $nomination->id }}").innerHTML =
                    "فاضل: " + days + " أيام " + hours + " ساعات " + minutes + " دقائق " + seconds + " ثواني";
            }
        }, 1000);

        // عداد النهاية
        var endTime{{ $nomination->id }} = new Date("{{ $nomination->round->end }}").getTime();
        var countdownEnd{{ $nomination->id }} = setInterval(function() {
            var now = new Date().getTime();
            var distance = endTime{{ $nomination->id }} - now;

            if(distance < 0){
                clearInterval(countdownEnd{{ $nomination->id }});
                document.getElementById("countdown-end-{{ $nomination->id }}").innerHTML = "انتهت الجولة";
            } else {
                var days = Math.floor(distance / (1000*60*60*24));
                var hours = Math.floor((distance % (1000*60*60*24))/(1000*60*60));
                var minutes = Math.floor((distance % (1000*60*60))/(1000*60));
                var seconds = Math.floor((distance % (1000*60))/1000);
                document.getElementById("countdown-end-{{ $nomination->id }}").innerHTML =
                    "فاضل: " + days + " أيام " + hours + " ساعات " + minutes + " دقائق " + seconds + " ثواني";
            }
        }, 1000);
    @endforeach
});
</script>

@endsection
