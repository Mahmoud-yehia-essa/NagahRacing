@extends('admin.master_admin')
@section('admin')

<!-- Google Maps JavaScript API is loaded at the bottom -->

<style>
    #map {
        height: 450px;
        width: 100%;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        z-index: 1;
    }
    .metric-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .metric-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    /* Chat/Timeline styles */
    .chat-container {
        max-height: 500px;
        overflow-y: auto;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 12px;
        border: 1px solid #e9ecef;
    }
    .chat-bubble {
        max-width: 75%;
        margin-bottom: 15px;
        padding: 12px 16px;
        border-radius: 16px;
        position: relative;
    }
    .chat-owner {
        background-color: #e3f2fd;
        margin-left: auto;
        border-bottom-right-radius: 4px;
        color: #0d47a1;
        border: 1px solid #bbdefb;
    }
    .chat-worker {
        background-color: #f1f8e9;
        margin-right: auto;
        border-bottom-left-radius: 4px;
        color: #33691e;
        border: 1px solid #dcedc8;
    }
    .chat-meta {
        font-size: 11px;
        opacity: 0.8;
        margin-top: 5px;
        display: block;
    }
    .chat-reply {
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px dashed rgba(0,0,0,0.1);
    }
</style>

<div class="page-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">جلسات التدريب</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('all.training.sessions') }}">جلسات التدريب</a></li>
                    <li class="breadcrumb-item active" aria-current="page">تفاصيل الجلسة #{{ $session->id }}</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a href="{{ route('all.training.sessions') }}" class="btn btn-secondary"><i class="bx bx-arrow-back"></i> العودة للجلسات</a>
        </div>
    </div>
    <!--end breadcrumb-->

    <hr/>

    <!-- General Info Row -->
    <div class="row">
        <!-- Trainer & Owner Info Card -->
        <div class="col-lg-4 col-md-12">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">بيانات الجلسة</h5>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-container me-3" style="width: 60px; height: 60px; border-radius: 50%; overflow: hidden; background: #e9ecef;">
                            @if($session->worker && $session->worker->photo_path)
                                <img src="{{ asset($session->worker->photo_path) }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <img src="{{ asset('upload/no_image.jpg') }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @endif
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">{{ $session->worker ? $session->worker->full_name : 'عامل غير معروف' }}</h6>
                            <span class="text-muted font-13">المدرب / العامل المسؤول</span>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <span class="text-muted d-block font-12">المالك المشرف:</span>
                        @if($session->worker && $session->worker->owner)
                            <strong class="font-15"><i class="bx bx-user text-primary"></i> {{ $session->worker->owner->fname }} {{ $session->worker->owner->lname }}</strong>
                            <span class="text-muted d-block font-12 mt-1"><i class="bx bx-phone"></i> {{ $session->worker->owner->phone }}</span>
                        @else
                            <strong class="text-muted">غير محدد</strong>
                        @endif
                    </div>

                    <div class="mb-3">
                        <span class="text-muted d-block font-12">موقع بداية الجلسة:</span>
                        <strong><i class="bx bx-map-pin text-danger"></i> {{ $session->location_name ?? 'غير محدد' }}</strong>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted d-block font-12">حالة الجلسة:</span>
                        @if($session->round_status == 'pending')
                            <span class="badge bg-warning text-dark"><i class="bx bx-time"></i> قيد الانتظار</span>
                        @elseif($session->round_status == 'working')
                            <span class="badge bg-success"><i class="bx bx-run"></i> قيد التدريب (نشطة)</span>
                        @elseif($session->round_status == 'stop')
                            <span class="badge bg-danger"><i class="bx bx-pause"></i> متوقفة مؤقتاً</span>
                        @elseif($session->round_status == 'end')
                            <span class="badge bg-secondary"><i class="bx bx-check-circle"></i> منتهية</span>
                        @endif
                    </div>

                    <div class="mb-0">
                        <span class="text-muted d-block font-12">تاريخ الجلسة:</span>
                        <strong><i class="bx bx-calendar"></i> {{ $session->created_at->format('Y-m-d H:i:s') }}</strong>
                        @if($session->session_ended_at)
                            <span class="text-muted d-block font-12 mt-1">تاريخ الانتهاء:</span>
                            <strong><i class="bx bx-calendar-check"></i> {{ $session->session_ended_at->format('Y-m-d H:i:s') }}</strong>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Metrics Grid -->
        <div class="col-lg-8 col-md-12">
            <div class="row g-3 h-100">
                <!-- Speed Card -->
                <div class="col-md-6 col-sm-6">
                    <div class="card metric-card border-start border-0 border-4 border-info h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">السرعة الحالية</p>
                                    <h4 id="card-current-speed" class="my-1 text-info fw-bold">{{ number_format($session->speed, 2) }} <span class="font-14">كم/س</span></h4>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-scooter text-white ms-auto">
                                    <i class="bx bx-tachometer"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Avg Speed Card -->
                <div class="col-md-6 col-sm-6">
                    <div class="card metric-card border-start border-0 border-4 border-primary h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">متوسط السرعة</p>
                                    <h4 id="card-average-speed" class="my-1 text-primary fw-bold">{{ number_format($session->average_speed, 2) }} <span class="font-14">كم/س</span></h4>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-bloody text-white ms-auto">
                                    <i class="bx bx-pulse"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Distance Card -->
                <div class="col-md-6 col-sm-6">
                    <div class="card metric-card border-start border-0 border-4 border-success h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">المسافة الاجمالية المقطوعة</p>
                                    <h4 id="card-distance" class="my-1 text-success fw-bold">{{ number_format($session->round_distance_km, 2) }} <span class="font-14">كم</span></h4>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto">
                                    <i class="bx bx-map-alt"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Time Card -->
                <div class="col-md-6 col-sm-6">
                    <div class="card metric-card border-start border-0 border-4 border-warning h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">مدة الجلسة</p>
                                    <h4 id="card-session-duration" class="my-1 text-warning fw-bold">{{ $session->round_time ?? '00:00:00' }}</h4>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-blooker text-white ms-auto">
                                    <i class="bx bx-time-five"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Card -->
                <div class="col-12">
                    <div class="card metric-card border-start border-0 border-4 border-danger h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <p class="mb-0 text-secondary">تقييم أداء الهجن</p>
                                <span class="badge bg-danger font-14">{{ number_format($session->performance, 2) }}%</span>
                            </div>
                            <div class="progress" style="height: 12px;">
                                <div class="progress-bar bg-danger progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ min(100, max(0, $session->performance)) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($session->round_status == 'end' && ($session->summary_text || $session->summary_audio || $session->summary_image))
        <!-- Session Summary/Notes Card -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-3 text-success"><i class="bx bx-notepad"></i> ملخص وملاحظات الجلسة (من قبل العامل)</h5>
                <div class="row align-items-center">
                    <div class="col-md-8">
                        @if($session->summary_text)
                            <p class="font-15 text-dark fw-bold mb-3" style="line-height: 1.8;">{{ $session->summary_text }}</p>
                        @endif

                        @if($session->summary_audio)
                            <div class="my-3">
                                <span class="text-muted d-block font-12 mb-1"><i class="bx bx-volume-full text-success"></i> الملاحظة الصوتية من العامل:</span>
                                <audio src="{{ asset($session->summary_audio) }}" controls class="w-100" style="max-width: 500px; height: 40px;"></audio>
                            </div>
                        @endif
                    </div>
                    @if($session->summary_image)
                        <div class="col-md-4 text-center">
                            <span class="text-muted d-block font-12 mb-2"><i class="bx bx-image"></i> الصورة المرفقة مع الملاحظة:</span>
                            <img src="{{ asset($session->summary_image) }}" class="img-fluid rounded shadow-sm border" style="max-height: 200px; cursor: pointer; object-fit: cover;" onclick="window.open(this.src)" alt="ملخص الجلسة">
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Map & Instructions Chat Row -->
    <div class="row mt-4">
        <!-- Interactive Map Card -->
        <div class="col-lg-7 col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title fw-bold mb-0"><i class="bx bx-map text-danger"></i> خريطة مسار التدريب والموقع الحالي</h5>
                        <div class="d-flex gap-2">
                            <button id="btn-start-simulation" class="btn btn-sm btn-success px-3"><i class="bx bx-play-circle"></i> بدء محاكاة حركة المطية</button>
                            <button id="btn-track-mode" class="btn btn-sm btn-outline-primary px-3"><i class="bx bx-navigation"></i> تفعيل وضع التتبع</button>
                            <button id="btn-clear-logs" class="btn btn-sm btn-danger px-3"><i class="bx bx-trash"></i> حذف السجلات</button>
                        </div>
                    </div>
                    <div style="position: relative; width: 100%;">
                        <div id="map"></div>
                        <!-- Compass Overlay -->
                        <div id="compass-container" style="display: none; position: absolute; top: 15px; right: 15px; width: 65px; height: 65px; background: rgba(255,255,255,0.9); backdrop-filter: blur(5px); border-radius: 50%; box-shadow: 0 4px 15px rgba(0,0,0,0.2); z-index: 99; align-items: center; justify-content: center; border: 2px solid #971048; transition: all 0.3s ease;">
                            <!-- Compass Dial -->
                            <div style="position: absolute; width: 100%; height: 100%; border-radius: 50%; border: 1px dashed rgba(151,16,72,0.3); pointer-events: none;"></div>
                            <span style="position: absolute; top: 3px; font-size: 9px; font-weight: bold; color: #971048; font-family: Arial, sans-serif;">N</span>
                            <span style="position: absolute; bottom: 3px; font-size: 9px; font-weight: bold; color: #777777; font-family: Arial, sans-serif;">S</span>
                            <span style="position: absolute; left: 5px; font-size: 9px; font-weight: bold; color: #777777; font-family: Arial, sans-serif;">W</span>
                            <span style="position: absolute; right: 5px; font-size: 9px; font-weight: bold; color: #777777; font-family: Arial, sans-serif;">E</span>
                            <!-- Compass Needle -->
                            <div id="compass-needle" style="width: 8px; height: 42px; position: relative; transition: transform 0.5s ease-out; transform: rotate(0deg);">
                                <!-- Red pointer pointing North -->
                                <div style="width: 0; height: 0; border-left: 4px solid transparent; border-right: 4px solid transparent; border-bottom: 21px solid #971048; position: absolute; top: 0; left: 0;"></div>
                                <!-- Silver pointer pointing South -->
                                <div style="width: 0; height: 0; border-left: 4px solid transparent; border-right: 4px solid transparent; border-top: 21px solid #c0c0c0; position: absolute; bottom: 0; left: 0;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructions Timeline Card -->
        <div class="col-lg-5 col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-3"><i class="bx bx-message-rounded-dots text-primary"></i> المحادثة بين المالك والعامل</h5>
                    
                    <div class="chat-container">
                        @forelse($session->chats as $chat)
                            <!-- Chat Bubble -->
                            <div class="chat-bubble {{ $chat->owner_id ? 'chat-owner' : 'chat-worker' }}">
                                <div class="fw-bold font-12 mb-1">
                                    @if($chat->owner_id)
                                        <i class="bx bx-user"></i> المالك ({{ $chat->owner ? ($chat->owner->fname . ' ' . $chat->owner->lname) : ($session->worker && $session->worker->owner ? ($session->worker->owner->fname . ' ' . $session->worker->owner->lname) : 'المالك') }})
                                    @else
                                        <i class="bx bx-run"></i> العامل ({{ $chat->worker ? $chat->worker->full_name : ($session->worker ? $session->worker->full_name : 'العامل') }})
                                    @endif
                                </div>
                                
                                @if($chat->content_type == 'text')
                                    <p class="mb-0 font-14">{{ $chat->content }}</p>
                                @elseif($chat->content_type == 'image')
                                    <img src="{{ asset($chat->content) }}" class="img-fluid rounded border my-2" style="max-height: 150px; cursor: pointer;" onclick="window.open(this.src)">
                                @elseif($chat->content_type == 'sound')
                                    <audio src="{{ asset($chat->content) }}" controls class="w-100 my-2" style="max-height: 40px;"></audio>
                                @endif

                                <span class="chat-meta">{{ $chat->created_at->format('H:i:s | Y-m-d') }}</span>
                            </div>
                        @empty
                            <div class="text-center text-muted py-5">
                                <i class="bx bx-message-alt-x font-24 d-block mb-1"></i>
                                لم يتم تسجيل أي رسائل محادثة في هذه الجلسة.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Speed Logs Table Card -->
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title fw-bold mb-3"><i class="bx bx-list-ol text-info"></i> سجل السرعات والمواقع التفصيلي</h5>
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="speedLogsTable" style="width: 100%">
                    <thead>
                        <tr>
                            <th>الرقم</th>
                            <th>السرعة المسجلة</th>
                            <th>خط العرض (Latitude)</th>
                            <th>خط الطول (Longitude)</th>
                            <th>اسم/وصف الموقع</th>
                            <th>وقت التسجيل</th>
                        </tr>
                    </thead>
                    <tbody id="speed-logs-tbody">
                        @forelse($session->speedLogs as $key => $log)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td><span class="badge bg-info text-dark font-13 fw-bold">{{ number_format($log->speed, 2) }} كم/س</span></td>
                                <td>{{ $log->latitude }}</td>
                                <td>{{ $log->longitude }}</td>
                                <td>
                                    @if($log->location_name)
                                        <i class="bx bx-map text-danger"></i> {{ $log->location_name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">لا يوجد سجلات سرعة متوفرة لهذه الجلسة التدريبية.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Commented out old map script for future restoration
@if(($session->latitude && $session->longitude) || $session->speedLogs->count() > 0)
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&v=weekly"></script>
<script>
    // Helper function to convert a rectangular image into a circular marker with a white border and shadow
    function createCircularMarker(imgUrl, size, borderSize, borderColor, callback) {
        var img = new Image();
        img.crossOrigin = "anonymous";
        img.src = imgUrl;
        img.onload = function() {
            var canvas = document.createElement("canvas");
            canvas.width = size;
            canvas.height = size;
            var ctx = canvas.getContext("2d");
            
            ctx.clearRect(0, 0, size, size);
            var radius = size / 2;
            
            // Draw drop shadow
            ctx.shadowColor = "rgba(0, 0, 0, 0.3)";
            ctx.shadowBlur = 5;
            ctx.shadowOffsetX = 0;
            ctx.shadowOffsetY = 3;
            
            // Draw outer border circle
            ctx.beginPath();
            ctx.arc(radius, radius, radius - borderSize, 0, 2 * Math.PI);
            ctx.fillStyle = borderColor || "#ffffff";
            ctx.fill();
            
            // Reset shadow
            ctx.shadowColor = "transparent";
            
            // Clip path for circular image
            ctx.save();
            ctx.beginPath();
            ctx.arc(radius, radius, radius - borderSize - 1, 0, 2 * Math.PI);
            ctx.clip();
            
            // Draw image inside circle
            ctx.drawImage(img, borderSize, borderSize, size - 2 * borderSize, size - 2 * borderSize);
            ctx.restore();
            
            callback(canvas.toDataURL());
        };
        img.onerror = function() {
            // Fallback to original image URL
            callback(imgUrl);
        };
    }

    function initMap() {
        // Collect coordinates from logs
        var pathCoords = [];
        @foreach($session->speedLogs as $log)
            pathCoords.push({ lat: {{ (double)$log->latitude }}, lng: {{ (double)$log->longitude }} });
        @endforeach

        // Initial coordinates (session base coordinate)
        var startLat = {{ $session->latitude ?? 0 }};
        var startLng = {{ $session->longitude ?? 0 }};

        // Determine center point
        var centerLat = startLat;
        var centerLng = startLng;
        
        if (pathCoords.length > 0) {
            centerLat = pathCoords[pathCoords.length - 1].lat;
            centerLng = pathCoords[pathCoords.length - 1].lng;
        }

        // Initialize Google Map
        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 14,
            center: { lat: centerLat, lng: centerLng },
            mapTypeId: 'terrain' // nice terrain view for camel race tracks
        });

        // Render Marker at Start Point
        if (startLat && startLng) {
            var startMarker = new google.maps.Marker({
                position: { lat: startLat, lng: startLng },
                map: map,
                title: "نقطة بداية الجلسة"
            });

            var startInfoWindow = new google.maps.InfoWindow({
                content: "<b>نقطة بداية الجلسة</b><br>{{ $session->location_name ?? '' }}"
            });

            startMarker.addListener("click", function () {
                startInfoWindow.open(map, startMarker);
            });
        }

        // Draw Polyline and Add Marker for logged path
        if (pathCoords.length > 0) {
            // Draw Path Line (Double-layer for Google Maps navigation look)
            var outerPolyline = new google.maps.Polyline({
                path: pathCoords,
                geodesic: true,
                strokeColor: '#0d6efd',
                strokeOpacity: 0.3,
                strokeWeight: 8,
                lineCap: 'round',
                lineJoin: 'round'
            });
            outerPolyline.setMap(map);

            var innerPolyline = new google.maps.Polyline({
                path: pathCoords,
                geodesic: true,
                strokeColor: '#0056b3',
                strokeOpacity: 1.0,
                strokeWeight: 4,
                lineCap: 'round',
                lineJoin: 'round'
            });
            innerPolyline.setMap(map);

            // Fit Bounds
            var bounds = new google.maps.LatLngBounds();
            for (var i = 0; i < pathCoords.length; i++) {
                bounds.extend(pathCoords[i]);
            }
            if (startLat && startLng) {
                bounds.extend({ lat: startLat, lng: startLng });
            }
            map.fitBounds(bounds);

            // Define current position as the last logged coordinate
            var currentPos = pathCoords[pathCoords.length - 1];

            // Render circular current position marker with the app's logo
            createCircularMarker("{{ asset('backend/assets/images/logo-icon.png') }}", 48, 3, "#ffffff", function(circularIconUrl) {
                var currentMarker = new google.maps.Marker({
                    position: currentPos,
                    map: map,
                    title: "الموقع الحالي للهجن",
                    icon: {
                        url: circularIconUrl,
                        scaledSize: new google.maps.Size(48, 48),
                        origin: new google.maps.Point(0, 0),
                        anchor: new google.maps.Point(24, 24)
                    }
                });

                var workerName = {!! json_encode($session->worker ? $session->worker->full_name : 'غير محدد') !!};
                var latestLocation = {!! json_encode($session->speedLogs->last() && $session->speedLogs->last()->location_name ? $session->speedLogs->last()->location_name : ($session->location_name ?? 'غير محدد')) !!};
                var currentSpeed = "{{ number_format($session->speed, 2) }} كم/س";

                var currentInfoWindow = new google.maps.InfoWindow({
                    content: `<div style="text-align: right; direction: rtl; font-family: sans-serif; font-size: 13px; line-height: 1.6; padding: 4px;">` +
                             `<strong style="color: #0d6efd; font-size: 14px; display: block; margin-bottom: 6px;">الموقع الحالي للهجن</strong>` +
                             `<b>العامل المسؤول:</b> ${workerName}<br>` +
                             `<b>الموقع:</b> ${latestLocation}<br>` +
                             `<b>السرعة الحالية:</b> ${currentSpeed}` +
                             `</div>`
                });

                currentMarker.addListener("click", function () {
                    currentInfoWindow.open(map, currentMarker);
                });
            });
        }
    }

    // Load Map
    window.onload = initMap;
</script>
@endif
--}}

<!-- Google Maps API and Path Rendering (New Live Simulation Code) -->
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&v=weekly"></script>
<script>
    // Helper function to convert a rectangular image into a circular marker with a white border and shadow
    function createCircularMarker(imgUrl, size, borderSize, borderColor, callback) {
        var img = new Image();
        img.crossOrigin = "anonymous";
        img.src = imgUrl;
        img.onload = function() {
            var canvas = document.createElement("canvas");
            canvas.width = size;
            canvas.height = size;
            var ctx = canvas.getContext("2d");
            
            ctx.clearRect(0, 0, size, size);
            var radius = size / 2;
            
            // Draw drop shadow
            ctx.shadowColor = "rgba(0, 0, 0, 0.3)";
            ctx.shadowBlur = 5;
            ctx.shadowOffsetX = 0;
            ctx.shadowOffsetY = 3;
            
            // Draw outer border circle
            ctx.beginPath();
            ctx.arc(radius, radius, radius - borderSize, 0, 2 * Math.PI);
            ctx.fillStyle = borderColor || "#ffffff";
            ctx.fill();
            
            // Reset shadow
            ctx.shadowColor = "transparent";
            
            // Clip path for circular image
            ctx.save();
            ctx.beginPath();
            ctx.arc(radius, radius, radius - borderSize - 1, 0, 2 * Math.PI);
            ctx.clip();
            
            // Draw image inside circle
            ctx.drawImage(img, borderSize, borderSize, size - 2 * borderSize, size - 2 * borderSize);
            ctx.restore();
            
            callback(canvas.toDataURL());
        };
        img.onerror = function() {
            callback(imgUrl);
        };
    }

    // Smooth marker position interpolation (Smooth animation) and dynamic line drawing
    function animateMarker(marker, startPos, endPos, duration) {
        const startTime = performance.now();

        function step(currentTime) {
            const elapsed = currentTime - startTime;
            const fraction = Math.min(elapsed / duration, 1);

            const lat = startPos.lat + (endPos.lat - startPos.lat) * fraction;
            const lng = startPos.lng + (endPos.lng - startPos.lng) * fraction;
            
            const currentPos = new google.maps.LatLng(lat, lng);
            marker.setPosition(currentPos);

            // Dynamically pan map if tracking mode is active (keeping camera behind/camel in lower third)
            if (isTrackingMode && map) {
                var offset = getOffsetLatLng(lat, lng, currentBearing, 80); // 80 meters ahead of camel
                map.setCenter(new google.maps.LatLng(offset.lat, offset.lng));
            }

            // Update polyline path to end exactly at current animated marker position
            const tempPath = [...pathCoords, { lat: lat, lng: lng }];
            if (outerPolyline) outerPolyline.setPath(tempPath);
            if (innerPolyline) innerPolyline.setPath(tempPath);

            if (fraction < 1) {
                requestAnimationFrame(step);
            } else {
                // Animation complete: commit the final coordinate to the permanent path
                pathCoords.push(endPos);
                if (outerPolyline) outerPolyline.setPath(pathCoords);
                if (innerPolyline) innerPolyline.setPath(pathCoords);
            }
        }

        requestAnimationFrame(step);
    }

    var map;
    var outerPolyline;
    var innerPolyline;
    var currentMarker;
    var pathCoords = [];
    var circularMarkerUrl = "";
    var isSimulating = false;
    var isTrackingMode = false;
    var currentBearing = 0;
    var simulationTimer = null;
    var durationTimer = null;

    // Helper to calculate bearing (direction angle) between two coordinates
    function calculateBearing(startLat, startLng, endLat, endLng) {
        var dLng = (endLng - startLng) * Math.PI / 180;
        var lat1 = startLat * Math.PI / 180;
        var lat2 = endLat * Math.PI / 180;
        var y = Math.sin(dLng) * Math.cos(lat2);
        var x = Math.cos(lat1) * Math.sin(lat2) -
                Math.sin(lat1) * Math.cos(lat2) * Math.cos(dLng);
        var brng = Math.atan2(y, x) * 180 / Math.PI;
        return (brng + 360) % 360;
    }

    // Helper to calculate an offset coordinate along a bearing (to keep camera behind)
    function getOffsetLatLng(lat, lng, bearing, distanceMeters) {
        var R = 6378137; // Earth radius in meters
        var d = distanceMeters;
        var brng = bearing * Math.PI / 180;
        
        var lat1 = lat * Math.PI / 180;
        var lon1 = lng * Math.PI / 180;
        
        var lat2 = Math.asin(Math.sin(lat1) * Math.cos(d/R) +
                      Math.cos(lat1) * Math.sin(d/R) * Math.cos(brng));
                      
        var lon2 = lon1 + Math.atan2(Math.sin(brng) * Math.sin(d/R) * Math.cos(lat1),
                         Math.cos(d/R) - Math.sin(lat1) * Math.sin(lat2));
                         
        return {
            lat: lat2 * 180 / Math.PI,
            lng: lon2 * 180 / Math.PI
        };
    }

    // Helpers for duration timer
    function timeStringToSeconds(timeStr) {
        if (!timeStr) return 0;
        const parts = timeStr.trim().split(':');
        if (parts.length !== 3) return 0;
        return (parseInt(parts[0], 10) || 0) * 3600 +
               (parseInt(parts[1], 10) || 0) * 60 +
               (parseInt(parts[2], 10) || 0);
    }

    function secondsToTimeString(totalSecs) {
        const hrs = Math.floor(totalSecs / 3600);
        const mins = Math.floor((totalSecs % 3600) / 60);
        const secs = totalSecs % 60;
        const pad = (num) => String(num).padStart(2, '0');
        return `${pad(hrs)}:${pad(mins)}:${pad(secs)}`;
    }

    // Load coordinates from speed logs initially
    @foreach($session->speedLogs as $log)
        pathCoords.push({ lat: {{ (double)$log->latitude }}, lng: {{ (double)$log->longitude }} });
    @endforeach

    // Initial coordinates (session base coordinate - Al Marmoom)
    var startLat = {{ $session->latitude ?? 24.963000 }};
    var startLng = {{ $session->longitude ?? 55.480000 }};

    function initMap() {
        var centerLat = startLat;
        var centerLng = startLng;
        
        if (pathCoords.length > 0) {
            centerLat = pathCoords[pathCoords.length - 1].lat;
            centerLng = pathCoords[pathCoords.length - 1].lng;
        }

        // Initialize Google Map
        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 15,
            center: { lat: centerLat, lng: centerLng },
            mapTypeId: 'roadmap',
            tilt: 0,
            heading: 0,
            mapId: 'DEMO_MAP_ID'
        });

        // Render Marker at Start Point
        if (startLat && startLng) {
            var startMarker = new google.maps.Marker({
                position: { lat: startLat, lng: startLng },
                map: map,
                title: "نقطة بداية الجلسة"
            });

            var startInfoWindow = new google.maps.InfoWindow({
                content: "<b>نقطة بداية الجلسة</b><br>{{ $session->location_name ?? '' }}"
            });

            startMarker.addListener("click", function () {
                startInfoWindow.open(map, startMarker);
            });
        }

        // Setup Polylines
        outerPolyline = new google.maps.Polyline({
            path: pathCoords,
            geodesic: true,
            strokeColor: '#0d6efd',
            strokeOpacity: 0.3,
            strokeWeight: 8,
            lineCap: 'round',
            lineJoin: 'round',
            map: map
        });

        innerPolyline = new google.maps.Polyline({
            path: pathCoords,
            geodesic: true,
            strokeColor: '#0056b3',
            strokeOpacity: 1.0,
            strokeWeight: 4,
            lineCap: 'round',
            lineJoin: 'round',
            map: map
        });

        // Fit Bounds
        if (pathCoords.length > 0) {
            var bounds = new google.maps.LatLngBounds();
            for (var i = 0; i < pathCoords.length; i++) {
                bounds.extend(pathCoords[i]);
            }
            if (startLat && startLng) {
                bounds.extend({ lat: startLat, lng: startLng });
            }
            map.fitBounds(bounds);
        }

        // Generate circular icon and setup camel marker
        createCircularMarker("{{ asset('backend/assets/images/logo-icon.png') }}", 48, 3, "#ffffff", function(circularIconUrl) {
            circularMarkerUrl = circularIconUrl;
            
            var initialPos = (pathCoords.length > 0) ? pathCoords[pathCoords.length - 1] : { lat: startLat, lng: startLng };
            
            currentMarker = new google.maps.Marker({
                position: initialPos,
                map: map,
                title: "الموقع الحالي للهجن",
                icon: {
                    url: circularMarkerUrl,
                    scaledSize: new google.maps.Size(48, 48),
                    origin: new google.maps.Point(0, 0),
                    anchor: new google.maps.Point(24, 24)
                }
            });

            setupInfoWindow();
        });

        // Setup click handler for start/pause simulation
        document.getElementById('btn-start-simulation').addEventListener('click', function() {
            var btn = this;
            if (isSimulating) {
                // Pause simulation
                clearInterval(simulationTimer);
                clearInterval(durationTimer);
                isSimulating = false;
                btn.classList.remove('btn-warning');
                btn.classList.add('btn-success');
                btn.innerHTML = `<i class="bx bx-play-circle"></i> بدء محاكاة حركة المطية`;
            } else {
                // Start simulation
                isSimulating = true;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-warning');
                btn.innerHTML = `<i class="bx bx-pause-circle"></i> إيقاف مؤقت للمحاكاة`;
                
                // Start duration timer
                var durationEl = document.getElementById('card-session-duration');
                var seconds = timeStringToSeconds(durationEl.innerText);
                durationTimer = setInterval(function() {
                    seconds++;
                    durationEl.innerText = secondsToTimeString(seconds);
                }, 1000);
                
                // Trigger ping immediately, then every 3 seconds
                triggerGpsSimulatePing();
                simulationTimer = setInterval(triggerGpsSimulatePing, 3000);
            }
        });

        // Setup click handler for navigation follow/tracking mode
        document.getElementById('btn-track-mode').addEventListener('click', function() {
            var btn = this;
            var compass = document.getElementById('compass-container');
            if (isTrackingMode) {
                // Turn off tracking mode
                isTrackingMode = false;
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-primary');
                btn.innerHTML = `<i class="bx bx-navigation"></i> تفعيل وضع التتبع`;
                if (compass) compass.style.display = 'none';
                
                // Restore default zoom, tilt, heading and fit bounds to show entire path
                map.setZoom(15);
                map.setTilt(0);
                map.setHeading(0);
                
                var needle = document.getElementById('compass-needle');
                if (needle) {
                    needle.style.transform = 'rotate(0deg)';
                }

                if (pathCoords.length > 0) {
                    var bounds = new google.maps.LatLngBounds();
                    for (var i = 0; i < pathCoords.length; i++) {
                        bounds.extend(pathCoords[i]);
                    }
                    if (startLat && startLng) {
                        bounds.extend({ lat: startLat, lng: startLng });
                    }
                    map.fitBounds(bounds);
                }
            } else {
                // Turn on tracking mode
                isTrackingMode = true;
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-primary');
                btn.innerHTML = `<i class="bx bx-navigation"></i> وضع التتبع نشط`;
                if (compass) compass.style.display = 'flex';
                
                // Zoom in close, tilt (45 degrees) and rotate heading based on direction
                map.setZoom(17);
                map.setTilt(45);
                
                var bearingVal = 0;
                if (pathCoords.length >= 2) {
                    var pPrev = pathCoords[pathCoords.length - 2];
                    var pLast = pathCoords[pathCoords.length - 1];
                    bearingVal = calculateBearing(pPrev.lat, pPrev.lng, pLast.lat, pLast.lng);
                }
                currentBearing = bearingVal;
                map.setHeading(bearingVal);
                
                var needleRotation = 360 - bearingVal;
                var needle = document.getElementById('compass-needle');
                if (needle) {
                    needle.style.transform = 'rotate(' + needleRotation + 'deg)';
                }

                if (currentMarker) {
                    var mPos = currentMarker.getPosition();
                    var offset = getOffsetLatLng(mPos.lat(), mPos.lng(), currentBearing, 80);
                    map.panTo(new google.maps.LatLng(offset.lat, offset.lng));
                }
            }
        });

        // Setup click handler for clearing logs
        document.getElementById('btn-clear-logs').addEventListener('click', function() {
            if (confirm('هل أنت متأكد من رغبتك في حذف جميع السجلات وإعادة تهيئة الإحصائيات؟')) {
                // Stop simulation if running
                if (isSimulating) {
                    document.getElementById('btn-start-simulation').click();
                } else {
                    clearInterval(durationTimer);
                }
                
                fetch("{{ route('details.training.session.clearLogs', $session->id) }}")
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // 1. Reset variables and map polylines
                            pathCoords = [];
                            outerPolyline.setPath([]);
                            innerPolyline.setPath([]);
                            
                            // 2. Move marker back to starting position
                            var startPos = new google.maps.LatLng(startLat, startLng);
                            if (currentMarker) {
                                currentMarker.setPosition(startPos);
                            }
                            map.setCenter(startPos);
                            map.setZoom(15);
                            
                            // 3. Reset Stats Card UI
                            document.getElementById('card-current-speed').innerHTML = `0.00 <span class="font-14">كم/س</span>`;
                            document.getElementById('card-average-speed').innerHTML = `0.00 <span class="font-14">كم/س</span>`;
                            document.getElementById('card-distance').innerHTML = `0.00 <span class="font-14">كم</span>`;
                            document.getElementById('card-session-duration').innerText = `00:00:00`;
                            
                            // 4. Update info window text if active
                            var infoSpeedSpan = document.getElementById('info-current-speed');
                            if (infoSpeedSpan) {
                                infoSpeedSpan.innerText = `0.00 كم/س`;
                            }
                            
                            // 5. Reset speed logs table body
                            document.getElementById('speed-logs-tbody').innerHTML = `
                                <tr>
                                    <td colspan="6" class="text-center text-muted">لا يوجد سجلات سرعة متوفرة لهذه الجلسة التدريبية.</td>
                                </tr>
                            `;
                            
                            // Show success message
                            toastr.success(data.message);
                        }
                    })
                    .catch(err => {
                        console.error("Error clearing logs:", err);
                        toastr.error("حدث خطأ أثناء حذف السجلات.");
                    });
            }
        });
    }

    function setupInfoWindow() {
        if (!currentMarker) return;
        
        var workerName = {!! json_encode($session->worker ? $session->worker->full_name : 'غير حدد') !!};
        var currentSpeed = document.getElementById('card-current-speed').innerText.trim() || "{{ number_format($session->speed, 2) }} كم/س";
        
        var currentInfoWindow = new google.maps.InfoWindow({
            content: `<div id="marker-info-window" style="text-align: right; direction: rtl; font-family: sans-serif; font-size: 13px; line-height: 1.6; padding: 4px;">` +
                     `<strong style="color: #0d6efd; font-size: 14px; display: block; margin-bottom: 6px;">الموقع الحالي للهجن</strong>` +
                     `<b>العامل المسؤول:</b> ${workerName}<br>` +
                     `<b>السرعة الحالية:</b> <span id="info-current-speed">${currentSpeed}</span>` +
                     `</div>`
        });

        currentMarker.addListener("click", function () {
            currentInfoWindow.open(map, currentMarker);
        });
    }

    function triggerGpsSimulatePing() {
        var currentDuration = document.getElementById('card-session-duration').innerText.trim();
        fetch("{{ route('details.training.session.simulate', $session->id) }}?duration=" + encodeURIComponent(currentDuration))
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    var newCoord = { lat: parseFloat(data.log.latitude), lng: parseFloat(data.log.longitude) };
                    
                    // 1. Get old coordinates
                    var oldCoord = { lat: currentMarker.getPosition().lat(), lng: currentMarker.getPosition().lng() };
                    
                    // Update compass needle rotation based on travel bearing
                    var bearing = calculateBearing(oldCoord.lat, oldCoord.lng, newCoord.lat, newCoord.lng);
                    currentBearing = bearing;
                    var needle = document.getElementById('compass-needle');
                    if (needle) {
                        var needleRotation = isTrackingMode ? (360 - bearing) : bearing;
                        needle.style.transform = 'rotate(' + needleRotation + 'deg)';
                    }

                    // Dynamically rotate map in tracking mode so the camel's movement direction is always UP
                    if (isTrackingMode && map) {
                        map.setTilt(45);
                        map.setHeading(bearing);
                    }

                    // 2. Smoothly animate camel marker and dynamic polyline path from old to new position
                    animateMarker(currentMarker, oldCoord, newCoord, 2800);
                    
                    // 3. Pan map smoothly (if not in live tracking frame-by-frame mode)
                    if (!isTrackingMode) {
                        map.panTo(newCoord);
                    }
                    
                    // 5. Update HTML stats cards
                    document.getElementById('card-current-speed').innerHTML = `${data.current_speed} <span class="font-14">كم/س</span>`;
                    document.getElementById('card-average-speed').innerHTML = `${data.average_speed} <span class="font-14">كم/س</span>`;
                    document.getElementById('card-distance').innerHTML = `${data.distance} <span class="font-14">كم</span>`;
                    
                    // Update InfoWindow speed text if it's open
                    var infoSpeedSpan = document.getElementById('info-current-speed');
                    if (infoSpeedSpan) {
                        infoSpeedSpan.innerText = `${data.current_speed} كم/س`;
                    }
                    
                    // 6. Append new row to speed logs table with nice fade-in animation
                    var tbody = document.getElementById('speed-logs-tbody');
                    
                    // Clear empty table message if exists
                    if (tbody.innerHTML.includes('لا يوجد سجلات') || tbody.innerHTML.includes('لا يوجد سجل')) {
                        tbody.innerHTML = '';
                    }
                    
                    var rowCount = tbody.querySelectorAll('tr').length + 1;
                    var newRow = `
                        <tr style="animation: fadeIn 0.8s ease-in-out; background-color: rgba(40, 167, 69, 0.05);">
                            <td>${rowCount}</td>
                            <td><span class="badge bg-info text-dark font-13 fw-bold">${data.log.speed} كم/س</span></td>
                            <td>${data.log.latitude}</td>
                            <td>${data.log.longitude}</td>
                            <td><i class="bx bx-map text-danger"></i> ${data.log.location_name}</td>
                            <td>${data.log.time}</td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('afterbegin', newRow);
                }
            })
            .catch(err => {
                console.error("Simulation error:", err);
            });
    }

    window.onload = initMap;
</script>

<!-- CSS Keyframes for smooth table row insert -->
<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>


@endsection
