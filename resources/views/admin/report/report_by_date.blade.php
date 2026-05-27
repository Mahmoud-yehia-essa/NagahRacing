@extends('admin.master_admin')
@section('admin')


<script type="text/javascript">
  google.charts.load("current", {packages:["corechart"]});
  google.charts.setOnLoadCallback(drawChart);
  function drawChart() {
    var data = google.visualization.arrayToDataTable([
      ['Task', 'Hours per Day'],
      ['المستخدمين المسجلين',     {{$users->count()}}],
      ['الفئات',      {{$category->count()}}],
      ['الألعاب',  {{$games->count()}}],
      ['الأسئلة', {{$questions->count()}}],
    ]);


        // ['Task', 'Hours per Day'],
    //   ['المستخدمين المسجلين',     {{$users}}],
    //   ['الفئات',      {{$category}}],
    //   ['الألعاب',  {{$games}}],
    //   ['الأسئلة',  {{$questions}}],
    var options = {
      title: 'نتائج الاحصائية في تاريخ : {{ $formatDate }}',
      is3D: true,
    };

    var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
    chart.draw(data, options);
  }
</script>




				<!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<div class="breadcrumb-title pe-3">الاحصائية خلال تاريخ معين</div>
					<div class="ps-3">
						<nav aria-label="breadcrumb">

						</nav>
					</div>
					<div class="ms-auto">
						<div class="btn-group">

						</div>
					</div>
				</div>
				<!--end breadcrumb-->
				 <h3> نتائج الاحصائية في تاريخ - {{ $formatDate }}</h3>
				<hr/>

                @if ($users->count() == 0 && $category->count() == 0 && $games->count() == 0  && $questions->count() == 0)

                <h3 class=" text-danger"> لا توجد بيانات متاحة لعرض الاحصائية</h3>


                @else
                <div id="piechart_3d" style="width: 100%; height: 550px;"></div>

                @endif





@endsection
