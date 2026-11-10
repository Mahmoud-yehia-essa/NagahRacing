<?php

namespace App\Events;

use App\Models\SessionSpeedLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // لبث الموقع لحظياً بدون تأخير
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $log;
    public $recentLogs;
    public $sessionStats;
    public $heading;

    public $sessionId;

    /**
     * استقبال السجل الجديد المضاف للتو أو جلسة التدريب المحدثة
     */
    public function __construct($log = null, $heading = null, $session = null, $recentLogs = null)
    {
        if ($log && $heading !== null) {
            $log->setAttribute('heading', (float)$heading);
        }
        $this->log = $log;
        $this->recentLogs = $recentLogs;
        $this->heading = $heading !== null ? (float)$heading : ($log && isset($log->heading) ? (float)$log->heading : null);
        
        $resolvedSession = $session ?? ($log ? $log->session : null);
        if ($resolvedSession) {
            $this->sessionId = $resolvedSession->id;
            $exactDist = \Illuminate\Support\Facades\Cache::get('session_exact_dist_' . $resolvedSession->id);
            $distVal = $exactDist !== null ? (float)$exactDist : (float)($resolvedSession->round_distance_km ?? 0);
            $this->sessionStats = [
                'current_speed' => number_format((float)($resolvedSession->speed ?? 0), 2, '.', ''),
                'average_speed' => number_format((float)($resolvedSession->average_speed ?? 0), 2, '.', ''),
                'distance' => number_format($distVal, 2, '.', ''),
                'exact_distance_km' => round($distVal, 4),
                'round_time' => $resolvedSession->round_time ?? '00:00:00',
                'round_status' => $resolvedSession->round_status,
            ];
        } elseif ($log) {
            $this->sessionId = $log->training_session_id;
        }
    }

    /**
     * تحديد القناة التي سنبث عليها (قناة مخصصة لكل جلسة تدريب)
     */
    public function broadcastOn(): array
    {
        $targetId = $this->sessionId ?? ($this->log ? $this->log->training_session_id : 0);
        return [
            new Channel('tracking-session.' . $targetId),
        ];
    }

    /**
     * اسم الحدث الذي سنستمع إليه في الجافاسكربت
     */
    public function broadcastAs(): string
    {
        return 'location.updated';
    }
}
