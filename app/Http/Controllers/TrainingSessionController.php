<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CamelWorker;
use App\Models\TrainingSession;
use App\Models\NotificationForApp;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TrainingSessionController extends Controller
{
    /**
     * Display a listing of training sessions with filters.
     */
    public function index(Request $request)
    {
        $owners = User::where('role', 'owner')->latest()->get();
        $workersList = CamelWorker::latest()->get();

        $query = TrainingSession::with(['worker.owner']);

        // Filter by specific worker
        if ($request->filled('camel_worker_id')) {
            $query->where('camel_worker_id', $request->camel_worker_id);
        }

        // Filter by owner (shows sessions for all workers of this owner)
        if ($request->filled('owner_id')) {
            $workerIds = CamelWorker::where('owner_id', $request->owner_id)->pluck('id');
            $query->whereIn('camel_worker_id', $workerIds);
        }

        // Filter by status (active vs ended)
        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->whereIn('round_status', ['pending', 'working', 'stop']);
            } elseif ($request->status == 'ended') {
                $query->where('round_status', 'end');
            }
        }

        $sessions = $query->latest()->get();

        return view('admin.training_session.all_sessions', compact('owners', 'workersList', 'sessions'));
    }

    /**
     * Display the specified training session details.
     */
    public function show($id)
    {
        $session = TrainingSession::with([
            'worker.owner',
            'speedLogs' => function ($q) {
                $q->orderBy('created_at', 'asc');
            },
            'chats' => function ($q) {
                $q->orderBy('created_at', 'asc');
            }
        ])->findOrFail($id);

        return view('admin.training_session.session_details', compact('session'));
    }

    /**
     * Remove the specified training session.
     */
    public function destroy($id)
    {
        $session = TrainingSession::findOrFail($id);
        $session->delete();

        $notification = [
            'message'    => 'تم حذف الجلسة التدريبية بنجاح',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.training.sessions')->with($notification);
    }

    /**
     * Simulate a GPS ping for the training session and save it.
     */
    public function simulatePing($id)
    {
        $session = TrainingSession::findOrFail($id);
        $logsCount = $session->logs_count ?? 0;
        
        $baseLat = $session->latitude ? (double)$session->latitude : 24.963000;
        $baseLng = $session->longitude ? (double)$session->longitude : 55.480000;
        
        $lat = null;
        $lng = null;
        $isRoadRoute = false;
        
        // 1. Try to get cached road route
        $cacheKey = "session_route_{$id}";
        $routePoints = \Illuminate\Support\Facades\Cache::get($cacheKey);
        
        if (!$routePoints) {
            $apiKey = config('services.google_maps.key');
            if ($apiKey) {
                // Determine a destination that is about 1.5 - 2km away
                $destLat = $baseLat + 0.015;
                $destLng = $baseLng + 0.015;
                
                $url = "https://maps.googleapis.com/maps/api/directions/json?origin={$baseLat},{$baseLng}&destination={$destLat},{$destLng}&mode=driving&key={$apiKey}";
                
                try {
                    $response = @file_get_contents($url);
                    if ($response) {
                        $data = json_decode($response, true);
                        if (isset($data['status']) && $data['status'] === 'OK' && isset($data['routes'][0]['overview_polyline']['points'])) {
                            $encodedPolyline = $data['routes'][0]['overview_polyline']['points'];
                            $points = $this->decodePolyline($encodedPolyline);
                            
                            if (count($points) > 0) {
                                // Interpolate intermediate points to make the movements slow and smooth
                                $points = $this->interpolatePoints($points, 0.005); // ~5 meters max per step
                                
                                // Create a back-and-forth loop to avoid sudden jump at the end
                                $reversed = array_reverse($points);
                                array_shift($reversed);
                                array_pop($reversed);
                                $routePoints = array_merge($points, $reversed);
                                
                                \Illuminate\Support\Facades\Cache::put($cacheKey, $routePoints, 3600); // cache for 1 hour
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore and fallback
                }
            }
        }
        
        if ($routePoints && count($routePoints) > 0) {
            $index = $logsCount % count($routePoints);
            $lat = $routePoints[$index]['latitude'];
            $lng = $routePoints[$index]['longitude'];
            $isRoadRoute = true;
        } else {
            // Fallback: Shift center of parametric oval path so that the path starts exactly at ($baseLat, $baseLng) when logsCount = 0
            $angle = $logsCount * 0.02; // Reduced angle increment to make the fallback simulation slow and moderate
            $radius = 0.005; // ~500m radius
            $centerLat = $baseLat;
            $centerLng = $baseLng - ($radius * 1.5);
            
            $lat = $centerLat + $radius * sin($angle);
            $lng = $centerLng + $radius * 1.5 * cos($angle);
        }
        
        // Moderate training speed (e.g. 12 - 25 km/h)
        $speed = rand(120, 250) / 10;
        
        // Calculate actual distance between last point and new point
        $distanceIncrement = 0.03; // default fallback increment in km
        $cacheKeyLastCoord = "session_last_coordinate_{$id}";
        $lastCoordinate = \Illuminate\Support\Facades\Cache::get($cacheKeyLastCoord);
        
        if ($lastCoordinate) {
            $distanceIncrement = $this->getDistance($lastCoordinate['latitude'], $lastCoordinate['longitude'], $lat, $lng);
            // If the increment is unusually large (due to looping back or a jump), cap it or use default
            if ($distanceIncrement > 0.5) {
                $distanceIncrement = 0.03;
            }
        } else {
            $lastLog = \App\Models\SessionSpeedLog::where('training_session_id', $id)->orderBy('id', 'desc')->first();
            if ($lastLog) {
                $distanceIncrement = $this->getDistance($lastLog->latitude, $lastLog->longitude, $lat, $lng);
                if ($distanceIncrement > 0.5) {
                    $distanceIncrement = 0.03;
                }
            } else {
                // First point from start position
                $distanceIncrement = $this->getDistance($baseLat, $baseLng, $lat, $lng);
                if ($distanceIncrement > 0.5) {
                    $distanceIncrement = 0.03;
                }
            }
        }
        
        // Cache the current coordinate
        \Illuminate\Support\Facades\Cache::put($cacheKeyLastCoord, [
            'latitude' => $lat,
            'longitude' => $lng
        ], 300);
        
        $locationName = $isRoadRoute ? "طريق محاكاة فعلي #" . ($logsCount + 1) : "موقع المحاكاة اللحظي #" . ($logsCount + 1);
        
        $log = \App\Models\SessionSpeedLog::create([
            'training_session_id' => $id,
            'speed' => $speed,
            'latitude' => $lat,
            'longitude' => $lng,
            'location_name' => $locationName,
        ]);
        
        $newAvgSpeed = (($session->average_speed * $logsCount) + $speed) / ($logsCount + 1);
        $newDistance = $session->round_distance_km + $distanceIncrement;
        
        $updateData = [
            'speed' => $speed,
            'average_speed' => $newAvgSpeed,
            'round_distance_km' => $newDistance,
            'logs_count' => $logsCount + 1,
            'round_status' => 'working',
        ];
        
        if (request()->filled('duration')) {
            $updateData['round_time'] = request('duration');
        }
        
        $session->update($updateData);
        
        return response()->json([
            'success' => true,
            'log' => [
                'id' => $log->id,
                'speed' => number_format($log->speed, 2),
                'latitude' => (double)$log->latitude,
                'longitude' => (double)$log->longitude,
                'location_name' => $log->location_name,
                'time' => $log->created_at->format('Y-m-d H:i:s'),
            ],
            'current_speed' => number_format($speed, 2),
            'average_speed' => number_format($newAvgSpeed, 2),
            'distance' => number_format($newDistance, 2),
        ]);
    }

    /**
     * Clear all speed logs for the specified training session.
     */
    public function clearLogs($id)
    {
        $session = TrainingSession::findOrFail($id);
        
        // Delete logs from database
        \App\Models\SessionSpeedLog::where('training_session_id', $id)->delete();
        
        // Clear cached route and last coordinate
        \Illuminate\Support\Facades\Cache::forget("session_route_{$id}");
        \Illuminate\Support\Facades\Cache::forget("session_last_coordinate_{$id}");
        
        // Reset metrics
        $session->update([
            'speed' => 0.00,
            'average_speed' => 0.00,
            'round_distance_km' => 0.00,
            'logs_count' => 0,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'تم حذف جميع السجلات وإعادة تهيئة إحصائيات الجلسة بنجاح.',
        ]);
    }

    /**
     * Update the status of the specified training session.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,working,stop,end',
        ]);

        $session = TrainingSession::findOrFail($id);
        
        $updateData = [
            'round_status' => $request->status,
        ];
        
        if ($request->status === 'end') {
            $updateData['session_ended_at'] = now();
        }
        
        $session->update($updateData);

        // Broadcast the status update as well
        try {
            // We can broadcast a mock location updated or specific status event if needed,
            // but updating status via websocket is also covered here.
            $latestLog = \App\Models\SessionSpeedLog::where('training_session_id', $id)->orderBy('id', 'desc')->first();
            if ($latestLog) {
                broadcast(new \App\Events\LocationUpdated($latestLog))->toOthers();
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("WebSocket status broadcast failed: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الجلسة بنجاح.',
            'round_status' => $session->round_status,
        ]);
    }

    /**
     * Decode a Google Maps encoded polyline.
     */
    private function decodePolyline($encoded)
    {
        $length = strlen($encoded);
        $index = 0;
        $points = [];
        $lat = 0;
        $lng = 0;

        while ($index < $length) {
            $b = 0;
            $shift = 0;
            $result = 0;
            do {
                $b = ord($encoded[$index++]) - 63;
                $result |= ($b & 0x1f) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $dlat = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $lat += $dlat;

            $shift = 0;
            $result = 0;
            do {
                $b = ord($encoded[$index++]) - 63;
                $result |= ($b & 0x1f) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $dlng = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $lng += $dlng;

            $points[] = [
                'latitude' => $lat * 1e-5,
                'longitude' => $lng * 1e-5
            ];
        }
        return $points;
    }

    /**
     * Interpolate intermediate points between route coordinates to slow down speed.
     */
    private function interpolatePoints($points, $maxDistanceKm)
    {
        $interpolated = [];
        $count = count($points);
        if ($count === 0) return $points;
        
        for ($i = 0; $i < $count - 1; $i++) {
            $p1 = $points[$i];
            $p2 = $points[$i+1];
            
            $dist = $this->getDistance($p1['latitude'], $p1['longitude'], $p2['latitude'], $p2['longitude']);
            
            $interpolated[] = $p1;
            
            if ($dist > $maxDistanceKm) {
                $steps = ceil($dist / $maxDistanceKm);
                for ($j = 1; $j < $steps; $j++) {
                    $fraction = $j / $steps;
                    $interpolated[] = [
                        'latitude' => $p1['latitude'] + ($p2['latitude'] - $p1['latitude']) * $fraction,
                        'longitude' => $p1['longitude'] + ($p2['longitude'] - $p1['longitude']) * $fraction,
                    ];
                }
            }
        }
        $interpolated[] = $points[$count - 1];
        return $interpolated;
    }

    /**
     * API to fetch training sessions of workers belonging to a specific owner, with status filtering.
     */
    public function getSessionsByOwnerApi(Request $request)
    {
        $request->validate([
            'owner_id' => 'required|exists:users,id',
            'status'   => 'nullable|string|in:active,working,paused,stop,ended,end,all',
        ], [
            'owner_id.required' => 'حقل المالك مطلوب.',
            'owner_id.exists'   => 'المالك غير موجود.',
            'status.in'         => 'حالة التصفية غير صالحة.',
        ]);

        // Check if the provided owner_id belongs to a user with the owner role
        $owner = User::find($request->owner_id);
        if (!$owner || $owner->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'The provided owner_id does not belong to a valid owner.'
            ], 403);
        }

        // Auto sync worker online statuses
        CamelWorker::syncOnlineStatuses($request->owner_id);

        // Get all worker IDs for this owner
        $workerIds = CamelWorker::where('owner_id', $request->owner_id)->pluck('id');

        $query = TrainingSession::with(['worker']);

        $query->whereIn('camel_worker_id', $workerIds);

        // Apply status filtering (skip if status is 'all')
        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status;
            if ($status === 'active' || $status === 'working') {
                $query->whereIn('round_status', ['working', 'pending']);
            } elseif ($status === 'paused' || $status === 'stop') {
                $query->where('round_status', 'stop');
            } elseif ($status === 'ended' || $status === 'end') {
                $query->where('round_status', 'end');
            }
        }

        \Carbon\Carbon::setLocale('ar');
        $now = \Carbon\Carbon::now();

        $sessions = $query->latest()->get()->map(function ($session) use ($now) {
            $session->start_date_time = $session->created_at ? $session->created_at->format('Y-m-d H:i:s') : null;
            $session->end_date_time = $session->session_ended_at ? $session->session_ended_at->format('Y-m-d H:i:s') : null;
            $session->started_ago = $session->created_at ? $session->created_at->diffForHumans($now) : null;
            if ($session->session_ended_at) {
                $session->ended_ago = $session->session_ended_at->diffForHumans($now);
            } else {
                $session->ended_ago = ($session->round_status === 'end') ? 'منتهية' : 'نشطة حالياً';
            }
            return $session;
        });

        return response()->json([
            'success' => true,
            'sessions' => $sessions
        ], 200);
    }

    /**
     * API to fetch statistics and current active session details for an owner's dashboard.
     */
    public function getOwnerDashboardStatsApi(Request $request)
    {
        $request->validate([
            'owner_id' => 'required|exists:users,id',
        ], [
            'owner_id.required' => 'حقل المالك مطلوب.',
            'owner_id.exists'   => 'المالك غير موجود.',
        ]);

        // Check if the user is an owner
        $owner = User::find($request->owner_id);
        if (!$owner || $owner->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'معرّف المالك المقدم لا ينتمي لمالك صالح.'
            ], 403);
        }

        // Auto sync worker online statuses
        CamelWorker::syncOnlineStatuses($request->owner_id);

        // Get all worker IDs for this owner
        $workerIds = CamelWorker::where('owner_id', $request->owner_id)->pluck('id');

        // 1. Workers Count
        $workersCount = CamelWorker::where('owner_id', $request->owner_id)->count();

        // 2. Active sessions count (status: working, pending, stop)
        $activeSessionsCount = TrainingSession::whereIn('camel_worker_id', $workerIds)
            ->whereIn('round_status', ['working', 'pending', 'stop'])
            ->count();

        // 3. Completed sessions count (status: end)
        $completedSessionsCount = TrainingSession::whereIn('camel_worker_id', $workerIds)
            ->where('round_status', 'end')
            ->count();

        // 4. Details of the latest session (active or completed)
        // 4. Details of all active/paused sessions
        $activeSessions = TrainingSession::with(['worker'])
            ->whereIn('camel_worker_id', $workerIds)
            ->whereIn('round_status', ['working', 'pending', 'stop'])
            ->latest()
            ->get();

        // Fallback to the absolute latest session (completed or ended) if no active sessions exist
        if ($activeSessions->isEmpty()) {
            $latestSession = TrainingSession::with(['worker'])
                ->whereIn('camel_worker_id', $workerIds)
                ->latest()
                ->first();
            if ($latestSession) {
                $activeSessions = collect([$latestSession]);
            }
        }

        $activeSessionsData = [];
        foreach ($activeSessions as $session) {
            \Carbon\Carbon::setLocale('ar');
            $now = \Carbon\Carbon::now();

            $elapsedSeconds = $session->created_at ? abs($now->diffInSeconds($session->created_at, false)) : 0;
            $hours = floor($elapsedSeconds / 3600);
            $minutes = floor(($elapsedSeconds / 60) % 60);
            $seconds = $elapsedSeconds % 60;
            $elapsedTime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

            $activeSessionsData[] = [
                'id' => $session->id,
                'camel_worker_id' => $session->camel_worker_id,
                'location_name' => $session->location_name,
                'latitude' => $session->latitude,
                'longitude' => $session->longitude,
                'round_status' => $session->round_status,
                'speed' => $session->speed,
                'average_speed' => $session->average_speed,
                'round_distance_km' => $session->round_distance_km,
                'round_time' => $session->round_time ?: '00:00:00', // Client stored round time
                'performance' => $session->performance,
                'session_ended_at' => $session->session_ended_at,
                'created_at' => $session->created_at ? $session->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $session->updated_at ? $session->updated_at->format('Y-m-d H:i:s') : null,
                'start_date_time' => $session->created_at ? $session->created_at->format('Y-m-d H:i:s') : null,
                'started_ago' => $session->created_at ? $session->created_at->diffForHumans($now) : null,
                'elapsed_time' => $session->round_time ?: '00:00:00', // Formatted training time of the session
                'worker' => $session->worker,
            ];
        }

        return response()->json([
            'success' => true,
            'active_sessions_count' => $activeSessionsCount,
            'completed_sessions_count' => $completedSessionsCount,
            'workers_count' => $workersCount,
            'active_session' => count($activeSessionsData) > 0 ? $activeSessionsData[0] : null,
            'active_sessions' => $activeSessionsData,
        ], 200);
    }

    /**
     * API to fetch training sessions belonging to a specific worker, with status filtering.
     */
    public function getSessionsByWorkerApi(Request $request)
    {
        $request->validate([
            'worker_id' => 'required|exists:camel_workers,id',
            'status'    => 'nullable|string|in:active,working,paused,stop,ended,end,all',
            'limit'     => 'nullable|integer|min:0',
        ], [
            'worker_id.required' => 'معرف العامل مطلوب.',
            'worker_id.exists'   => 'العامل غير موجود.',
            'status.in'          => 'حالة التصفية غير صالحة.',
            'limit.integer'      => 'يجب أن يكون الحد رقمًا صحيحًا.',
            'limit.min'          => 'يجب أن يكون الحد 0 أو أكثر.',
        ]);

        $query = TrainingSession::with(['worker']);

        $query->where('camel_worker_id', $request->worker_id);

        // Apply status filtering (skip if status is 'all')
        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status;
            if ($status === 'active' || $status === 'working') {
                $query->whereIn('round_status', ['working', 'pending']);
            } elseif ($status === 'paused' || $status === 'stop') {
                $query->where('round_status', 'stop');
            } elseif ($status === 'ended' || $status === 'end') {
                $query->where('round_status', 'end');
            }
        }

        if ($request->filled('limit') && (int)$request->limit > 0) {
            $query->limit((int)$request->limit);
        }

        \Carbon\Carbon::setLocale('ar');
        $now = \Carbon\Carbon::now();

        $sessions = $query->latest()->get()->map(function ($session) use ($now) {
            $session->start_date_time = $session->created_at ? $session->created_at->format('Y-m-d H:i:s') : null;
            $session->end_date_time = $session->session_ended_at ? $session->session_ended_at->format('Y-m-d H:i:s') : null;
            $session->started_ago = $session->created_at ? $session->created_at->diffForHumans($now) : null;
            if ($session->session_ended_at) {
                $session->ended_ago = $session->session_ended_at->diffForHumans($now);
            } else {
                $session->ended_ago = ($session->round_status === 'end') ? 'منتهية' : 'نشطة حالياً';
            }
            return $session;
        });

        return response()->json([
            'success' => true,
            'sessions' => $sessions
        ], 200);
    }

    /**
     * API to fetch full training session details (worker, owner, speedLogs, chats, instructions).
     */
    public function getSessionDetailsApi(Request $request)
    {
        $id = $request->input('session_id') ?? $request->input('id');

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'معرف الجلسة مطلوب.'
            ], 422);
        }

        // Validate that the session exists
        $sessionExists = TrainingSession::where('id', $id)->exists();
        if (!$sessionExists) {
            return response()->json([
                'success' => false,
                'message' => 'الجلسة التدريبية غير موجودة.'
            ], 404);
        }

        $recentOnly = $request->boolean('recent_only') || $request->input('recent_only') == '1';
        $sinceId = $request->input('since_id') ? (int)$request->input('since_id') : null;

        $session = TrainingSession::with([
            'worker.owner',
            'speedLogs' => function ($q) use ($recentOnly, $sinceId) {
                if ($sinceId !== null) {
                    $q->where('id', '>', $sinceId)->orderBy('id', 'asc');
                } elseif ($recentOnly) {
                    $q->orderBy('id', 'desc')->take(25);
                } else {
                    $q->orderBy('created_at', 'asc');
                }
            },
            'chats' => function ($q) {
                $q->orderBy('created_at', 'asc');
            },
            'instructions' => function ($q) {
                $q->orderBy('created_at', 'asc');
            }
        ])->find($id);

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'الجلسة التدريبية غير موجودة.'
            ], 404);
        }

        // Inactivity watchdog for active training:
        // If session is marked 'working', verify worker is actively sending GPS updates.
        // During active training, updates are sent every 1-4 seconds.
        if ($session->round_status === 'working') {
            $latestLog = \App\Models\SessionSpeedLog::where('training_session_id', $session->id)->orderBy('id', 'desc')->first();
            $lastActivity = $latestLog ? $latestLog->created_at : $session->updated_at;
            $worker = $session->worker;

            $diffSeconds = $lastActivity ? now()->diffInSeconds($lastActivity) : 999;
            $isWorkerSilent = ($diffSeconds >= 8) ||
                (($worker && in_array($worker->online_status, ['background', 'offline'])) && $diffSeconds >= 6);

            if ($isWorkerSilent) {
                $session->round_status = 'stop';
                $session->save();

                if ($worker) {
                    $worker->online_status = 'offline';
                    $worker->is_online = 0;
                    $worker->save();
                    try {
                        broadcast(new \App\Events\WorkerStatusUpdated($worker));
                    } catch (\Exception $e) {}
                }

                try {
                    broadcast(new \App\Events\LocationUpdated($latestLog, null, $session));
                    broadcast(new \App\Events\TrainingSessionEnded($session));
                } catch (\Exception $e) {}

                $this->notifyOwnerWorkerClosedApp($session, $worker);
            }
        }

        if ($recentOnly && $sinceId === null && $session && $session->relationLoaded('speedLogs')) {
            $session->setRelation('speedLogs', $session->speedLogs->reverse()->values());
        }

        if ($session->round_status === 'working' && (empty($session->round_time) || $session->round_time === '00:00:00')) {
            $diffSec = $session->created_at ? abs(now()->diffInSeconds($session->created_at, false)) : 0;
            $h = floor($diffSec / 3600);
            $m = floor(($diffSec / 60) % 60);
            $s = $diffSec % 60;
            $session->round_time = sprintf('%02d:%02d:%02d', $h, $m, $s);
        }

        \Carbon\Carbon::setLocale('ar');
        $now = \Carbon\Carbon::now();

        $session->start_date_time = $session->created_at ? $session->created_at->format('Y-m-d H:i:s') : null;
        $session->end_date_time = $session->session_ended_at ? $session->session_ended_at->format('Y-m-d H:i:s') : null;
        $session->started_ago = $session->created_at ? $session->created_at->diffForHumans($now) : null;
        if ($session->session_ended_at) {
            $session->ended_ago = $session->session_ended_at->diffForHumans($now);
        } else {
            $session->ended_ago = ($session->round_status === 'end') ? 'منتهية' : ($session->round_status === 'stop' ? 'متوقفة مؤقتاً' : 'نشطة حالياً');
        }

        return response()->json([
            'success' => true,
            'session' => $session
        ], 200);
    }

    /**
     * API to start a new training session for a worker.
     */
    public function startSessionApi(Request $request)
    {
        $request->validate([
            'camel_worker_id' => 'required|exists:camel_workers,id',
            'location_name'   => 'nullable|string|max:255',
            'latitude'        => 'nullable|numeric|between:-90,90',
            'longitude'       => 'nullable|numeric|between:-180,180',
            'round_status'    => 'nullable|in:pending,working,stop,end',
        ], [
            'camel_worker_id.required' => 'معرف العامل مطلوب.',
            'camel_worker_id.exists'   => 'العامل غير موجود.',
            'latitude.numeric'         => 'خط العرض يجب أن يكون رقماً.',
            'longitude.numeric'        => 'خط الطول يجب أن يكون رقماً.',
            'round_status.in'          => 'حالة الجلسة غير صالحة.',
        ]);

        // End any active/pending sessions for this worker to prevent overlaps
        TrainingSession::where('camel_worker_id', $request->camel_worker_id)
            ->whereIn('round_status', ['pending', 'working', 'stop'])
            ->update([
                'round_status' => 'end',
                'session_ended_at' => now()
            ]);

        // Update worker status
        CamelWorker::where('id', $request->camel_worker_id)->update(['is_online' => 1, 'last_activity_at' => now()]);

        // Create the new tracking session
        $session = TrainingSession::create([
            'camel_worker_id' => $request->camel_worker_id,
            'location_name'   => $request->location_name,
            'latitude'        => $request->latitude,
            'longitude'       => $request->longitude,
            'round_status'    => $request->round_status ?? 'pending',
            'speed'           => 0.00,
            'average_speed'   => 0.00,
            'round_distance_km'=> 0.00,
            'round_time'      => '00:00:00',
            'performance'     => 0.00,
        ]);

        // Load worker details and associated owner
        $session->load('worker.owner');
        $worker = $session->worker;
        $owner = $worker ? $worker->owner : null;

        // Broadcast the start event to the owner channel
        try {
            broadcast(new \App\Events\TrainingSessionCreated($session));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("WebSocket training session created broadcast failed: " . $e->getMessage());
        }

        // Send Push Notification and create In-App Notification for Owner
        if ($owner) {
            try {
                \Carbon\Carbon::setLocale('ar');
                $startTimeFormatted = now()->format('h:i A');
                $locText = !empty($session->location_name) ? " في {$session->location_name}" : "";
                $workerName = $worker ? $worker->full_name : 'العامل';
                $notifTitle = "بدء تدريب جديد 🏃‍♂️";
                $notifBody = "بدأ العامل {$workerName} التدريب الآن الساعة ({$startTimeFormatted}){$locText}.";

                // Save in-app notification in database
                \App\Models\NotificationForApp::create([
                    'user_id'   => $owner->id,
                    'title'     => $notifTitle,
                    'des'       => $notifBody,
                    'date'      => now()->format('Y-m-d'),
                    'user_view' => 'no',
                    'created_at'=> now(),
                ]);

                // Send FCM Push Notification to owner's device
                $deviceToken = $owner->token ?: ($owner->firebase_token ?? null);
                \Illuminate\Support\Facades\Log::info("Training start push: owner_id={$owner->id}, token=" . (!empty($deviceToken) ? substr($deviceToken, 0, 15) . "..." : "NONE"));
                if (!empty($deviceToken)) {
                    $firebaseService = new \App\Services\FirebaseNotificationService();
                    $unreadCount = \App\Models\NotificationForApp::where('user_id', $owner->id)
                        ->where('user_view', 'no')
                        ->count();

                    $pushRes = $firebaseService->sendNotification(
                        $deviceToken,
                        $notifTitle,
                        $notifBody,
                        (string)$session->id,
                        'training_started',
                        $unreadCount
                    );
                    \Illuminate\Support\Facades\Log::info("Training start FCM push result: " . json_encode($pushRes));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Owner notification for training start failed: " . $e->getMessage());
            }
        }

        // Append started_ago and ended_ago fields
        \Carbon\Carbon::setLocale('ar');
        $session->start_date_time = $session->created_at ? $session->created_at->format('Y-m-d H:i:s') : null;
        $session->end_date_time = $session->session_ended_at ? $session->session_ended_at->format('Y-m-d H:i:s') : null;
        $session->started_ago = $session->created_at ? $session->created_at->diffForHumans() : null;
        $session->ended_ago = 'نشطة حالياً';

        return response()->json([
            'success' => true,
            'message' => 'تم بدء جلسة التتبع بنجاح.',
            'session' => $session
        ], 201);
    }

    /**
     * API to end an active training session.
     */
    public function endSessionApi(Request $request)
    {
        $id = $request->input('session_id') ?? $request->input('id');

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'معرف الجلسة مطلوب.'
            ], 422);
        }

        $session = TrainingSession::find($id);

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'الجلسة التدريبية غير موجودة.'
            ], 404);
        }

        if ($session->round_status === 'end') {
            return response()->json([
                'success' => false,
                'message' => 'هذه الجلسة منتهية بالفعل.'
            ], 422);
        }

        $request->validate([
            'summary_text'      => 'nullable|string',
            'summary_audio'     => 'nullable', // file or string path
            'summary_image'     => 'nullable', // file or string path
            'average_speed'     => 'nullable|numeric|min:0',
            'round_distance_km' => 'nullable|numeric|min:0',
            'round_time'        => 'nullable|string|max:255',
            'performance'       => 'nullable|numeric|min:0',
        ], [
            'average_speed.numeric'     => 'متوسط السرعة يجب أن يكون رقماً.',
            'round_distance_km.numeric' => 'المسافة يجب أن تكون رقماً.',
            'performance.numeric'       => 'الأداء يجب أن يكون رقماً.',
        ]);

        // Process summary audio
        $audioPath = $session->summary_audio;
        if ($request->hasFile('summary_audio')) {
            // Delete old file if exists
            if ($session->summary_audio && file_exists(public_path($session->summary_audio))) {
                @unlink(public_path($session->summary_audio));
            }
            
            $file = $request->file('summary_audio');
            $filename = date('YmdHi') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/summaries/audio'), $filename);
            $audioPath = 'upload/summaries/audio/' . $filename;
        } elseif ($request->has('summary_audio')) {
            if ($request->filled('summary_audio') && is_string($request->summary_audio) && $request->summary_audio !== "") {
                $audioPath = $request->summary_audio;
            } else {
                if ($session->summary_audio && file_exists(public_path($session->summary_audio))) {
                    @unlink(public_path($session->summary_audio));
                }
                $audioPath = null;
            }
        }

        // Process summary image
        $imagePath = $session->summary_image;
        if ($request->hasFile('summary_image')) {
            // Delete old file if exists
            if ($session->summary_image && file_exists(public_path($session->summary_image))) {
                @unlink(public_path($session->summary_image));
            }
            
            $file = $request->file('summary_image');
            $filename = date('YmdHi') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/summaries/images'), $filename);
            $imagePath = 'upload/summaries/images/' . $filename;
        } elseif ($request->has('summary_image')) {
            if ($request->filled('summary_image') && is_string($request->summary_image) && $request->summary_image !== "") {
                $imagePath = $request->summary_image;
            } else {
                if ($session->summary_image && file_exists(public_path($session->summary_image))) {
                    @unlink(public_path($session->summary_image));
                }
                $imagePath = null;
            }
        }

        // Update the session details and end it
        $session->update([
            'round_status'      => 'end',
            'session_ended_at'  => now(),
            'summary_text'      => $request->has('summary_text') ? $request->summary_text : $session->summary_text,
            'summary_audio'     => $audioPath,
            'summary_image'     => $imagePath,
            'average_speed'     => $request->has('average_speed') ? $request->average_speed : $session->average_speed,
            'round_distance_km' => $request->has('round_distance_km') ? $request->round_distance_km : $session->round_distance_km,
            'round_time'        => $request->has('round_time') ? $request->round_time : $session->round_time,
            'performance'       => $request->has('performance') ? $request->performance : $session->performance,
        ]);

        // Broadcast the ended status update to the WebSocket channel
        try {
            $latestLog = \App\Models\SessionSpeedLog::where('training_session_id', $id)->orderBy('id', 'desc')->first();
            broadcast(new \App\Events\LocationUpdated($latestLog, null, $session));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("WebSocket end session broadcast failed: " . $e->getMessage());
        }

        $session->load('worker.owner');
        $worker = $session->worker;
        $owner = $worker ? $worker->owner : null;

        // Broadcast the end event to the owner channel
        try {
            broadcast(new \App\Events\TrainingSessionEnded($session));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("WebSocket training session ended broadcast failed: " . $e->getMessage());
        }

        // Send Push Notification and create In-App Notification for Owner upon training completion
        if ($owner) {
            try {
                \Carbon\Carbon::setLocale('ar');
                $endTimeFormatted = now()->format('h:i A');
                $workerName = $worker ? $worker->full_name : 'العامل';
                $distText = !empty($session->round_distance_km) && $session->round_distance_km > 0
                    ? " (المسافة: {$session->round_distance_km} كم)"
                    : "";
                $durationText = !empty($session->round_time) && $session->round_time !== '00:00:00'
                    ? " واستغرقت {$session->round_time}"
                    : "";

                $notifTitle = "انتهاء جلسة التدريب 🏁";
                $notifBody = "أنهى العامل {$workerName} جلسة التدريب الآن الساعة ({$endTimeFormatted}){$durationText}{$distText}.";

                // Save in-app notification in database
                \App\Models\NotificationForApp::create([
                    'user_id'   => $owner->id,
                    'title'     => $notifTitle,
                    'des'       => $notifBody,
                    'date'      => now()->format('Y-m-d'),
                    'user_view' => 'no',
                    'created_at'=> now(),
                ]);

                // Send FCM Push Notification to owner's device
                $deviceToken = $owner->token ?: ($owner->firebase_token ?? null);
                \Illuminate\Support\Facades\Log::info("Training end push: owner_id={$owner->id}, token=" . (!empty($deviceToken) ? substr($deviceToken, 0, 15) . "..." : "NONE"));
                if (!empty($deviceToken)) {
                    $firebaseService = new \App\Services\FirebaseNotificationService();
                    $unreadCount = \App\Models\NotificationForApp::where('user_id', $owner->id)
                        ->where('user_view', 'no')
                        ->count();

                    $pushRes = $firebaseService->sendNotification(
                        $deviceToken,
                        $notifTitle,
                        $notifBody,
                        (string)$session->id,
                        'training_ended',
                        $unreadCount
                    );
                    \Illuminate\Support\Facades\Log::info("Training end FCM push result: " . json_encode($pushRes));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Owner notification for training end failed: " . $e->getMessage());
            }
        }

        // Append formatted datetimes and relative times
        \Carbon\Carbon::setLocale('ar');
        $now = \Carbon\Carbon::now();
        $session->start_date_time = $session->created_at ? $session->created_at->format('Y-m-d H:i:s') : null;
        $session->end_date_time = $session->session_ended_at ? $session->session_ended_at->format('Y-m-d H:i:s') : null;
        $session->started_ago = $session->created_at ? $session->created_at->diffForHumans($now) : null;
        $session->ended_ago = $session->session_ended_at ? $session->session_ended_at->diffForHumans($now) : 'منتهية';

        return response()->json([
            'success' => true,
            'message' => 'تم إنهاء جلسة التتبع بنجاح وتسجيل الملخص.',
            'session' => $session
        ], 200);
    }

    /**
     * Calculate distance between two coordinates in kilometers using Haversine formula.
     */
    private function getDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }

    /**
     * API to update the status of the specified training session.
     */
    public function updateStatusApi(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:training_sessions,id',
            'status' => 'required|in:pending,working,stop,end',
        ], [
            'session_id.required' => 'معرف الجلسة مطلوب.',
            'session_id.exists' => 'الجلسة غير موجودة.',
            'status.required' => 'الحالة مطلوبة.',
            'status.in' => 'الحالة غير صالحة.',
        ]);

        $session = TrainingSession::findOrFail($request->session_id);
        
        $oldStatus = $session->round_status;
        $newStatus = $request->status;

        $updateData = [
            'round_status' => $newStatus,
        ];
        
        if ($request->filled('round_time')) {
            $updateData['round_time'] = $request->round_time;
        } elseif ($request->filled('duration')) {
            $updateData['round_time'] = $request->duration;
        }

        if ($newStatus === 'end') {
            $updateData['session_ended_at'] = now();
        }
        
        $session->update($updateData);
        $session->refresh();

        // Load worker relation for owner channel
        $session->load('worker');

        // Broadcast status change or location update if logs exist
        try {
            $latestLog = \App\Models\SessionSpeedLog::where('training_session_id', $session->id)->orderBy('id', 'desc')->first();
            broadcast(new \App\Events\LocationUpdated($latestLog, null, $session));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("WebSocket status update log broadcast failed: " . $e->getMessage());
        }

        // Broadcast TrainingSessionCreated or TrainingSessionEnded to owner if applicable
        try {
            if ($newStatus === 'working') {
                \Illuminate\Support\Facades\Cache::forget("worker_closed_notif_sent_{$session->id}");
                if ($session->worker) {
                    $session->worker->update([
                        'is_online' => 1,
                        'online_status' => 'online',
                        'last_activity_at' => now(),
                    ]);
                    try {
                        broadcast(new \App\Events\WorkerStatusUpdated($session->worker));
                    } catch (\Exception $e) {}
                }
                if ($oldStatus === 'stop') {
                    broadcast(new \App\Events\TrainingSessionCreated($session));
                }
            } else if ($newStatus === 'stop' && $oldStatus === 'working') {
                broadcast(new \App\Events\TrainingSessionEnded($session));
            } else if ($newStatus === 'end') {
                broadcast(new \App\Events\TrainingSessionEnded($session));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("WebSocket status change owner broadcast failed: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الجلسة بنجاح.',
            'round_status' => $session->round_status,
            'session' => $session
        ], 200);
    }

    /**
     * API to handle worker completely closing the app during training session.
     * Pauses the session (round_status = 'stop'), preserves metrics, logs final position, and notifies owner.
     */
    public function workerAppClosedApi(Request $request)
    {
        $workerId = $request->input('worker_id') ?? $request->input('camel_worker_id');
        $sessionId = $request->input('session_id') ?? $request->input('training_session_id');

        if (!$workerId && !$sessionId) {
            return response()->json([
                'success' => false,
                'message' => 'معرف العامل أو معرف الجلسة مطلوب.'
            ], 422);
        }

        $sessionQuery = TrainingSession::whereIn('round_status', ['working', 'pending']);
        if ($sessionId) {
            $sessionQuery->where('id', $sessionId);
        } elseif ($workerId) {
            $sessionQuery->where('camel_worker_id', $workerId);
        }
        $session = $sessionQuery->latest()->first();

        if ($session) {
            $updateData = [
                'round_status' => 'stop',
            ];
            if ($request->filled('elapsed_time') && $request->input('elapsed_time') !== '00:00:00') {
                $updateData['round_time'] = $request->input('elapsed_time');
            }
            if ($request->filled('distance_km')) {
                $updateData['round_distance_km'] = (double)$request->input('distance_km');
            }
            $session->update($updateData);

            // Log final location coordinate if provided
            if ($request->filled('latitude') && $request->filled('longitude')) {
                \App\Models\SessionSpeedLog::create([
                    'training_session_id' => $session->id,
                    'speed' => 0.00,
                    'latitude' => $request->input('latitude'),
                    'longitude' => $request->input('longitude'),
                    'location_name' => 'نقطة انقطاع التدريب (إغلاق التطبيق)',
                ]);
            }

            // Broadcast to Reverb WebSocket so owner live screen immediately reflects pause
            try {
                $latestLog = \App\Models\SessionSpeedLog::where('training_session_id', $session->id)->orderBy('id', 'desc')->first();
                broadcast(new \App\Events\LocationUpdated($latestLog, null, $session));
                broadcast(new \App\Events\TrainingSessionEnded($session));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("WebSocket broadcast worker app closed failed: " . $e->getMessage());
            }

            // Send notification to owner
            $this->notifyOwnerWorkerClosedApp($session);
        }

        // Set worker status to offline
        if ($workerId) {
            $worker = CamelWorker::find($workerId);
            if ($worker) {
                $worker->online_status = 'offline';
                $worker->is_online = 0;
                $worker->last_activity_at = null;
                $worker->save();
                try {
                    broadcast(new \App\Events\WorkerStatusUpdated($worker));
                } catch (\Exception $e) {}
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ نقطة الانقطاع وإشعار المالك بنجاح.',
            'session' => $session
        ], 200);
    }

    /**
     * Send notification to owner when worker closes app completely during active training.
     */
    public function notifyOwnerWorkerClosedApp($session, $worker = null)
    {
        if (!$session) return;

        // Prevent duplicate notifications within 3 minutes for this session
        $cacheKey = "worker_closed_notif_sent_{$session->id}";
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return;
        }
        \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addMinutes(3));

        if (!$worker) {
            $session->load('worker.owner');
            $worker = $session->worker;
        } elseif (!$worker->relationLoaded('owner')) {
            $worker->load('owner');
        }
        $owner = $worker ? $worker->owner : null;
        if (!$owner) return;

        try {
            \Carbon\Carbon::setLocale('ar');
            $timeFormatted = now()->format('h:i A');
            $workerName = $worker ? $worker->full_name : 'العامل';

            $notifTitle = "تنبيه: إغلاق التطبيق أثناء التدريب ⚠️";
            $notifBody = "قام العامل {$workerName} بإغلاق التطبيق نهائياً أثناء جلسة التدريب في تمام الساعة ({$timeFormatted}). تم حفظ نقطة الانقطاع لاستئناف التدريب لاحقاً.";

            // Save in-app notification in database
            \App\Models\NotificationForApp::create([
                'user_id'   => $owner->id,
                'title'     => $notifTitle,
                'des'       => $notifBody,
                'date'      => now()->format('Y-m-d'),
                'user_view' => 'no',
                'created_at'=> now(),
            ]);

            // Send FCM Push Notification to owner's device
            $deviceToken = $owner->token ?: ($owner->firebase_token ?? null);
            \Illuminate\Support\Facades\Log::info("Worker app closed push: owner_id={$owner->id}, token=" . (!empty($deviceToken) ? substr($deviceToken, 0, 15) . "..." : "NONE"));
            if (!empty($deviceToken)) {
                $firebaseService = new \App\Services\FirebaseNotificationService();
                $unreadCount = \App\Models\NotificationForApp::where('user_id', $owner->id)
                    ->where('user_view', 'no')
                    ->count();

                $pushRes = $firebaseService->sendNotification(
                    $deviceToken,
                    $notifTitle,
                    $notifBody,
                    (string)$session->id,
                    'worker_app_closed',
                    $unreadCount
                );
                \Illuminate\Support\Facades\Log::info("Worker app closed FCM push result: " . json_encode($pushRes));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Owner notification for worker app closed failed: " . $e->getMessage());
        }
    }

    /**
     * API to delete a training session.
     */
    public function deleteSessionApi(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:training_sessions,id',
            'owner_id'   => 'nullable|exists:users,id',
        ], [
            'session_id.required' => 'معرف الجلسة التدريبية مطلوب.',
            'session_id.exists'   => 'الجلسة التدريبية غير موجودة.',
            'owner_id.exists'     => 'المالك غير موجود.',
        ]);

        $session = TrainingSession::findOrFail($request->session_id);

        if ($request->filled('owner_id')) {
            $owner = User::find($request->owner_id);
            if ($owner && $owner->role === 'owner') {
                $workerIds = CamelWorker::where('owner_id', $owner->id)->pluck('id');
                if (!$workerIds->contains($session->camel_worker_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'غير مصرح لك بحذف هذه الجلسة التدريبية.'
                    ], 403);
                }
            }
        }

        // Delete associated files if any
        if ($session->summary_audio && file_exists(public_path($session->summary_audio))) {
            @unlink(public_path($session->summary_audio));
        }
        if ($session->summary_image && file_exists(public_path($session->summary_image))) {
            @unlink(public_path($session->summary_image));
        }

        // Delete child relations
        \App\Models\SessionSpeedLog::where('training_session_id', $session->id)->delete();
        \App\Models\SessionInstruction::where('training_session_id', $session->id)->delete();
        \App\Models\SessionChat::where('training_session_id', $session->id)->delete();

        // Clear route cache if exists
        \Illuminate\Support\Facades\Cache::forget("session_route_{$session->id}");

        // Delete the session record
        $session->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الجلسة التدريبية بنجاح'
        ], 200);
    }
}
