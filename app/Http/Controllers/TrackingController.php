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
        $now = now();

        // 2. تجهيز سلسلة النقاط (النقاط البينية المجمعة أثناء الإرسال + النقطة الحالية) لضمان عدم ضياع أي منحنى
        $pointsToProcess = [];
        if (!empty($validated['path_points']) && is_array($validated['path_points'])) {
            foreach ($validated['path_points'] as $pt) {
                if (isset($pt['latitude'], $pt['longitude']) && is_numeric($pt['latitude']) && is_numeric($pt['longitude'])) {
                    $latVal = (float)$pt['latitude'];
                    $lngVal = (float)$pt['longitude'];
                    if ($latVal == 0.0 && $lngVal == 0.0) {
                        continue;
                    }
                    $rawClientTs = $this->parseRawClientUnix($pt['recorded_at'] ?? null);
                    $pointsToProcess[] = [
                        'latitude' => $latVal,
                        'longitude' => $lngVal,
                        'speed' => isset($pt['speed']) && is_numeric($pt['speed']) ? max(0.0, (float)$pt['speed']) : $currentSpeed,
                        'heading' => isset($pt['heading']) && is_numeric($pt['heading']) ? (float)$pt['heading'] : $heading,
                        'location_name' => $validated['location_name'] ?? null,
                        'client_ts' => $rawClientTs,
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
                'client_ts' => null,
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
                    'client_ts' => null,
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

        // Fetch recent logs once (fast indexed scan) for BOTH last-coordinate fallback,
        // monotonic server-clock alignment, AND retry-batch deduplication (prevents spiderweb chords on 4G timeouts).
        $recentDbLogs = \Illuminate\Support\Facades\DB::table('session_speed_logs')
            ->where('training_session_id', $sessionId)
            ->select(['id', 'latitude', 'longitude', 'created_at'])
            ->orderBy('id', 'desc')
            ->limit(60)
            ->get();

        $latestDbRow = $recentDbLogs->first();
        $minAllowedUnix = 0;
        if ($latestDbRow && !empty($latestDbRow->created_at)) {
            $minAllowedUnix = \Carbon\Carbon::parse($latestDbRow->created_at)->timestamp;
        }

        if (!$lastCoordinate && $session) {
            if ($latestDbRow) {
                $lastCoordinate = [
                    'latitude' => (float)$latestDbRow->latitude,
                    'longitude' => (float)$latestDbRow->longitude,
                ];
            } elseif ($session->latitude !== null && $session->longitude !== null) {
                $lastCoordinate = [
                    'latitude' => (float)$session->latitude,
                    'longitude' => (float)$session->longitude,
                ];
            }
        }

        // Align all batch timestamps strictly to SERVER clock ($now) preserving relative intra-batch
        // spacing, so phone-vs-server clock skew can NEVER interleave path_points with final points!
        $batchCount = count($pointsToProcess);
        $anchorClientTs = null;
        for ($i = $batchCount - 1; $i >= 0; $i--) {
            if ($pointsToProcess[$i]['client_ts'] !== null) {
                $anchorClientTs = $pointsToProcess[$i]['client_ts'];
                break;
            }
        }
        $nowUnix = $now->timestamp;
        $runningUnix = 0;
        for ($i = 0; $i < $batchCount; $i++) {
            $ptTs = $nowUnix;
            if ($anchorClientTs !== null && $pointsToProcess[$i]['client_ts'] !== null) {
                $offsetSec = $anchorClientTs - $pointsToProcess[$i]['client_ts'];
                if ($offsetSec < 0) {
                    $offsetSec = 0;
                } elseif ($offsetSec > 45) {
                    $offsetSec = 45;
                }
                $ptTs = $nowUnix - (int)round($offsetSec);
            }
            if ($minAllowedUnix > 0 && $ptTs < $minAllowedUnix) {
                $ptTs = $minAllowedUnix;
            }
            if ($runningUnix > 0 && $ptTs < $runningUnix) {
                $ptTs = $runningUnix;
            }
            $runningUnix = $ptTs;
            $pointsToProcess[$i]['recorded_at'] = \Carbon\Carbon::createFromTimestamp($ptTs, config('app.timezone'));
        }

        $createdLogs = [];
        $distanceIncrement = 0.0;

        // Deduplicate consecutive near-identical points within the same batch
        // AND drop re-queued retry points already persisted in the last 25 seconds (eliminates duplicate loop chords).
        $recentCoordsForDedup = [];
        foreach ($recentDbLogs as $rLog) {
            $rTs = !empty($rLog->created_at) ? \Carbon\Carbon::parse($rLog->created_at)->timestamp : 0;
            if ($rTs > 0 && ($nowUnix - $rTs) <= 25) {
                $recentCoordsForDedup[] = [(float)$rLog->latitude, (float)$rLog->longitude];
            }
        }

        $isMultiPointBatch = count($pointsToProcess) > 1;
        $dedupedPoints = [];
        foreach ($pointsToProcess as $idx => $pt) {
            $isLastInBatch = ($idx === count($pointsToProcess) - 1);
            if (!empty($dedupedPoints)) {
                $prevPt = end($dedupedPoints);
                $intraDist = $this->getDistance(
                    $prevPt['latitude'],
                    $prevPt['longitude'],
                    $pt['latitude'],
                    $pt['longitude']
                );
                if ($intraDist < 0.0005 && $pt['speed'] < 0.5) {
                    $dedupedPoints[count($dedupedPoints) - 1]['speed'] = $pt['speed'];
                    $dedupedPoints[count($dedupedPoints) - 1]['heading'] = $pt['heading'];
                    continue;
                }
            }
            // Skip intermediate retry points that already exist in DB within the last 25 seconds & ~1.9m
            if ($isMultiPointBatch && !$isLastInBatch && !empty($recentCoordsForDedup)) {
                $alreadySavedRecently = false;
                foreach ($recentCoordsForDedup as [$rLat, $rLng]) {
                    if (abs($pt['latitude'] - $rLat) < 0.000018 && abs($pt['longitude'] - $rLng) < 0.000018) {
                        $alreadySavedRecently = true;
                        break;
                    }
                }
                if ($alreadySavedRecently) {
                    continue;
                }
            }
            $dedupedPoints[] = $pt;
        }
        $pointsToProcess = $dedupedPoints;

        // Avoid bloating session_speed_logs with identical stationary heartbeat rows every 2.5s
        $reusedStationaryLog = null;
        if (count($pointsToProcess) === 1 && $lastCoordinate && $currentSpeed < 0.5) {
            $singleDist = $this->getDistance(
                $lastCoordinate['latitude'],
                $lastCoordinate['longitude'],
                $pointsToProcess[0]['latitude'],
                $pointsToProcess[0]['longitude']
            );
            if ($singleDist < 0.0012) {
                $existingLatestLog = SessionSpeedLog::where('training_session_id', $sessionId)
                    ->orderBy('id', 'desc')
                    ->first();
                if ($existingLatestLog) {
                    $reusedStationaryLog = $existingLatestLog;
                    if ($pointsToProcess[0]['heading'] !== null) {
                        $reusedStationaryLog->setAttribute('heading', (float)$pointsToProcess[0]['heading']);
                    }
                }
            }
        }

        if ($reusedStationaryLog === null) {
            foreach ($pointsToProcess as $pt) {
                $logItem = new SessionSpeedLog([
                    'training_session_id' => $sessionId,
                    'latitude' => $pt['latitude'],
                    'longitude' => $pt['longitude'],
                    'speed' => $pt['speed'],
                    'location_name' => $pt['location_name'],
                ]);
                $logItem->created_at = $pt['recorded_at'];
                $logItem->updated_at = $now;
                $logItem->save();

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
                    // الحركة من 2.5 متر إلى 1.5 كم تُحتسب وتُحدث نقطة الارتكاز لتفادي تضخم المسافة عند السكون
                    if ($dist >= 0.0025 && $dist <= 1.5) {
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
            if (!empty($createdLogs)) {
                Cache::forget('session_compact_route_v3_' . $sessionId);
            }
        }

        $log = $reusedStationaryLog ?? end($createdLogs);
        $newAvgSpeed = 0.0;
        $exactDistance = 0.0;

        if ($session) {
            if ($lastCoordinate) {
                Cache::put($cacheCoordKey, $lastCoordinate, 86400);
            }

            $currentCount = ($session->logs_count ?? 0) + count($createdLogs);

            $cachedExactDist = Cache::get($cacheDistKey);
            $existingExactDist = max(
                $cachedExactDist !== null ? (float)$cachedExactDist : 0.0,
                (float)($session->round_distance_km ?? 0.0)
            );

            // اعتماد المسافة الدقيقة الصادرة من جهاز العامل مع ضمان عدم تراجعها أبداً عند تأخر الطلبات
            if (isset($validated['distance_km']) && is_numeric($validated['distance_km'])) {
                $clientDist = max(0.0, (float)$validated['distance_km']);
                $exactDistance = max($existingExactDist, $clientDist);
            } else {
                $exactDistance = $existingExactDist + $distanceIncrement;
            }

            Cache::put($cacheDistKey, $exactDistance, 86400);

            // تحديد الزمن الفعلي للجلسة من جهاز العامل مباشرة مع ضمان عدم تراجع الوقت
            $existingTimeStr = $session->round_time ?? '00:00:00';
            $roundTimeStr = $existingTimeStr;
            $incomingTimeStr = null;
            if ($request->filled('duration') && !empty($request->input('duration'))) {
                $incomingTimeStr = (string)$request->input('duration');
            } elseif ($request->filled('round_time') && !empty($request->input('round_time'))) {
                $incomingTimeStr = (string)$request->input('round_time');
            }
            if ($incomingTimeStr !== null &&
                $this->parseDurationToSeconds($incomingTimeStr) >= $this->parseDurationToSeconds($existingTimeStr)) {
                $roundTimeStr = $incomingTimeStr;
            }

            // حماية ضد تذبذب الـ GPS عند السكون التام (مسافة أقل من 3 أمتار وسرعة ضوضائية منخفضة)
            if ($exactDistance < 0.003 && $currentSpeed < 2.5) {
                $currentSpeed = 0.0;
            }

            // اعتماد متوسط السرعة الدقيق المحسوب وفقاً لوقت الحركة الفعلي (وصفر عند عدم قطع مسافة حقيقية)
            if ($exactDistance < 0.003) {
                $newAvgSpeed = 0.0;
            } elseif (isset($validated['average_speed']) && is_numeric($validated['average_speed'])) {
                $newAvgSpeed = min(85.0, max(0.0, (float)$validated['average_speed']));
            } else {
                $elapsedSeconds = $this->parseDurationToSeconds($roundTimeStr);
                if ($elapsedSeconds >= 5 && $exactDistance >= 0.003) {
                    $hours = $elapsedSeconds / 3600.0;
                    $newAvgSpeed = min(85.0, max(0.0, $exactDistance / $hours));
                } else {
                    $newAvgSpeed = (float)($session->average_speed ?? 0.0);
                }
            }

            $preservedStatus = in_array($session->round_status, ['end', 'stop'], true)
                ? $session->round_status
                : 'working';
            $resolvedSpeed = $preservedStatus === 'working' ? round($currentSpeed, 2) : 0.00;

            $updateData = [
                'latitude' => $log->latitude,
                'longitude' => $log->longitude,
                'speed' => $resolvedSpeed,
                'average_speed' => round($newAvgSpeed, 2),
                'round_distance_km' => round($exactDistance, 2),
                'logs_count' => $currentCount,
                'round_status' => $preservedStatus,
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
            $recentLogsPayload = count($createdLogs) > 1 ? array_slice($createdLogs, -40) : null;
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

    private function parseRawClientUnix(?string $raw): ?float
    {
        if (empty($raw)) {
            return null;
        }
        try {
            $parsed = \Carbon\Carbon::parse($raw);
            return (float)$parsed->getTimestamp() + ($parsed->micro / 1000000.0);
        } catch (\Throwable $e) {
            return null;
        }
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
