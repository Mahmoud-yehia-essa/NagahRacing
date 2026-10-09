<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallSignalEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $channelName;
    public $callerName;
    public $receiverId;
    public $callerId;
    public $type; // 'incoming' (اتصال جديد) أو 'hangup' (إلغاء)
    public $token;
    public $callerPhoto;

    public function __construct($channelName, $callerName, $receiverId, $type = 'incoming', $callerId = null, $token = null, $callerPhoto = null)
    {
        $this->channelName = $channelName;
        $this->callerName = $callerName;
        $this->receiverId = $receiverId;
        $this->type = $type;
        $this->callerId = $callerId;
        $this->token = $token;
        $this->callerPhoto = $callerPhoto;
    }

    public function broadcastOn(): array
    {
        // البث على قناة عامة أو خاصة بالمستقبل لكي يسمع الرنين
        return [
            new Channel('user-call.' . $this->receiverId)
        ];
    }

    public function broadcastAs(): string
    {
        return 'call.signal';
    }
}