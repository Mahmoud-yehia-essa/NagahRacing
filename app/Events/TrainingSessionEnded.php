<?php

namespace App\Events;

use App\Models\TrainingSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TrainingSessionEnded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $session;
    public $ownerId;

    /**
     * Create a new event instance.
     */
    public function __construct(TrainingSession $session)
    {
        $this->session = $session;
        // Load worker details if not loaded to extract ownerId safely
        if (!$session->relationLoaded('worker')) {
            $session->load('worker');
        }
        $this->ownerId = $session->worker ? $session->worker->owner_id : null;
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
        return 'training.session.ended';
    }
}
