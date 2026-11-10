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
            'average_speed' => 'nullable|numeric',
            'distance_km' => 'nullable|numeric',
            'location_name' => 'nullable|string',
            'heading' => 'nullable|numeric',
            'path_points' => 'nullable|array',
        ]);

        $sessionId = (int)$validated['training_session_id'];
        $heading = isset($validated['heading']) ? (float)$validated['heading'] : null;
        $currentSpeed = max(0.0, (float)($validated['speed'] ?? 0));

        // 2. تجهيز سلسلة النقاط (النقاط البينية المجمعة أثناء الإرسال + النقطة الحالية) لضمان عدم ضياع أي منحنى
        $pointsToProcess = [];
        if (!empty($validated['path_points']) && is_array($validated['path_points'])) {
            foreach ($validated['path_points'] as $pt) {
                if (isset($pt['latitude'], $pt['longitude']) && is_numeric($pt['latitude']) && is_numeric($pt['longitude'])) {
                    $pointsToProcess[] = [
                        'latitude' => (float)$pt['latitude'],
                        'longitude' => (float)$pt['longitude'],
                        'speed' => isset($pt['speed']) && is_numeric($pt['speed']) ? max(0.0, (float)$pt['speed']) : $currentSpeed,
                        'heading' => isset($pt['heading']) && is_numeric($pt['heading']) ? (float)$pt['heading'] : $heading,
                        'location_name' => $validated['location_name'] ?? null,
                    ];
                }
            }
        }

        $finalLat = (float)$validated['latitude'];
        $finalLng = (float)$validated['longitude'];

        // تأكد من أن النقطة النهائية مضافة في نهاية السلسلة دون تكرار متطابق
        if (empty($pointsToProcess)) {
            $pointsToProcess[] = [
                'latitude' => $finalLat,
                'longitude' => $finalLng,
                'speed' => $currentSpeed,
                'heading' => $heading,
                'location_name' => $validated['location_name'] ?? null,
            ];
        } else {
            $lastPt = end($pointsToProcess);
            if (abs($lastPt['latitude'] - $finalLat) > 0.000001 || abs($lastPt['longitude'] - $finalLng) > 0.000001) {
                $pointsToProcess[] = [
                    'latitude' => $finalLat,
                    'longitude' => $finalLng,
                    'speed' => $currentSpeed,
                    'heading' => $heading,
                    'location_name' => $validated['location_name'] ?? null,
                ];
            } else {
                $pointsToProcess[count($pointsToProcess) - 1]['speed'] = $currentSpeed;
                $pointsToProcess[count($pointsToProcess) - 1]['heading'] = $heading;
            }
        }

        // 3. تحديث بيانات الجلسة التدريبية وحساب المسافة والسرعة بدقة عالية
        $session = TrainingSession::find($sessionId);
        $cacheCoordKey = 'session_last_coord_' . $sessionId;
        $cacheDistKey = 'session_exact_dist_' . $sessionId;
        $lastCoordinate = Cache::get($cacheCoordKey);

        if (!$lastCoordinate && $session) {
            $lastLog = SessionSpeedLog::where('training_session_id', $sessionId)
                ->orderBy('id', 'desc')
                ->first();
            if ($lastLog) {
                $lastCoordinate = [
                    'latitude' => (float)$lastLog->latitude,
                    'longitude' => (float)$lastLog->longitude,
                ];
            } elseif ($session->latitude !== null && $session->longitude !== null) {
                $lastCoordinate = [
                    'latitude' => (float)$session->latitude,
                    'longitude' => (float)$session->longitude,
                ];
            }
        }

        $createdLogs = [];
        $distanceIncrement = 0.0;

        foreach ($pointsToProcess as $pt) {
            $logItem = SessionSpeedLog::create([
                'training_session_id' => $sessionId,
                'latitude' => $pt['latitude'],
                'longitude' => $pt['longitude'],
                'speed' => $pt['speed'],
                'location_name' => $pt['location_name'],
            ]);
            if ($pt['heading'] !== null) {
                $logItem->setAttribute('heading', (float)$pt['heading']);
            }
            $createdLogs[] = $logItem;

            if ($lastCoordinate) {
                $dist = $this->getDistance(
                    $lastCoordinate['latitude'],
                    $lastCoordinate['longitude'],
                    $pt['latitude'],
                    $pt['longitude']
                );
                // الحركة من 1 متر إلى 1.5 كم تُحتسب وتُحدث نقطة الارتكاز؛ أقل من 1 متر لا تُحدث نقطة الارتكاز حتى لا تضيع الحركة البطيئة
                if ($dist >= 0.0010 && $dist <= 1.5) {
                    $distanceIncrement += $dist;
                    $lastCoordinate = [
                        'latitude' => $pt['latitude'],
                        'longitude' => $pt['longitude'],
                    ];
                } elseif ($dist > 1.5) {
                    // قفزة GPS شاذة: نحدث نقطة الارتكاز دون إضافة مسافة وهمية
                    $lastCoordinate = [
                        'latitude' => $pt['latitude'],
                        'longitude' => $pt['longitude'],
                    ];
                }
            } else {
                $lastCoordinate = [
                    'latitude' => $pt['latitude'],
                    'longitude' => $pt['longitude'],
                ];
            }
        }

        $log = end($createdLogs);
        $newAvgSpeed = 0.0;
        $exactDistance = 0.0;

        if ($session) {
            if ($lastCoordinate) {
                Cache::put($cacheCoordKey, $lastCoordinate, 86400);
            }

            $currentCount = ($session->logs_count ?? 0) + count($createdLogs);

            // استرجاع المسافة الدقيقة غير المقربة من الكاش لمنع تصفير الزيادات الصغيرة عند التقريب لرقمين عشريين
            $cachedExactDist = Cache::get($cacheDistKey);
            $baseDistance = $cachedExactDist !== null
                ? (float)$cachedExactDist
                : (float)($session->round_distance_km ?? 0.0);

            $exactDistance = $baseDistance + $distanceIncrement;

            // مزامنة المسافة مع المسافة المحسوبة محلياً لدى العامل (في حال تأخر أو ضياع أي حزمة سابقة)
            if (isset($validated['distance_km']) && is_numeric($validated['distance_km'])) {
                $clientDist = max(0.0, (float)$validated['distance_km']);
                if ($clientDist > $exactDistance && ($clientDist - $exactDistance) <= 10.0) {
                    $exactDistance = $clientDist;
                }
            }

            Cache::put($cacheDistKey, $exactDistance, 86400);

            // تحديد الزمن الفعلي للجلسة
            $roundTimeStr = $session->round_time ?? '00:00:00';
            if ($request->filled('duration')) {
                $roundTimeStr = $request->input('duration');
            } elseif ($request->filled('round_time')) {
                $roundTimeStr = $request->input('round_time');
            }

            $elapsedSeconds = $this->parseDurationToSeconds($roundTimeStr);
            if ($elapsedSeconds <= 0 && $session->created_at) {
                $elapsedSeconds = max(1, (int)round(abs(now()->diffInSeconds($session->created_at, false))));
            }

            // حساب متوسط السرعة بدقة احترافية
            if (isset($validated['average_speed']) && is_numeric($validated['average_speed']) && (float)$validated['average_speed'] > 0) {
                $newAvgSpeed = min(85.0, max(0.0, (float)$validated['average_speed']));
            } elseif ($elapsedSeconds >= 5 && $exactDistance >= 0.002) {
                $hours = $elapsedSeconds / 3600.0;
                $newAvgSpeed = min(85.0, max(0.0, $exactDistance / $hours));
            } else {
                $prevAvg = (float)($session->average_speed ?? 0.0);
                if ($currentSpeed > 0.3) {
                    $newAvgSpeed = $prevAvg > 0
                        ? (($prevAvg * 0.75) + ($currentSpeed * 0.25))
                        : $currentSpeed;
                } else {
                    $newAvgSpeed = $prevAvg;
                }
            }

            $updateData = [
                'latitude' => $log->latitude,
                'longitude' => $log->longitude,
                'speed' => round($currentSpeed, 2),
                'average_speed' => round($newAvgSpeed, 2),
                'round_distance_km' => round($exactDistance, 2),
                'logs_count' => $currentCount,
                'round_status' => ($session->round_status === 'end') ? 'end' : 'working',
                'round_time' => $roundTimeStr,
            ];

            $session->update($updateData);
            $session->refresh();

            if ($session->camel_worker_id) {
                $workerThrottleKey = 'worker_ping_throttle_' . $session->camel_worker_id;
                if (!Cache::has($workerThrottleKey)) {
                    CamelWorker::where('id', $session->camel_worker_id)->update([
                        'is_online' => 1,
                        'online_status' => 'online',
                        'last_activity_at' => now(),
                    ]);
                    Cache::put($workerThrottleKey, true, 15);
                }
            }
        }

        // 4. بث السجل الجديد (مع أي نقاط منحنى بينية) فوراً عبر الويب سوكيت لحظياً وبدون تأخير
        try {
            $recentLogsPayload = count($createdLogs) > 1 ? $createdLogs : null;
            broadcast(new LocationUpdated($log, $heading, $session, $recentLogsPayload));
        } catch (\Exception $e) {
            Log::warning('WebSocket broadcast failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ الموقع وبثه بنجاح',
            'data' => $log,
            'current_speed' => number_format($currentSpeed, 2, '.', ''),
            'average_speed' => number_format($newAvgSpeed, 2, '.', ''),
            'distance' => number_format($exactDistance, 2, '.', ''),
            'exact_distance_km' => round($exactDistance, 4),
            'round_status' => $session ? $session->round_status : 'working',
        ]);
    }

    private function parseDurationToSeconds(?string $duration): int
    {
        if (empty($duration)) {
            return 0;
        }
        $parts = explode(':', trim($duration));
        if (count($parts) === 3) {
            return ((int)$parts[0] * 3600) + ((int)$parts[1] * 60) + (int)$parts[2];
        }
        if (count($parts) === 2) {
            return ((int)$parts[0] * 60) + (int)$parts[1];
        }
        return 0;
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
