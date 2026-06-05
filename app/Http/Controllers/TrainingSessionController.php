<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CamelWorker;
use App\Models\TrainingSession;
use Illuminate\Http\Request;

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
        $logsCount = \App\Models\SessionSpeedLog::where('training_session_id', $id)->count();
        
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
        $lastLog = \App\Models\SessionSpeedLog::where('training_session_id', $id)->orderBy('id', 'desc')->first();
        if ($lastLog) {
            $distanceIncrement = $this->getDistance($lastLog->latitude, $lastLog->longitude, $lat, $lng);
            // If the increment is unusually large (due to looping back or a jump), cap it or use default
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
        
        // Clear cached route
        \Illuminate\Support\Facades\Cache::forget("session_route_{$id}");
        
        // Reset metrics
        $session->update([
            'speed' => 0.00,
            'average_speed' => 0.00,
            'round_distance_km' => 0.00,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'تم حذف جميع السجلات وإعادة تهيئة إحصائيات الجلسة بنجاح.',
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
     * API to fetch training sessions belonging to a specific worker, with status filtering.
     */
    public function getSessionsByWorkerApi(Request $request)
    {
        $request->validate([
            'worker_id' => 'required|exists:camel_workers,id',
            'status'    => 'nullable|string|in:active,working,paused,stop,ended,end,all',
        ], [
            'worker_id.required' => 'معرف العامل مطلوب.',
            'worker_id.exists'   => 'العامل غير موجود.',
            'status.in'          => 'حالة التصفية غير صالحة.',
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

        $session = TrainingSession::with([
            'worker.owner',
            'speedLogs' => function ($q) {
                $q->orderBy('created_at', 'asc');
            },
            'chats' => function ($q) {
                $q->orderBy('created_at', 'asc');
            },
            'instructions' => function ($q) {
                $q->orderBy('created_at', 'asc');
            }
        ])->find($id);

        \Carbon\Carbon::setLocale('ar');
        $now = \Carbon\Carbon::now();

        $session->start_date_time = $session->created_at ? $session->created_at->format('Y-m-d H:i:s') : null;
        $session->end_date_time = $session->session_ended_at ? $session->session_ended_at->format('Y-m-d H:i:s') : null;
        $session->started_ago = $session->created_at ? $session->created_at->diffForHumans($now) : null;
        if ($session->session_ended_at) {
            $session->ended_ago = $session->session_ended_at->diffForHumans($now);
        } else {
            $session->ended_ago = ($session->round_status === 'end') ? 'منتهية' : 'نشطة حالياً';
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

        // Load worker details
        $session->load('worker');

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

        $session->load('worker');

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
}
