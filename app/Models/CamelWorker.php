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

            try {
                $activeSession = TrainingSession::where('camel_worker_id', $w->id)
                    ->whereIn('round_status', ['working', 'pending'])
                    ->latest()
                    ->first();
                if ($activeSession) {
                    $activeSession->update(['round_status' => 'stop']);
                    $sessionController = app(\App\Http\Controllers\TrainingSessionController::class);
                    $sessionController->notifyOwnerWorkerClosedApp($activeSession, $w);
                    broadcast(new \App\Events\TrainingSessionEnded($activeSession));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Error handling active session on worker offline sync: " . $e->getMessage());
            }
        }

        // 3. Move active sessions ('working') with no GPS logs for > 10s to 'stop'
        try {
            $workingSessionsQuery = TrainingSession::with('worker.owner')
                ->where('round_status', 'working');
            if ($ownerId) {
                $workingSessionsQuery->whereHas('worker', function ($q) use ($ownerId) {
                    $q->where('owner_id', $ownerId);
                });
            }
            foreach ($workingSessionsQuery->get() as $sess) {
                $latestLog = \App\Models\SessionSpeedLog::where('training_session_id', $sess->id)->orderBy('id', 'desc')->first();
                $lastActivity = $latestLog ? $latestLog->created_at : $sess->updated_at;
                $diffSeconds = $lastActivity ? now()->diffInSeconds($lastActivity) : 999;
                $worker = $sess->worker;

                $isSilent = ($diffSeconds >= 10) ||
                    (($worker && in_array($worker->online_status, ['background', 'offline'])) && $diffSeconds >= 6);

                if ($isSilent) {
                    $sess->round_status = 'stop';
                    $sess->save();

                    if ($worker) {
                        $worker->online_status = 'offline';
                        $worker->is_online = 0;
                        $worker->save();
                        try {
                            broadcast(new \App\Events\WorkerStatusUpdated($worker));
                        } catch (\Exception $e) {}
                    }

                    try {
                        broadcast(new \App\Events\LocationUpdated($latestLog, null, $sess));
                        broadcast(new \App\Events\TrainingSessionEnded($sess));
                    } catch (\Exception $e) {}

                    $sessionController = app(\App\Http\Controllers\TrainingSessionController::class);
                    $sessionController->notifyOwnerWorkerClosedApp($sess, $worker);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Error checking active session silence in syncOnlineStatuses: " . $e->getMessage());
        }
    }
}
