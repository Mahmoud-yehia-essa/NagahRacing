@extends('admin.master_admin')
@section('admin')

<script type="text/javascript">
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        var data = google.visualization.arrayToDataTable([
            ['المقياس', 'العدد'],
            ['الملاك', {{$owners_count}}],
            ['العمال', {{$workers_count}}],
            ['جلسات التدريب', {{$sessions_count}}],
            ['باقات الاشتراك', {{$plans_count}}],
            ['اشتراكات الملاك', {{$subscriptions_count}}],
        ]);

        var options = {
            title: 'توزيع إحصائيات الاشتراكات والجلسات',
            fontName: 'sans-serif',
            fontSize: 14,
            chartArea: { width: '90%', height: '80%' },
            colors: ['#971048', '#b8235d', '#d4437c', '#eb6e9f', '#ff9ec4'],
            is3D: true,
            legend: { position: 'right', alignment: 'center' }
        };

        var chart = new google.visualization.PieChart(document.getElementById('piechart'));
        chart.draw(data, options);
    }
</script>

<style>
    .brand-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(151, 16, 72, 0.1);
        color: #ffffff;
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
    }
    .brand-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(151, 16, 72, 0.25);
    }
    .brand-card .brand-icon-box {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.15);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        transition: all 0.3s ease;
    }
    .brand-card:hover .brand-icon-box {
        background-color: #ffffff;
        color: #971048;
    }
    .brand-card .brand-title {
        color: rgba(255, 255, 255, 0.85);
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 3px;
    }
    .brand-card .brand-value {
        color: #ffffff;
        font-size: 24px;
        font-weight: 800;
    }

    /* Gradient Themes using brand color #971048 as base */
    .brand-grad-1 {
        background: linear-gradient(135deg, #971048, #bd2160);
    }
    .brand-grad-2 {
        background: linear-gradient(135deg, #800a3b, #a61c56);
    }
    .brand-grad-3 {
        background: linear-gradient(135deg, #5c0528, #880e4f);
    }
    .brand-grad-4 {
        background: linear-gradient(135deg, #ad1457, #d81b60);
    }
    .brand-grad-5 {
        background: linear-gradient(135deg, #6a0b33, #971048);
    }
    .brand-grad-6 {
        background: linear-gradient(135deg, #4a001f, #7b0d3a);
    }
</style>

<!-- Statistics Row -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3 mb-4">
    <!-- Owners Count -->
    <div class="col">
        <a href="{{ route('all.users') }}" class="text-decoration-none">
            <div class="card brand-card brand-grad-1 h-100">
                <div class="card-body d-flex align-items-center">
                    <div>
                        <p class="brand-title">عدد الملاك</p>
                        <h3 class="brand-value mb-0">{{ $owners_count }}</h3>
                    </div>
                    <div class="brand-icon-box ms-auto">
                        <i class='bx bx-group'></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Workers Count -->
    <div class="col">
        <a href="{{ route('all.camel.workers') }}" class="text-decoration-none">
            <div class="card brand-card brand-grad-2 h-100">
                <div class="card-body d-flex align-items-center">
                    <div>
                        <p class="brand-title">عدد العمال</p>
                        <h3 class="brand-value mb-0">{{ $workers_count }}</h3>
                    </div>
                    <div class="brand-icon-box ms-auto">
                        <i class='bx bx-run'></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Sessions Count -->
    <div class="col">
        <a href="{{ route('all.training.sessions') }}" class="text-decoration-none">
            <div class="card brand-card brand-grad-3 h-100">
                <div class="card-body d-flex align-items-center">
                    <div>
                        <p class="brand-title">عدد الجلسات</p>
                        <h3 class="brand-value mb-0">{{ $sessions_count }}</h3>
                    </div>
                    <div class="brand-icon-box ms-auto">
                        <i class='bx bx-history'></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Plans Count -->
    <div class="col">
        <a href="{{ route('all.subscription.plans') }}" class="text-decoration-none">
            <div class="card brand-card brand-grad-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div>
                        <p class="brand-title">عدد الباقات</p>
                        <h3 class="brand-value mb-0">{{ $plans_count }}</h3>
                    </div>
                    <div class="brand-icon-box ms-auto">
                        <i class='bx bx-credit-card-front'></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Subscriptions Count -->
    <div class="col">
        <a href="{{ route('all.user.subscriptions') }}" class="text-decoration-none">
            <div class="card brand-card brand-grad-5 h-100">
                <div class="card-body d-flex align-items-center">
                    <div>
                        <p class="brand-title">عدد الاشتراكات</p>
                        <h3 class="brand-value mb-0">{{ $subscriptions_count }}</h3>
                    </div>
                    <div class="brand-icon-box ms-auto">
                        <i class='bx bx-check-shield'></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Total Revenue -->
    <div class="col">
        <a href="{{ route('all.user.subscriptions') }}" class="text-decoration-none">
            <div class="card brand-card brand-grad-6 h-100">
                <div class="card-body d-flex align-items-center">
                    <div>
                        <p class="brand-title">إجمالي الإيرادات</p>
                        <h3 class="brand-value mb-0">{{ number_format($total_revenue, 2) }} ر.س</h3>
                    </div>
                    <div class="brand-icon-box ms-auto">
                        <i class='bx bx-money'></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Chart and Visuals -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div id="piechart" style="width: 100%; height: 350px;"></div>
            </div>
        </div>
    </div>
</div>

<hr class="my-4">

<h4 class="mb-4 fw-bold text-dark"><i class="bx bx-user-check text-primary"></i> آخر 10 ملاك مسجلين</h4>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th>الرقم</th>
                        <th>الاسم الأول</th>
                        <th>اسم العائلة</th>
                        <th>البريد الإلكتروني</th>
                        <th>عدد العمال</th>
                        <th>تاريخ التسجيل</th>
                        <th>الصورة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($owners as $key => $item)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td class="fw-bold">{{ $item->fname }}</td>
                            <td class="fw-bold">{{ $item->lname }}</td>
                            <td>{{ $item->email }}</td>
                            <td>
                                <a href="{{ route('all.camel.workers', ['owner_id' => $item->id]) }}" class="badge bg-primary text-white text-decoration-none px-3 py-2">
                                    <i class="bx bx-group me-1"></i> {{ $item->camel_workers_count }} عمال
                                </a>
                            </td>
                            <td>
                                <span class="text-muted"><i class="bx bx-time"></i> {{ $item->created_at ? $item->created_at->diffForHumans() : 'لم يتم التحديد' }}</span>
                            </td>
                            <td>
                                <img class="rounded-circle border" src="{{ (!empty($item->photo)) ? url('upload/user_images/'.$item->photo) : url('upload/no_image.jpg') }}" style="width: 45px; height: 45px; border-color: #971048 !important; object-fit: cover;" >
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
