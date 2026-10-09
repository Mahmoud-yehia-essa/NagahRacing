<?php

namespace App\Events;

use App\Models\CamelWorker;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkerStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $worker;
    public $workerId;
    public $ownerId;
    public $isOnline;
    public $onlineStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(CamelWorker $worker)
    {
        $this->worker = $worker;
        $this->workerId = $worker->id;
        $this->ownerId = $worker->owner_id;
        $this->isOnline = (bool) $worker->is_online;
        $this->onlineStatus = $worker->online_status ?? ($worker->is_online ? 'online' : 'offline');
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('owner.' . $this->ownerId),
        ];
    }

    /**
     * Get the broadcast event name.
     */
    public function broadcastAs(): string
    {
        return 'worker.status.updated';
    }
}
