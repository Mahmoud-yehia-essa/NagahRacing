<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SessionSpeedLog;
use App\Events\LocationUpdated;

class TrackingController extends Controller
{
    public function updateLocation(Request $request)
    {
        // 1. التحقق من البيانات القادمة من جهاز التتبع
        $validated = $request->validate([
            'training_session_id' => 'required|integer', // يمكن إضافة exists:training_sessions,id إذا أردت
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'speed' => 'nullable|numeric',
            'location_name' => 'nullable|string',
        ]);

        // 2. إنشاء السجل وحفظه في جدول session_speed_logs
        $log = SessionSpeedLog::create([
            'training_session_id' => $validated['training_session_id'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'speed' => $validated['speed'] ?? 0,
            'location_name' => $validated['location_name'] ?? null,
        ]);

        // 3. تحديث بيانات الجلسة التدريبية (السرعة، متوسط السرعة، المسافة، الزمن)
        $session = \App\Models\TrainingSession::find($validated['training_session_id']);
        $newAvgSpeed = 0;
        $newDistance = 0;
        if ($session) {
            // التحسين الأول: عداد تراكمي مخزن في قاعدة البيانات بدلاً من استعلام COUNT
            $currentCount = ($session->logs_count ?? 0) + 1;
            
            // التحسين الثاني: استخدام الكاش لجلب إحداثيات السجل السابق لتجنب استعلام قاعدة البيانات المكلف
            $distanceIncrement = 0.0;
            
            if ($lastCoordinate) {
                $dist = $this->getDistance($lastCoordinate['latitude'], $lastCoordinate['longitude'], $log->latitude, $log->longitude);
                // تصفية تشويش الـ GPS: الحركة أقل من 2 متر تعتبر ثبات، والقفزات فوق 1.5 كم في بضع ثوانٍ تعتبر خطأ إشارة
                if ($dist >= 0.002 && $dist <= 1.5) {
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
                    if ($dist >= 0.002 && $dist <= 1.5) {
                        $distanceIncrement = $dist;
                    }
                }
            }

            // تحديث الكاش بالإحداثيات الجغرافية الحالية للجلسة (صالحة لمدة 5 دقائق)
            \Illuminate\Support\Facades\Cache::put($cacheKey, [
                'latitude' => $log->latitude,
                'longitude' => $log->longitude
            ], 300);

            // حساب متوسط السرعة بطريقة تراكمية أسرع بكثير
            $newAvgSpeed = (($session->average_speed * ($currentCount - 1)) + ($log->speed ?? 0)) / $currentCount;
            $newDistance = $session->round_distance_km + $distanceIncrement;
            
            $updateData = [
                'speed' => $log->speed ?? 0,
                'average_speed' => $newAvgSpeed,
                'round_distance_km' => $newDistance,
                'logs_count' => $currentCount,
                'round_status' => 'working',
            ];
            
            if ($request->filled('duration')) {
                $updateData['round_time'] = $request->input('duration');
            }
            
            $session->update($updateData);
            if ($session->camel_worker_id) {
                \App\Models\CamelWorker::where('id', $session->camel_worker_id)->update(['is_online' => 1, 'last_activity_at' => now()]);
            }
        }

        // 4. السحر هنا: بث السجل الجديد فوراً عبر الويب سوكيت!
        try {
            broadcast(new LocationUpdated($log))->toOthers();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("WebSocket broadcast failed: " . $e->getMessage());
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