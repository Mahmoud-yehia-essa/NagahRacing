<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SessionSpeedLog;
use App\Events\LocationUpdated;
use App\Models\TrainingSession;
use App\Models\CamelWorker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TrackingController extends Controller
{
    public function updateLocation(Request $request)
    {
        // 1. التحقق من البيانات القادمة من جهاز التتبع
        $validated = $request->validate([
            'training_session_id' => 'required|integer',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'speed' => 'nullable|numeric',
            'location_name' => 'nullable|string',
            'heading' => 'nullable|numeric',
        ]);

        $heading = isset($validated['heading']) ? (float)$validated['heading'] : null;

        // 2. إنشاء السجل وحفظه في جدول session_speed_logs
        $log = SessionSpeedLog::create([
            'training_session_id' => $validated['training_session_id'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'speed' => $validated['speed'] ?? 0,
            'location_name' => $validated['location_name'] ?? null,
        ]);
        if ($heading !== null) {
            $log->heading = $heading;
        }

        // 3. تحديث بيانات الجلسة التدريبية (السرعة، متوسط السرعة، المسافة، الزمن)
        $session = TrainingSession::find($validated['training_session_id']);
        $newAvgSpeed = 0;
        $newDistance = 0;
        if ($session) {
            $cacheKey = 'session_last_coord_' . $session->id;
            $lastCoordinate = Cache::get($cacheKey);

            // عداد تراكمي مخزن في قاعدة البيانات
            $currentCount = ($session->logs_count ?? 0) + 1;
            $distanceIncrement = 0.0;
            
            if ($lastCoordinate) {
                $dist = $this->getDistance($lastCoordinate['latitude'], $lastCoordinate['longitude'], $log->latitude, $log->longitude);
                // تصفية تشويش الـ GPS: الحركة أقل من 1.5 متر تعتبر ثبات، والقفزات فوق 1.5 كم في ثانية واحدة تعتبر خطأ إشارة
                if ($dist >= 0.0015 && $dist <= 1.5) {
                    $distanceIncrement = $dist;
                }
            } else {
                // جلب من قاعدة البيانات فقط في أول إرسال أو عند انقطاع الكاش
                $lastLog = SessionSpeedLog::where('training_session_id', $session->id)
                    ->where('id', '<', $log->id)
                    ->orderBy('id', 'desc')
                    ->first();
                    
                if ($lastLog) {
                    $dist = $this->getDistance($lastLog->latitude, $lastLog->longitude, $log->latitude, $log->longitude);
                    if ($dist >= 0.0015 && $dist <= 1.5) {
                        $distanceIncrement = $dist;
                    }
                }
            }

            // تحديث الكاش بالإحداثيات الجغرافية الحالية للجلسة (صالحة لمدة 10 دقائق)
            Cache::put($cacheKey, [
                'latitude' => $log->latitude,
                'longitude' => $log->longitude
            ], 600);

            // حساب متوسط السرعة بطريقة تراكمية سريعة
            $newAvgSpeed = (($session->average_speed * ($currentCount - 1)) + ($log->speed ?? 0)) / $currentCount;
            $newDistance = $session->round_distance_km + $distanceIncrement;
            
            $updateData = [
                'latitude' => $log->latitude,
                'longitude' => $log->longitude,
                'speed' => $log->speed ?? 0,
                'average_speed' => $newAvgSpeed,
                'round_distance_km' => $newDistance,
                'logs_count' => $currentCount,
                'round_status' => ($session->round_status === 'end') ? 'end' : 'working',
            ];
            
            if ($request->filled('duration')) {
                $updateData['round_time'] = $request->input('duration');
            } elseif ($request->filled('round_time')) {
                $updateData['round_time'] = $request->input('round_time');
            }
            
            $session->update($updateData);
            $session->refresh();
            if ($session->camel_worker_id) {
                $workerThrottleKey = 'worker_ping_throttle_' . $session->camel_worker_id;
                if (!Cache::has($workerThrottleKey)) {
                    CamelWorker::where('id', $session->camel_worker_id)->update(['is_online' => 1, 'last_activity_at' => now()]);
                    Cache::put($workerThrottleKey, true, 20);
                }
            }
        }

        // 4. بث السجل الجديد فوراً عبر الويب سوكيت لحظياً وبدون تأخير
        try {
            broadcast(new LocationUpdated($log, $heading, $session));
        } catch (\Exception $e) {
            Log::warning('WebSocket broadcast failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true, 
            'message' => 'تم حفظ الموقع وبثه بنجاح',
            'data' => $log,
            'current_speed' => number_format($log->speed ?? 0, 2),
            'average_speed' => number_format($newAvgSpeed, 2),
            'distance' => number_format($newDistance, 2),
            'round_status' => $session ? $session->round_status : 'working',
        ]);
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
