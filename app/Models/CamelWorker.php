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
     * Never fabricate 'background' from missing heartbeats; 'background' is only set explicitly by client lifecycle.
     * 1) 'online' with no activity for > 25s (or null) -> directly 'offline'
     * 2) 'background' with no activity for > 45s (or null) -> directly 'offline'
     */
    public static function syncOnlineStatuses($ownerId = null)
    {
        $now = now();
        $onlineCutoff = $now->copy()->subSeconds(25);
        $backgroundCutoff = $now->copy()->subSeconds(45);
        $workersToBroadcast = [];

        $staleQuery = static::where(function ($q) use ($onlineCutoff, $backgroundCutoff) {
            $q->where(function ($qOnline) use ($onlineCutoff) {
                $qOnline->where('online_status', 'online')
                    ->where(function ($t) use ($onlineCutoff) {
                        $t->whereNull('last_activity_at')
                          ->orWhere('last_activity_at', '<', $onlineCutoff);
                    });
            })->orWhere(function ($qBg) use ($backgroundCutoff) {
                $qBg->where('online_status', 'background')
                    ->where(function ($t) use ($backgroundCutoff) {
                        $t->whereNull('last_activity_at')
                          ->orWhere('last_activity_at', '<', $backgroundCutoff);
                    });
            })->orWhere(function ($qLegacy) use ($onlineCutoff) {
                $qLegacy->where('is_online', 1)
                    ->where(function ($s) {
                        $s->whereNull('online_status')
                          ->orWhere('online_status', '')
                          ->orWhere('online_status', 'offline');
                    })
                    ->where(function ($t) use ($onlineCutoff) {
                        $t->whereNull('last_activity_at')
                          ->orWhere('last_activity_at', '<', $onlineCutoff);
                    });
            });
        });

        if ($ownerId) {
            $staleQuery->where('owner_id', $ownerId);
        }

        foreach ($staleQuery->get() as $w) {
            $updated = static::where('id', $w->id)
                ->where(function ($q) {
                    $q->whereIn('online_status', ['online', 'background'])
                      ->orWhere('is_online', 1);
                })
                ->update([
                    'online_status' => 'offline',
                    'is_online' => false,
                ]);

            if ($updated > 0) {
                $w->online_status = 'offline';
                $w->is_online = false;
                $workersToBroadcast[] = $w;
            }
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
