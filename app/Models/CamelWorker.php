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
     * 1) > 45s without ping while in 'online' -> transition to 'background'
     * 2) > 90s without ping -> transition to 'offline'
     */
    public static function syncOnlineStatuses($ownerId = null)
    {
        // 1. Move stale online (> 45s) to background
        $staleOnline = static::where('online_status', 'online')
            ->where(function ($q) {
                $q->whereNull('last_activity_at')
                  ->orWhere('last_activity_at', '<', now()->subSeconds(45));
            });
        if ($ownerId) {
            $staleOnline->where('owner_id', $ownerId);
        }
        foreach ($staleOnline->get() as $w) {
            $w->online_status = 'background';
            $w->is_online = true;
            $w->save();
            try {
                broadcast(new \App\Events\WorkerStatusUpdated($w));
            } catch (\Exception $e) {}
        }

        // 2. Move stale background or online (> 90s) to offline
        $staleAll = static::whereIn('online_status', ['online', 'background'])
            ->where(function ($q) {
                $q->whereNull('last_activity_at')
                  ->orWhere('last_activity_at', '<', now()->subSeconds(90));
            });
        if ($ownerId) {
            $staleAll->where('owner_id', $ownerId);
        }
        foreach ($staleAll->get() as $w) {
            $w->online_status = 'offline';
            $w->is_online = false;
            $w->save();
            try {
                broadcast(new \App\Events\WorkerStatusUpdated($w));
            } catch (\Exception $e) {}
        }
    }
}
