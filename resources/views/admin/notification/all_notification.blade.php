@extends('admin.master_admin')

@section('admin')
{{ $notification->links('pagination::bootstrap-5') }}

<!-- breadcrumb -->
<style>
/* تصغير أيقونة السهم داخل pagination */
.pagination svg {
    width: 12px !important;
    height: 12px !important;
}

/* تقليل المساحة حوالين السهم */
.pagination .page-link {
    padding: 4px 8px !important;
    line-height: 1 !important;
}
</style>


<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">كل الإشعارات</div>

    <div class="ps-3">
        <a href="{{ route('delete.notification.all') }}" class="btn btn-danger" id="delete">
            حذف الكل
        </a>
    </div>
</div>

<hr/>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">

            <table class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>الرقم</th>
                        <th>العنوان</th>
                        <th>تم الإرسال إلى</th>
                        <th>وقت الإرسال</th>
                        <th>الحالة</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($notification as $key => $item)
                    <tr>
                        {{-- ترقيم صحيح مع pagination --}}
                        <td>{{ $notification->firstItem() + $key }}</td>

                        <td>{{ $item->title }}</td>

                        <td>{{ $item->user->fname ?? '-' }}</td>

                        <td>{{ $item->created_at->diffForHumans() }}</td>

                        <td>
                            @if ($item->user_view == 'no')
                                <span class="badge bg-dark">لم تتم المشاهدة</span>
                            @elseif ($item->user_view == 'yes')
                                <span class="badge bg-success">تمت المشاهدة</span>
                            @else
                                <span class="badge bg-danger">تم الحذف</span>
                            @endif
                        </td>

                        <td>
                            <a href="javascript:void(0);"
                               class="btn btn-primary showNotificationBtn"
                               data-title="{{ $item->title }}"
                               data-description="{!! htmlspecialchars($item->des, ENT_QUOTES) !!}"
                               title="تفاصيل">
                                <i class="fa-solid fa-eye"></i>
                            </a>

                            <a href="{{ route('delete.notification', $item->id) }}"
                               class="btn btn-danger"
                               id="delete">
                                حذف
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            لا توجد إشعارات
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>

            <!-- Pagination Links -->
            <div class="mt-3 d-flex justify-content-center">
                {{ $notification->links() }}
            </div>

        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="notificationModalLabel">عنوان الإشعار</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" id="notificationDescription"></div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
      </div>

    </div>
  </div>
</div>

<!-- Image Styling -->
<style>
    #notificationDescription img {
        max-width: 100%;
        height: auto;
        max-height: 400px;
        object-fit: contain;
        display: block;
        margin-bottom: 10px;
    }
</style>

<!-- Modal JS -->
<script>
document.addEventListener("DOMContentLoaded", function () {

    const modal = new bootstrap.Modal(document.getElementById('notificationModal'));

    document.querySelectorAll('.showNotificationBtn').forEach(btn => {

        btn.addEventListener('click', function () {

            const title = this.getAttribute('data-title');
            const description = this.getAttribute('data-description');

            const container = document.getElementById('notificationDescription');

            document.getElementById('notificationModalLabel').innerText = title;
            container.innerHTML = description;

            // Responsive videos
            container.querySelectorAll('iframe').forEach(iframe => {
                if (!iframe.parentElement.classList.contains('ratio')) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'ratio ratio-16x9 mb-2';
                    iframe.parentNode.insertBefore(wrapper, iframe);
                    wrapper.appendChild(iframe);
                }
            });

            modal.show();
        });

    });
});
</script>

@endsection
