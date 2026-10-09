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
    public $sessionStats;

    /**
     * استقبال السجل الجديد المضاف للتو
     */
    public function __construct(SessionSpeedLog $log)
    {
        $this->log = $log;
        $session = $log->session;
        if ($session) {
            $this->sessionStats = [
                'current_speed' => number_format($session->speed, 2),
                'average_speed' => number_format($session->average_speed, 2),
                'distance' => number_format($session->round_distance_km, 2),
                'round_time' => $session->round_time,
                'round_status' => $session->round_status,
            ];
        }
    }

    /**
     * تحديد القناة التي سنبث عليها (قناة مخصصة لكل جلسة تدريب)
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('tracking-session.' . $this->log->training_session_id),
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