<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CamelWorker extends Model
{
    protected $fillable = [
        'owner_id',
        'full_name',
        'login_code',
        'photo_path',
        'phone',
        'token',
        'status',
        'is_online',
        'online_status',
        'last_activity_at',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'last_activity_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Synchronize online statuses: auto-expire workers who have not sent a heartbeat recently.
     * 1) 45s..90s without ping while in 'online' -> transition to 'background'
     * 2) > 90s without ping -> transition to 'offline'
     */
    public static function syncOnlineStatuses($ownerId = null)
    {
        $throttleKey = 'sync_online_statuses_' . ($ownerId ?? 'all');
        try {
            // Prevent simultaneous dashboard-stats & camel-workers requests from running duplicate syncs & broadcasts
            if (!\Illuminate\Support\Facades\Cache::add($throttleKey, 1, 2)) {
                return;
            }
        } catch (\Throwable $e) {
            // Proceed cleanly if cache store is unavailable
        }

        $now = now();
        $cutoff45 = $now->copy()->subSeconds(45);
        $cutoff90 = $now->copy()->subSeconds(90);
        $workersToBroadcast = [];

        // 1. Move stale background or online (> 90s or null) directly to offline in one pass
        $staleOfflineQuery = static::whereIn('online_status', ['online', 'background'])
            ->where(function ($q) use ($cutoff90) {
                $q->whereNull('last_activity_at')
                  ->orWhere('last_activity_at', '<', $cutoff90);
            });
        if ($ownerId) {
            $staleOfflineQuery->where('owner_id', $ownerId);
        }
        foreach ($staleOfflineQuery->get() as $w) {
            $w->online_status = 'offline';
            $w->is_online = false;
            $w->save();
            $workersToBroadcast[] = $w;
        }

        // 2. Move stale online (between 45s and 90s) to background
        $staleBackgroundQuery = static::where('online_status', 'online')
            ->whereNotNull('last_activity_at')
            ->where('last_activity_at', '<', $cutoff45)
            ->where('last_activity_at', '>=', $cutoff90);
        if ($ownerId) {
            $staleBackgroundQuery->where('owner_id', $ownerId);
        }
        foreach ($staleBackgroundQuery->get() as $w) {
            $w->online_status = 'background';
            $w->is_online = true;
            $w->save();
            $workersToBroadcast[] = $w;
        }

        if (!empty($workersToBroadcast)) {
            $broadcastFn = function () use ($workersToBroadcast) {
                foreach ($workersToBroadcast as $worker) {
                    try {
                        broadcast(new \App\Events\WorkerStatusUpdated($worker));
                    } catch (\Throwable $e) {
                    }
                }
            };

            // Defer synchronous Reverb/Pusher broadcast until after HTTP response is sent
            try {
                dispatch($broadcastFn)->afterResponse();
            } catch (\Throwable $e) {
                $broadcastFn();
            }
        }
    }
}
