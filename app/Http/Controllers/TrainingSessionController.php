<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CamelWorker;
use App\Models\TrainingSession;
use App\Models\NotificationForApp;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TrainingSessionController extends Controller
{
    /**
     * Resolve a media/file path safely to a full URL (handles both external URLs and relative paths).
     */
    private function resolveMediaUrl(?string $path, ?string $fallback = null): ?string
    {
        if (empty($path)) {
            return $fallback;
        }
        $trimmed = trim($path);
        if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
            return $trimmed;
        }
        return asset(ltrim($trimmed, '/'));
    }

    /**
     * Display a listing of training sessions with rich filters, KPIs, and pagination (Admin Panel).
     */
    public function index(Request $request)
    {
        // Keep worker online statuses accurate without blocking
        try {
            CamelWorker::syncOnlineStatuses();
        } catch (\Throwable $e) {
            Log::warning('Admin index syncOnlineStatuses failed: ' . $e->getMessage());
        }

        $owners = User::where('role', 'owner')
            ->select('id', 'fname', 'lname', 'phone')
            ->orderBy('fname')
            ->get();

        $workersList = CamelWorker::select('id', 'owner_id', 'full_name', 'online_status', 'is_online')
            ->orderBy('full_name')
            ->get();

        $query = TrainingSession::with(['worker.owner'])
            ->withCount('speedLogs')
            ->withMax('speedLogs', 'speed');

        // Filter by specific worker
        if ($request->filled('camel_worker_id')) {
            $query->where('camel_worker_id', (int)$request->camel_worker_id);
        }

        // Filter by owner (sessions for all workers belonging to this owner)
        if ($request->filled('owner_id')) {
            $workerIds = CamelWorker::where('owner_id', (int)$request->owner_id)->pluck('id');
            $query->whereIn('camel_worker_id', $workerIds);
        }

        // Filter by status (supports both legacy active/ended and exact statuses)
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'active') {
                $query->whereIn('round_status', ['pending', 'working', 'stop']);
            } elseif ($status === 'ended' || $status === 'end') {
                $query->where('round_status', 'end');
            } elseif (in_array($status, ['working', 'pending', 'stop'], true)) {
                $query->where('round_status', $status);
            }
        }

        // Date range filter
        if ($request->filled('date_from')) {
            try {
                $query->whereDate('created_at', '>=', Carbon::parse($request->date_from)->format('Y-m-d'));
            } catch (\Throwable $e) {
            }
        }
        if ($request->filled('date_to')) {
            try {
                $query->whereDate('created_at', '<=', Carbon::parse($request->date_to)->format('Y-m-d'));
            } catch (\Throwable $e) {
            }
        }

        // Free-text search (session ID, location, worker name, owner name/phone)
        if ($request->filled('search')) {
            $term = trim((string)$request->search);
            $numericTerm = ltrim($term, '#');
            $query->where(function ($q) use ($term, $numericTerm) {
                if (ctype_digit($numericTerm)) {
                    $q->orWhere('id', (int)$numericTerm);
                }
                $q->orWhere('location_name', 'like', "%{$term}%")
                  ->orWhereHas('worker', function ($wq) use ($term) {
                      $wq->where('full_name', 'like', "%{$term}%")
                         ->orWhere('phone', 'like', "%{$term}%")
                         ->orWhereHas('owner', function ($oq) use ($term) {
                             $oq->where('fname', 'like', "%{$term}%")
                                ->orWhere('lname', 'like', "%{$term}%")
                                ->orWhere('phone', 'like', "%{$term}%")
                                ->orWhereRaw("CONCAT(COALESCE(fname,''), ' ', COALESCE(lname,'')) LIKE ?", ["%{$term}%"]);
                         });
                  });
            });
        }

        // Filtered summary aggregates (before pagination)
        $filteredSummary = (clone $query)
            ->reorder()
            ->selectRaw('COUNT(*) as filtered_count, COALESCE(SUM(round_distance_km), 0) as filtered_distance_km')
            ->first();

        // Global operations KPIs (1 fast aggregated query)
        $todayStart = Carbon::today()->startOfDay();
        $globalSummary = \Illuminate\Support\Facades\DB::table('training_sessions')
            ->selectRaw("
                COUNT(*) as total_count,
                SUM(CASE WHEN round_status = 'working' THEN 1 ELSE 0 END) as live_now,
                SUM(CASE WHEN round_status IN ('pending', 'stop') THEN 1 ELSE 0 END) as paused_pending,
                SUM(CASE WHEN round_status = 'end' THEN 1 ELSE 0 END) as ended_count,
                SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as today_count
            ", [$todayStart])
            ->first();

        $stats = [
            'live_now'             => (int)($globalSummary->live_now ?? 0),
            'paused_pending'       => (int)($globalSummary->paused_pending ?? 0),
            'today_count'          => (int)($globalSummary->today_count ?? 0),
            'ended_count'          => (int)($globalSummary->ended_count ?? 0),
            'total_count'          => (int)($globalSummary->total_count ?? 0),
            'filtered_count'       => (int)($filteredSummary->filtered_count ?? 0),
            'filtered_distance_km' => (float)($filteredSummary->filtered_distance_km ?? 0),
        ];

        $perPage = (int)$request->input('per_page', 25);
        if (!in_array($perPage, [15, 25, 50, 100], true)) {
            $perPage = 25;
        }

        $sessions = $query->latest('id')->paginate($perPage)->withQueryString();

        // Live operations strip: up to 12 currently active/paused sessions
        $activeSessionsPreview = TrainingSession::with(['worker.owner'])
            ->whereIn('round_status', ['working', 'pending', 'stop'])
            ->latest('updated_at')
            ->limit(12)
            ->get();

        // Batch-load latest GPS log for all sessions on the page + live preview strip (1 query, zero N+1)
        $allVisibleIds = $sessions->getCollection()->pluck('id')
            ->merge($activeSessionsPreview->pluck('id'))
            ->unique()
            ->values()
            ->all();

        $latestLogsBySession = collect();
        if (!empty($allVisibleIds)) {
            $latestLogIds = \Illuminate\Support\Facades\DB::table('session_speed_logs')
                ->whereIn('training_session_id', $allVisibleIds)
                ->selectRaw('MAX(id) as max_id')
                ->groupBy('training_session_id')
                ->pluck('max_id');

            if ($latestLogIds->isNotEmpty()) {
                $latestLogsBySession = \Illuminate\Support\Facades\DB::table('session_speed_logs')
                    ->whereIn('id', $latestLogIds)
                    ->select(['id', 'training_session_id', 'latitude', 'longitude', 'speed', 'created_at', 'updated_at'])
                    ->get()
                    ->keyBy('training_session_id');
            }
        }

        $enrichSession = function ($item) use ($latestLogsBySession) {
            $latestLog = $latestLogsBySession->get($item->id);
            $activity = $this->resolveActivityStatus($item, $latestLog);
            $item->is_desert_mode = $activity['is_desert_mode'];
            $item->is_weak_connection = $activity['is_weak_connection'];
            $item->signal_diff_seconds = $activity['diff_seconds'];
            $item->resolved_round_time = $this->resolveLiveRoundTime($item);
            $item->latest_lat = ($latestLog && $latestLog->latitude) ? (float)$latestLog->latitude : ($item->latitude ? (float)$item->latitude : null);
            $item->latest_lng = ($latestLog && $latestLog->longitude) ? (float)$latestLog->longitude : ($item->longitude ? (float)$item->longitude : null);
            $item->worker_avatar_url = $this->resolveMediaUrl(
                $item->worker ? $item->worker->photo_path : null,
                asset('upload/no_image.jpg')
            );
            return $item;
        };

        $sessions->getCollection()->transform($enrichSession);
        $activeSessionsPreview->transform($enrichSession);

        return view('admin.training_session.all_sessions', compact(
            'owners',
            'workersList',
            'sessions',
            'stats',
            'activeSessionsPreview',
            'perPage'
        ));
    }

    /**
     * Admin-only lightweight JSON snapshot for live operations auto-refresh on /training-sessions/all.
     */
    public function liveOverview(Request $request)
    {
        try {
            CamelWorker::syncOnlineStatuses();
        } catch (\Throwable $e) {
        }

        $todayStart = Carbon::today()->startOfDay();
        $globalSummary = \Illuminate\Support\Facades\DB::table('training_sessions')
            ->selectRaw("
                COUNT(*) as total_count,
                SUM(CASE WHEN round_status = 'working' THEN 1 ELSE 0 END) as live_now,
                SUM(CASE WHEN round_status IN ('pending', 'stop') THEN 1 ELSE 0 END) as paused_pending,
                SUM(CASE WHEN round_status = 'end' THEN 1 ELSE 0 END) as ended_count,
                SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as today_count
            ", [$todayStart])
            ->first();

        $activeSessions = TrainingSession::with(['worker.owner'])
            ->whereIn('round_status', ['working', 'pending', 'stop'])
            ->latest('updated_at')
            ->limit(12)
            ->get();

        $sessionIds = $activeSessions->pluck('id')->all();
        $latestLogsBySession = collect();
        if (!empty($sessionIds)) {
            $latestLogIds = \Illuminate\Support\Facades\DB::table('session_speed_logs')
                ->whereIn('training_session_id', $sessionIds)
                ->selectRaw('MAX(id) as max_id')
                ->groupBy('training_session_id')
                ->pluck('max_id');

            if ($latestLogIds->isNotEmpty()) {
                $latestLogsBySession = \Illuminate\Support\Facades\DB::table('session_speed_logs')
                    ->whereIn('id', $latestLogIds)
                    ->select(['id', 'training_session_id', 'latitude', 'longitude', 'speed', 'created_at', 'updated_at'])
                    ->get()
                    ->keyBy('training_session_id');
            }
        }

        $items = $activeSessions->map(function ($item) use ($latestLogsBySession) {
            $latestLog = $latestLogsBySession->get($item->id);
            $activity = $this->resolveActivityStatus($item, $latestLog);
            $ownerName = ($item->worker && $item->worker->owner)
                ? trim($item->worker->owner->fname . ' ' . $item->worker->owner->lname)
                : 'غير محدد';

            return [
                'id'                  => $item->id,
                'worker_name'         => $item->worker ? $item->worker->full_name : 'عامل غير معروف',
                'worker_online'       => $item->worker ? (bool)($item->worker->is_online || $item->worker->online_status === 'online') : false,
                'owner_name'          => $ownerName,
                'round_status'        => $item->round_status,
                'is_desert_mode'      => $activity['is_desert_mode'],
                'is_weak_connection'  => $activity['is_weak_connection'],
                'signal_diff_seconds' => $activity['diff_seconds'],
                'speed'               => number_format((float)$item->speed, 2, '.', ''),
                'average_speed'       => number_format((float)$item->average_speed, 2, '.', ''),
                'distance'            => number_format((float)$item->round_distance_km, 2, '.', ''),
                'round_time'          => $this->resolveLiveRoundTime($item),
                'location_name'       => $item->location_name ?: 'موقع غير محدد',
                'details_url'         => route('details.training.session', $item->id),
            ];
        })->values();

        return response()->json([
            'success'         => true,
            'server_time'     => now()->format('H:i:s'),
            'stats'           => [
                'live_now'       => (int)($globalSummary->live_now ?? 0),
                'paused_pending' => (int)($globalSummary->paused_pending ?? 0),
                'today_count'    => (int)($globalSummary->today_count ?? 0),
                'ended_count'    => (int)($globalSummary->ended_count ?? 0),
                'total_count'    => (int)($globalSummary->total_count ?? 0),
            ],
            'active_sessions' => $items,
        ]);
    }

    /**
     * Display the specified training session details with high-performance compact route,
     * speed analytics, paginated logs, and real-time monitoring support (Admin Panel).
     */
    public function show($id)
    {
        $session = TrainingSession::with([
            'worker.owner',
            'chats' => function ($q) {
                $q->with(['owner', 'worker'])->orderBy('created_at', 'asc');
            },
            'instructions' => function ($q) {
                $q->with(['owner', 'worker'])->orderBy('created_at', 'asc');
            },
        ])->findOrFail($id);

        if ($session->worker && $session->worker->owner_id) {
            try {
                CamelWorker::syncOnlineStatuses($session->worker->owner_id);
                $session->worker->refresh();
            } catch (\Throwable $e) {
            }
        }

        // 1. Fast SQL aggregate query on session_speed_logs (zero Eloquent hydration overhead)
        $logStats = \Illuminate\Support\Facades\DB::table('session_speed_logs')
            ->where('training_session_id', $session->id)
            ->selectRaw('
                COUNT(*) as total_count,
                COALESCE(MAX(speed), 0) as max_speed,
                COALESCE(AVG(speed), 0) as avg_speed,
                MAX(id) as max_id
            ')
            ->first();

        $totalLogsCount = (int)($logStats->total_count ?? 0);
        $maxSpeed = (float)($logStats->max_speed ?? 0);
        $lastLogId = (int)($logStats->max_id ?? 0);

        // 2. Fetch lean cleaned/compacted route points (reuses battle-tested RDP & untangling!)
        $isEnded = ($session->round_status === 'end');
        $cacheKey = "session_compact_route_v3_{$session->id}";
        $routePoints = null;

        if ($isEnded) {
            $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
            if (is_array($cached) && !empty($cached)) {
                $routePoints = $cached;
            }
        }

        if ($routePoints === null) {
            $rawRows = \Illuminate\Support\Facades\DB::table('session_speed_logs')
                ->where('training_session_id', $session->id)
                ->select(['id', 'training_session_id', 'speed', 'latitude', 'longitude', 'created_at'])
                ->orderBy('id', 'asc')
                ->get()
                ->all();

            $routePoints = $this->cleanAndCompactSpeedLogs($rawRows, $isEnded, true);

            if ($isEnded && count($routePoints) >= 2) {
                \Illuminate\Support\Facades\Cache::put($cacheKey, $routePoints, 3600);
            }
        }

        // 3. Peak speed point & speed zones distribution
        $peakLog = \Illuminate\Support\Facades\DB::table('session_speed_logs')
            ->where('training_session_id', $session->id)
            ->where('speed', '>', 0)
            ->orderByDesc('speed')
            ->orderBy('id', 'asc')
            ->select(['id', 'speed', 'latitude', 'longitude', 'created_at'])
            ->first();

        if ($peakLog && (float)$peakLog->speed > $maxSpeed) {
            $maxSpeed = (float)$peakLog->speed;
        }

        $zoneCounts = \Illuminate\Support\Facades\DB::table('session_speed_logs')
            ->where('training_session_id', $session->id)
            ->selectRaw("
                SUM(CASE WHEN speed < 15 THEN 1 ELSE 0 END) as low_cnt,
                SUM(CASE WHEN speed >= 15 AND speed < 30 THEN 1 ELSE 0 END) as mid_cnt,
                SUM(CASE WHEN speed >= 30 THEN 1 ELSE 0 END) as high_cnt
            ")
            ->first();

        $lowCnt = (int)($zoneCounts->low_cnt ?? 0);
        $midCnt = (int)($zoneCounts->mid_cnt ?? 0);
        $highCnt = (int)($zoneCounts->high_cnt ?? 0);
        $zoneTotal = max(1, $lowCnt + $midCnt + $highCnt);

        // 4. Chart points (cap at ~180 points for ultra-smooth Chart.js rendering while preserving peak)
        $chartPoints = [];
        $routeCount = count($routePoints);
        if ($routeCount > 0) {
            $maxChartPoints = 180;
            if ($routeCount <= $maxChartPoints) {
                $chartPoints = $routePoints;
            } else {
                $step = ($routeCount - 1) / ($maxChartPoints - 1);
                $pickedIdx = [];
                for ($i = 0; $i < $maxChartPoints; $i++) {
                    $idx = (int)round($i * $step);
                    $pickedIdx[$idx] = true;
                }
                // Always include the highest-speed point in the route
                $peakRouteIdx = 0;
                $peakRouteSpeed = -1;
                foreach ($routePoints as $idx => $pt) {
                    if (($pt['speed'] ?? 0) > $peakRouteSpeed) {
                        $peakRouteSpeed = $pt['speed'] ?? 0;
                        $peakRouteIdx = $idx;
                    }
                }
                $pickedIdx[$peakRouteIdx] = true;
                ksort($pickedIdx);
                foreach (array_keys($pickedIdx) as $idx) {
                    $chartPoints[] = $routePoints[$idx];
                }
            }
        }

        // 5. Latest log & real activity/connectivity status
        $latestLog = \Illuminate\Support\Facades\DB::table('session_speed_logs')
            ->where('training_session_id', $session->id)
            ->select(['id', 'training_session_id', 'latitude', 'longitude', 'speed', 'created_at', 'updated_at'])
            ->orderBy('id', 'desc')
            ->first();

        $activityStatus = $this->resolveActivityStatus($session, $latestLog);

        // 6. Accurate Start & Current/End coordinates (first GPS log vs latest GPS log)
        $hasValidSessionCoord = ($session->latitude !== null && $session->longitude !== null && ((float)$session->latitude != 0.0 || (float)$session->longitude != 0.0));
        $startLat = $routeCount > 0
            ? (float)$routePoints[0]['latitude']
            : ($hasValidSessionCoord ? (float)$session->latitude : 24.963000);
        $startLng = $routeCount > 0
            ? (float)$routePoints[0]['longitude']
            : ($hasValidSessionCoord ? (float)$session->longitude : 55.480000);

        $currentLat = $routeCount > 0
            ? (float)$routePoints[$routeCount - 1]['latitude']
            : $startLat;
        $currentLng = $routeCount > 0
            ? (float)$routePoints[$routeCount - 1]['longitude']
            : $startLng;

        // 7. Paginated detailed speed logs table (fast & memory-safe)
        $speedLogs = \App\Models\SessionSpeedLog::where('training_session_id', $session->id)
            ->orderBy('id', 'desc')
            ->paginate(50)
            ->withQueryString();

        $exactDist = \Illuminate\Support\Facades\Cache::get('session_exact_dist_' . $session->id);
        $distanceKm = $exactDist !== null ? (float)$exactDist : (float)($session->round_distance_km ?? 0);

        $sessionMetrics = [
            'total_logs_count' => $totalLogsCount,
            'max_speed'        => $maxSpeed,
            'max_speed_time'   => ($peakLog && $peakLog->created_at) ? Carbon::parse($peakLog->created_at)->format('H:i:s') : null,
            'max_speed_lat'    => ($peakLog && $peakLog->latitude) ? (float)$peakLog->latitude : null,
            'max_speed_lng'    => ($peakLog && $peakLog->longitude) ? (float)$peakLog->longitude : null,
            'avg_speed'        => (float)($session->average_speed ?? ($logStats->avg_speed ?? 0)),
            'current_speed'    => (float)($session->speed ?? 0),
            'distance_km'      => $distanceKm,
            'round_time'       => $this->resolveLiveRoundTime($session),
            'performance'      => (float)($session->performance ?? 0),
            'last_log_id'      => $lastLogId,
            'last_chat_id'     => (int)($session->chats->max('id') ?? 0),
            'start_lat'        => $startLat,
            'start_lng'        => $startLng,
            'current_lat'      => $currentLat,
            'current_lng'      => $currentLng,
            'has_coordinates'  => ($routeCount > 0 || $hasValidSessionCoord),
            'speed_zones'      => [
                'low'        => $lowCnt,
                'medium'     => $midCnt,
                'high'       => $highCnt,
                'low_pct'    => round(($lowCnt / $zoneTotal) * 100, 1),
                'medium_pct' => round(($midCnt / $zoneTotal) * 100, 1),
                'high_pct'   => round(($highCnt / $zoneTotal) * 100, 1),
            ],
        ];

        $workerAvatarUrl = $this->resolveMediaUrl(
            $session->worker ? $session->worker->photo_path : null,
            asset('upload/no_image.jpg')
        );
        $summaryAudioUrl = $this->resolveMediaUrl($session->summary_audio);
        $summaryImageUrl = $this->resolveMediaUrl($session->summary_image);

        // Safe Reverb config read from config() instead of raw env()
        $reverbCfg = config('broadcasting.connections.reverb', []);
        $reverbOptions = $reverbCfg['options'] ?? [];
        $reverbConfig = [
            'enabled' => config('broadcasting.default') === 'reverb' && !empty($reverbCfg['key']),
            'key'     => (string)($reverbCfg['key'] ?? ''),
            'host'    => (string)($reverbOptions['host'] ?? request()->getHost()),
            'port'    => (int)($reverbOptions['port'] ?? 8080),
            'scheme'  => (string)($reverbOptions['scheme'] ?? (request()->isSecure() ? 'https' : 'http')),
        ];

        return view('admin.training_session.session_details', compact(
            'session',
            'speedLogs',
            'routePoints',
            'chartPoints',
            'sessionMetrics',
            'activityStatus',
            'workerAvatarUrl',
            'summaryAudioUrl',
            'summaryImageUrl',
            'reverbConfig'
        ));
    }

    /**
     * Admin-only incremental live polling endpoint for /training-sessions/details/{id}.
     * Keeps the map, KPIs, speed chart, and chat updated in real time even if WebSockets disconnect.
     */
    public function liveData(Request $request, $id)
    {
        $session = TrainingSession::with(['worker.owner'])->findOrFail($id);

        if ($session->worker && $session->worker->owner_id) {
            try {
                CamelWorker::syncOnlineStatuses($session->worker->owner_id);
                $session->worker->refresh();
            } catch (\Throwable $e) {
            }
        }

        $afterId = max(0, (int)$request->input('after_id', 0));
        $afterChatId = max(0, (int)$request->input('after_chat_id', 0));

        $newRawLogs = \Illuminate\Support\Facades\DB::table('session_speed_logs')
            ->where('training_session_id', $session->id)
            ->where('id', '>', $afterId)
            ->select(['id', 'training_session_id', 'speed', 'latitude', 'longitude', 'location_name', 'created_at', 'updated_at'])
            ->orderBy('id', 'asc')
            ->limit(200)
            ->get()
            ->all();

        $newLogs = $this->formatLeanSpeedLogs($newRawLogs, false);

        $agg = \Illuminate\Support\Facades\DB::table('session_speed_logs')
            ->where('training_session_id', $session->id)
            ->selectRaw('COUNT(*) as total_count, COALESCE(MAX(speed), 0) as max_speed, MAX(id) as max_id')
            ->first();

        $latestLog = \Illuminate\Support\Facades\DB::table('session_speed_logs')
            ->where('training_session_id', $session->id)
            ->select(['id', 'training_session_id', 'latitude', 'longitude', 'speed', 'created_at', 'updated_at'])
            ->orderBy('id', 'desc')
            ->first();

        $activity = $this->resolveActivityStatus($session, $latestLog);

        // Incremental new chats
        $newChatsModels = \App\Models\SessionChat::with(['owner', 'worker'])
            ->where('training_session_id', $session->id)
            ->where('id', '>', $afterChatId)
            ->orderBy('id', 'asc')
            ->limit(50)
            ->get();

        $newChats = $newChatsModels->map(function ($chat) use ($session) {
            $isOwner = !empty($chat->owner_id);
            $senderName = $isOwner
                ? ($chat->owner ? trim($chat->owner->fname . ' ' . $chat->owner->lname) : ($session->worker && $session->worker->owner ? trim($session->worker->owner->fname . ' ' . $session->worker->owner->lname) : 'المالك'))
                : ($chat->worker ? $chat->worker->full_name : ($session->worker ? $session->worker->full_name : 'العامل'));

            return [
                'id'           => $chat->id,
                'is_owner'     => $isOwner,
                'sender_name'  => $senderName,
                'content_type' => $chat->content_type,
                'content'      => $chat->content_type === 'text' ? $chat->content : $this->resolveMediaUrl($chat->content),
                'created_at'   => $chat->created_at ? $chat->created_at->format('H:i:s | Y-m-d') : '',
            ];
        })->values();

        $exactDist = \Illuminate\Support\Facades\Cache::get('session_exact_dist_' . $session->id);
        $distVal = $exactDist !== null ? (float)$exactDist : (float)($session->round_distance_km ?? 0);

        Carbon::setLocale('ar');
        $lastActivityHuman = ($session->worker && $session->worker->last_activity_at)
            ? $session->worker->last_activity_at->diffForHumans()
            : null;

        return response()->json([
            'success'      => true,
            'server_time'  => now()->format('H:i:s'),
            'session'      => [
                'id'                => $session->id,
                'round_status'      => $session->round_status,
                'speed'             => number_format((float)$session->speed, 2, '.', ''),
                'average_speed'     => number_format((float)$session->average_speed, 2, '.', ''),
                'max_speed'         => number_format((float)($agg->max_speed ?? 0), 2, '.', ''),
                'distance'          => number_format($distVal, 2, '.', ''),
                'round_time'        => $this->resolveLiveRoundTime($session),
                'performance'       => number_format((float)$session->performance, 2, '.', ''),
                'logs_count'        => (int)($agg->total_count ?? 0),
                'session_ended_at'  => $session->session_ended_at ? $session->session_ended_at->format('Y-m-d H:i:s') : null,
                'summary_text'      => $session->summary_text,
                'summary_audio_url' => $this->resolveMediaUrl($session->summary_audio),
                'summary_image_url' => $this->resolveMediaUrl($session->summary_image),
            ],
            'activity'     => $activity,
            'worker'       => [
                'online_status'       => $session->worker ? ($session->worker->online_status ?? 'offline') : 'offline',
                'is_online'           => $session->worker ? (bool)($session->worker->is_online || $session->worker->online_status === 'online') : false,
                'last_activity_human' => $lastActivityHuman,
            ],
            'new_logs'     => $newLogs,
            'last_log_id'  => (int)($agg->max_id ?? $afterId),
            'new_chats'    => $newChats,
            'last_chat_id' => $newChatsModels->isNotEmpty() ? (int)$newChatsModels->last()->id : $afterChatId,
        ]);
    }

    /**
     * Admin-only CSV export for session speed logs (UTF-8 BOM for clean Excel Arabic rendering).
     */
    public function exportLogs($id)
    {
        $session = TrainingSession::with(['worker.owner'])->findOrFail($id);
        $filename = 'training_session_' . $session->id . '_logs_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($session) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Microsoft Excel Arabic support
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                '#',
                'رقم الجلسة',
                'السرعة (كم/س)',
                'خط العرض (Latitude)',
                'خط الطول (Longitude)',
                'الموقع',
                'وقت التسجيل',
            ]);

            $index = 1;
            $cursor = \Illuminate\Support\Facades\DB::table('session_speed_logs')
                ->where('training_session_id', $session->id)
                ->orderBy('id', 'asc')
                ->select(['speed', 'latitude', 'longitude', 'location_name', 'created_at'])
                ->cursor();

            foreach ($cursor as $row) {
                fputcsv($handle, [
                    $index++,
                    $session->id,
                    number_format((float)$row->speed, 2, '.', ''),
                    $row->latitude,
                    $row->longitude,
                    $row->location_name ?? '',
                    $row->created_at ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Remove the specified training session.
     */
    public function destroy($id)
    {
        $session = TrainingSession::findOrFail($id);
        $session->delete();

        $notification = [
            'message'    => 'تم حذف الجلسة التدريبية بنجاح',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.training.sessions')->with($notification);
    }

    /**
     * Simulate a GPS ping for the training session and save it.
     */
    public function simulatePing($id)
    {
        $session = TrainingSession::findOrFail($id);
        $logsCount = $session->logs_count ?? 0;
        
        $baseLat = $session->latitude ? (double)$session->latitude : 24.963000;
        $baseLng = $session->longitude ? (double)$session->longitude : 55.480000;
        
        $lat = null;
        $lng = null;
        $isRoadRoute = false;
        
        // 1. Try to get cached road route
        $cacheKey = "session_route_{$id}";
        $routePoints = \Illuminate\Support\Facades\Cache::get($cacheKey);
        
        if (!$routePoints) {
            $apiKey = config('services.google_maps.key');
            if ($apiKey) {
                // Determine a destination that is about 1.5 - 2km away
                $destLat = $baseLat + 0.015;
                $destLng = $baseLng + 0.015;
                
                $url = "https://maps.googleapis.com/maps/api/directions/json?origin={$baseLat},{$baseLng}&destination={$destLat},{$destLng}&mode=driving&key={$apiKey}";
                
                try {
                    $response = @file_get_contents($url);
                    if ($response) {
                        $data = json_decode($response, true);
                        if (isset($data['status']) && $data['status'] === 'OK' && isset($data['routes'][0]['overview_polyline']['points'])) {
                            $encodedPolyline = $data['routes'][0]['overview_polyline']['points'];
                            $points = $this->decodePolyline($encodedPolyline);
                            
                            if (count($points) > 0) {
                                // Interpolate intermediate points to make the movements slow and smooth
                                $points = $this->interpolatePoints($points, 0.005); // ~5 meters max per step
                                
                                // Create a back-and-forth loop to avoid sudden jump at the end
                                $reversed = array_reverse($points);
                                array_shift($reversed);
                                array_pop($reversed);
                                $routePoints = array_merge($points, $reversed);
                                
                                \Illuminate\Support\Facades\Cache::put($cacheKey, $routePoints, 3600); // cache for 1 hour
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore and fallback
                }
            }
        }
        
        if ($routePoints && count($routePoints) > 0) {
            $index = $logsCount % count($routePoints);
            $lat = $routePoints[$index]['latitude'];
            $lng = $routePoints[$index]['longitude'];
            $isRoadRoute = true;
        } else {
            // Fallback: Shift center of parametric oval path so that the path starts exactly at ($baseLat, $baseLng) when logsCount = 0
            $angle = $logsCount * 0.02; // Reduced angle increment to make the fallback simulation slow and moderate
            $radius = 0.005; // ~500m radius
            $centerLat = $baseLat;
            $centerLng = $baseLng - ($radius * 1.5);
            
            $lat = $centerLat + $radius * sin($angle);
            $lng = $centerLng + $radius * 1.5 * cos($angle);
        }
        
        // Moderate training speed (e.g. 12 - 25 km/h)
        $speed = rand(120, 250) / 10;
        
        // Calculate actual distance between last point and new point
        $distanceIncrement = 0.03; // default fallback increment in km
        $cacheKeyLastCoord = "session_last_coordinate_{$id}";
        $lastCoordinate = \Illuminate\Support\Facades\Cache::get($cacheKeyLastCoord);
        
        if ($lastCoordinate) {
            $distanceIncrement = $this->getDistance($lastCoordinate['latitude'], $lastCoordinate['longitude'], $lat, $lng);
            // If the increment is unusually large (due to looping back or a jump), cap it or use default
            if ($distanceIncrement > 0.5) {
                $distanceIncrement = 0.03;
            }
        } else {
            $lastLog = \App\Models\SessionSpeedLog::where('training_session_id', $id)->orderBy('id', 'desc')->first();
            if ($lastLog) {
                $distanceIncrement = $this->getDistance($lastLog->latitude, $lastLog->longitude, $lat, $lng);
                if ($distanceIncrement > 0.5) {
                    $distanceIncrement = 0.03;
                }
            } else {
                // First point from start position
                $distanceIncrement = $this->getDistance($baseLat, $baseLng, $lat, $lng);
                if ($distanceIncrement > 0.5) {
                    $distanceIncrement = 0.03;
                }
            }
        }
        
        // Cache the current coordinate
        \Illuminate\Support\Facades\Cache::put($cacheKeyLastCoord, [
            'latitude' => $lat,
            'longitude' => $lng
        ], 300);
        
        $locationName = $isRoadRoute ? "طريق محاكاة فعلي #" . ($logsCount + 1) : "موقع المحاكاة اللحظي #" . ($logsCount + 1);
        
        $log = \App\Models\SessionSpeedLog::create([
            'training_session_id' => $id,
            'speed' => $speed,
            'latitude' => $lat,
            'longitude' => $lng,
            'location_name' => $locationName,
        ]);
        
        $newAvgSpeed = (($session->average_speed * $logsCount) + $speed) / ($logsCount + 1);
        $newDistance = $session->round_distance_km + $distanceIncrement;
        
        $updateData = [
            'latitude' => $lat,
            'longitude' => $lng,
            'speed' => $speed,
            'average_speed' => $newAvgSpeed,
            'round_distance_km' => $newDistance,
            'logs_count' => $logsCount + 1,
            'round_status' => 'working',
        ];
        
        if (request()->filled('duration')) {
            $updateData['round_time'] = request('duration');
        }
        
        $session->update($updateData);
        
        return response()->json([
            'success' => true,
            'log' => [
                'id' => $log->id,
                'speed' => number_format($log->speed, 2),
                'latitude' => (double)$log->latitude,
                'longitude' => (double)$log->longitude,
                'location_name' => $log->location_name,
                'time' => $log->created_at->format('Y-m-d H:i:s'),
            ],
            'current_speed' => number_format($speed, 2),
            'average_speed' => number_format($newAvgSpeed, 2),
            'distance' => number_format($newDistance, 2),
        ]);
    }

    /**
     * Clear all speed logs for the specified training session.
     */
    public function clearLogs($id)
    {
        $session = TrainingSession::findOrFail($id);
        
        // Delete logs from database
        \App\Models\SessionSpeedLog::where('training_session_id', $id)->delete();
        
        // Clear cached route and last coordinate
        \Illuminate\Support\Facades\Cache::forget("session_route_{$id}");
        \Illuminate\Support\Facades\Cache::forget("session_compact_route_v3_{$id}");
        \Illuminate\Support\Facades\Cache::forget("session_last_coordinate_{$id}");
        \Illuminate\Support\Facades\Cache::forget("session_last_coord_{$id}");
        \Illuminate\Support\Facades\Cache::forget("session_exact_dist_{$id}");
        
        // Reset metrics
        $session->update([
            'speed' => 0.00,
            'average_speed' => 0.00,
            'round_distance_km' => 0.00,
            'logs_count' => 0,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'تم حذف جميع السجلات وإعادة تهيئة إحصائيات الجلسة بنجاح.',
        ]);
    }

    /**
     * Update the status of the specified training session.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,working,stop,end',
        ]);

        $session = TrainingSession::findOrFail($id);
        
        $updateData = [
            'round_status' => $request->status,
        ];
        
        if ($request->status === 'end') {
            $updateData['session_ended_at'] = now();
        }
        
        $session->update($updateData);

        // Broadcast the status update as well
        try {
            // We can broadcast a mock location updated or specific status event if needed,
            // but updating status via websocket is also covered here.
            $latestLog = \App\Models\SessionSpeedLog::where('training_session_id', $id)->orderBy('id', 'desc')->first();
            if ($latestLog) {
                broadcast(new \App\Events\LocationUpdated($latestLog))->toOthers();
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("WebSocket status broadcast failed: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الجلسة بنجاح.',
            'round_status' => $session->round_status,
        ]);
    }

    /**
     * Decode a Google Maps encoded polyline.
     */
    private function decodePolyline($encoded)
    {
        $length = strlen($encoded);
        $index = 0;
        $points = [];
        $lat = 0;
        $lng = 0;

        while ($index < $length) {
            $b = 0;
            $shift = 0;
            $result = 0;
            do {
                $b = ord($encoded[$index++]) - 63;
                $result |= ($b & 0x1f) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $dlat = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $lat += $dlat;

            $shift = 0;
            $result = 0;
            do {
                $b = ord($encoded[$index++]) - 63;
                $result |= ($b & 0x1f) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $dlng = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $lng += $dlng;

            $points[] = [
                'latitude' => $lat * 1e-5,
                'longitude' => $lng * 1e-5
            ];
        }
        return $points;
    }

    /**
     * Interpolate intermediate points between route coordinates to slow down speed.
     */
    private function interpolatePoints($points, $maxDistanceKm)
    {
        $interpolated = [];
        $count = count($points);
        if ($count === 0) return $points;
        
        for ($i = 0; $i < $count - 1; $i++) {
            $p1 = $points[$i];
            $p2 = $points[$i+1];
            
            $dist = $this->getDistance($p1['latitude'], $p1['longitude'], $p2['latitude'], $p2['longitude']);
            
            $interpolated[] = $p1;
            
            if ($dist > $maxDistanceKm) {
                $steps = ceil($dist / $maxDistanceKm);
                for ($j = 1; $j < $steps; $j++) {
                    $fraction = $j / $steps;
                    $interpolated[] = [
                        'latitude' => $p1['latitude'] + ($p2['latitude'] - $p1['latitude']) * $fraction,
                        'longitude' => $p1['longitude'] + ($p2['longitude'] - $p1['longitude']) * $fraction,
                    ];
                }
            }
        }
        $interpolated[] = $points[$count - 1];
        return $interpolated;
    }

    /**
     * وقت الجلسة الفعلي الصادر مباشرة من ساعة العامل الدقيقة
     */
    private function parseDurationSec(?string $duration): int
    {
        if (empty($duration)) {
            return 0;
        }
        $parts = explode(':', trim($duration));
        if (count($parts) === 3) {
            return ((int)$parts[0] * 3600) + ((int)$parts[1] * 60) + (int)$parts[2];
        }
        if (count($parts) === 2) {
            return ((int)$parts[0] * 60) + (int)$parts[1];
        }
        return 0;
    }

    private function resolveLiveRoundTime($session): string
    {
        if (!$session) {
            return '00:00:00';
        }

        if (!empty($session->round_time) && $session->round_time !== '00:00:00') {
            return $session->round_time;
        }

        return '00:00:00';
    }

    /**
     * API to fetch training sessions of workers belonging to a specific owner, with status filtering.
     */
    public function getSessionsByOwnerApi(Request $request)
    {
        $request->validate([
            'owner_id' => 'required|exists:users,id',
            'status'   => 'nullable|string|in:active,working,paused,stop,ended,end,all',
        ], [
            'owner_id.required' => 'حقل المالك مطلوب.',
            'owner_id.exists'   => 'المالك غير موجود.',
            'status.in'         => 'حالة التصفية غير صالحة.',
        ]);

        // Check if the provided owner_id belongs to a user with the owner role
        $owner = User::find($request->owner_id);
        if (!$owner || $owner->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'The provided owner_id does not belong to a valid owner.'
            ], 403);
        }

        // Auto sync worker online statuses
        CamelWorker::syncOnlineStatuses($request->owner_id);

        // Get all worker IDs for this owner
        $workerIds = CamelWorker::where('owner_id', $request->owner_id)->pluck('id');

        $query = TrainingSession::with(['worker']);

        $query->whereIn('camel_worker_id', $workerIds);

        // Apply status filtering (skip if status is 'all')
        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status;
            if ($status === 'active' || $status === 'working') {
                $query->whereIn('round_status', ['working', 'pending']);
            } elseif ($status === 'paused' || $status === 'stop') {
                $query->where('round_status', 'stop');
            } elseif ($status === 'ended' || $status === 'end') {
                $query->where('round_status', 'end');
            }
        }

        \Carbon\Carbon::setLocale('ar');
        $now = \Carbon\Carbon::now();

        $rawSessions = $query->latest()->get();

        // Batch-load the latest SessionSpeedLog per session in 1 query instead of N+1
        $sessionIds = $rawSessions->pluck('id')->filter()->values()->all();
        $latestLogsBySession = collect();
        if (!empty($sessionIds)) {
            $latestLogIds = \Illuminate\Support\Facades\DB::table('session_speed_logs')
                ->whereIn('training_session_id', $sessionIds)
                ->selectRaw('MAX(id) as max_id')
                ->groupBy('training_session_id')
                ->pluck('max_id');
            if ($latestLogIds->isNotEmpty()) {
                $latestLogsBySession = \Illuminate\Support\Facades\DB::table('session_speed_logs')
                    ->whereIn('id', $latestLogIds)
                    ->select(['id', 'training_session_id', 'latitude', 'longitude', 'created_at', 'updated_at'])
                    ->get()
                    ->keyBy('training_session_id');
            }
        }

        $sessions = $rawSessions->map(function ($session) use ($now, $latestLogsBySession) {
            $latestLog = $latestLogsBySession->get($session->id);
            $activityStatus = $this->resolveActivityStatus($session, $latestLog);
            $session->is_desert_mode = $activityStatus['is_desert_mode'];
            $session->is_weak_connection = $activityStatus['is_weak_connection'];

            if ($latestLog) {
                if (!empty($latestLog->latitude)) {
                    $session->latitude = (string)$latestLog->latitude;
                }
                if (!empty($latestLog->longitude)) {
                    $session->longitude = (string)$latestLog->longitude;
                }
            }

            $session->round_time = $this->resolveLiveRoundTime($session);
            $session->start_date_time = $session->created_at ? $session->created_at->format('Y-m-d H:i:s') : null;
            $session->end_date_time = $session->session_ended_at ? $session->session_ended_at->format('Y-m-d H:i:s') : null;
            $session->started_ago = $session->created_at ? $session->created_at->diffForHumans($now) : null;
            if ($session->session_ended_at) {
                $session->ended_ago = $session->session_ended_at->diffForHumans($now);
            } else {
                if ($session->round_status === 'end') {
                    $session->ended_ago = 'منتهية';
                } elseif ($session->round_status === 'stop') {
                    $session->ended_ago = 'متوقفة مؤقتاً';
                } elseif ($session->is_desert_mode) {
                    $session->ended_ago = 'تدريب صحراوي';
                } else {
                    $session->ended_ago = 'نشطة حالياً';
                }
            }
            return $session;
        });

        return response()->json([
            'success' => true,
            'sessions' => $sessions
        ], 200);
    }

    /**
     * API to fetch statistics and current active session details for an owner's dashboard.
     */
    public function getOwnerDashboardStatsApi(Request $request)
    {
        $request->validate([
            'owner_id' => 'required|integer|min:1',
        ], [
            'owner_id.required' => 'حقل المالك مطلوب.',
            'owner_id.integer'  => 'المالك غير موجود.',
            'owner_id.min'      => 'المالك غير موجود.',
        ]);

        // Single lookup validates existence and role together
        $owner = User::select('id', 'role')->find($request->owner_id);
        if (!$owner) {
            return response()->json([
                'success' => false,
                'message' => 'المالك غير موجود.',
                'errors'  => ['owner_id' => ['المالك غير موجود.']],
            ], 422);
        }
        if ($owner->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'معرّف المالك المقدم لا ينتمي لمالك صالح.'
            ], 403);
        }

        $ownerId = (int)$request->owner_id;

        // Auto sync worker online statuses (throttled & non-blocking broadcasts)
        CamelWorker::syncOnlineStatuses($ownerId);

        // Get all worker IDs for this owner (count derived without extra COUNT(*) query)
        $workerIds = CamelWorker::where('owner_id', $ownerId)->pluck('id');
        $workersCount = $workerIds->count();

        if ($workerIds->isEmpty()) {
            return response()->json([
                'success' => true,
                'active_sessions_count' => 0,
                'completed_sessions_count' => 0,
                'workers_count' => 0,
                'active_session' => null,
                'active_sessions' => [],
            ], 200);
        }

        // 2. Details of all active/paused sessions (count derived directly)
        $activeSessions = TrainingSession::with(['worker'])
            ->whereIn('camel_worker_id', $workerIds)
            ->whereIn('round_status', ['working', 'pending', 'stop'])
            ->latest()
            ->get();

        $activeSessionsCount = $activeSessions->count();

        // 3. Completed sessions count (status: end)
        $completedSessionsCount = TrainingSession::whereIn('camel_worker_id', $workerIds)
            ->where('round_status', 'end')
            ->count();

        // 4. Fallback to the absolute latest session only if no active sessions exist and at least one ended exists
        if ($activeSessions->isEmpty() && $completedSessionsCount > 0) {
            $latestSession = TrainingSession::with(['worker'])
                ->whereIn('camel_worker_id', $workerIds)
                ->latest()
                ->first();
            if ($latestSession) {
                $activeSessions = collect([$latestSession]);
            }
        }

        // Batch-load the latest SessionSpeedLog per session in one query instead of N+1
        $sessionIds = $activeSessions->pluck('id')->filter()->values()->all();
        $latestLogsBySession = collect();
        if (!empty($sessionIds)) {
            $latestLogIds = \App\Models\SessionSpeedLog::whereIn('training_session_id', $sessionIds)
                ->selectRaw('MAX(id) as max_id')
                ->groupBy('training_session_id')
                ->pluck('max_id');
            if ($latestLogIds->isNotEmpty()) {
                $latestLogsBySession = \App\Models\SessionSpeedLog::whereIn('id', $latestLogIds)
                    ->get()
                    ->keyBy('training_session_id');
            }
        }

        \Carbon\Carbon::setLocale('ar');
        $now = \Carbon\Carbon::now();

        $activeSessionsData = [];
        foreach ($activeSessions as $session) {
            $latestLog = $latestLogsBySession->get($session->id);
            $activityStatus = $this->resolveActivityStatus($session, $latestLog);
            $isDesertMode = $activityStatus['is_desert_mode'];
            $isWeakConnection = $activityStatus['is_weak_connection'];
            $session->is_desert_mode = $isDesertMode;
            $session->is_weak_connection = $isWeakConnection;

            $liveRoundTime = $this->resolveLiveRoundTime($session);

            $activeSessionsData[] = [
                'id' => $session->id,
                'camel_worker_id' => $session->camel_worker_id,
                'location_name' => $session->location_name,
                'latitude' => ($latestLog && $latestLog->latitude) ? (string)$latestLog->latitude : (string)$session->latitude,
                'longitude' => ($latestLog && $latestLog->longitude) ? (string)$latestLog->longitude : (string)$session->longitude,
                'round_status' => $session->round_status,
                'speed' => $session->speed,
                'average_speed' => $session->average_speed,
                'round_distance_km' => $session->round_distance_km,
                'round_time' => $liveRoundTime, // Live synchronized round time
                'performance' => $session->performance,
                'session_ended_at' => $session->session_ended_at,
                'created_at' => $session->created_at ? $session->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $session->updated_at ? $session->updated_at->format('Y-m-d H:i:s') : null,
                'start_date_time' => $session->created_at ? $session->created_at->format('Y-m-d H:i:s') : null,
                'started_ago' => $session->created_at ? $session->created_at->diffForHumans($now) : null,
                'elapsed_time' => $liveRoundTime, // Formatted training time of the session
                'worker' => $session->worker,
                'is_desert_mode' => $isDesertMode,
                'is_weak_connection' => $isWeakConnection,
            ];
        }

        return response()->json([
            'success' => true,
            'active_sessions_count' => $activeSessionsCount,
            'completed_sessions_count' => $completedSessionsCount,
            'workers_count' => $workersCount,
            'active_session' => count($activeSessionsData) > 0 ? $activeSessionsData[0] : null,
            'active_sessions' => $activeSessionsData,
        ], 200);
    }

    /**
     * API to fetch training sessions belonging to a specific worker, with status filtering.
     */
    public function getSessionsByWorkerApi(Request $request)
    {
        $request->validate([
            'worker_id' => 'required|exists:camel_workers,id',
            'status'    => 'nullable|string|in:active,working,paused,stop,ended,end,all',
            'limit'     => 'nullable|integer|min:0',
        ], [
            'worker_id.required' => 'معرف العامل مطلوب.',
            'worker_id.exists'   => 'العامل غير موجود.',
            'status.in'          => 'حالة التصفية غير صالحة.',
            'limit.integer'      => 'يجب أن يكون الحد رقمًا صحيحًا.',
            'limit.min'          => 'يجب أن يكون الحد 0 أو أكثر.',
        ]);

        $query = TrainingSession::with(['worker']);

        $query->where('camel_worker_id', $request->worker_id);

        // Apply status filtering (skip if status is 'all')
        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status;
            if ($status === 'active' || $status === 'working') {
                $query->whereIn('round_status', ['working', 'pending']);
            } elseif ($status === 'paused' || $status === 'stop') {
                $query->where('round_status', 'stop');
            } elseif ($status === 'ended' || $status === 'end') {
                $query->where('round_status', 'end');
            }
        }

        if ($request->filled('limit') && (int)$request->limit > 0) {
            $query->limit((int)$request->limit);
        }

        \Carbon\Carbon::setLocale('ar');
        $now = \Carbon\Carbon::now();

        $sessions = $query->latest()->get()->map(function ($session) use ($now) {
            $session->round_time = $this->resolveLiveRoundTime($session);
            $session->start_date_time = $session->created_at ? $session->created_at->format('Y-m-d H:i:s') : null;
            $session->end_date_time = $session->session_ended_at ? $session->session_ended_at->format('Y-m-d H:i:s') : null;
            $session->started_ago = $session->created_at ? $session->created_at->diffForHumans($now) : null;
            if ($session->session_ended_at) {
                $session->ended_ago = $session->session_ended_at->diffForHumans($now);
            } else {
                $session->ended_ago = ($session->round_status === 'end') ? 'منتهية' : 'نشطة حالياً';
            }
            return $session;
        });

        return response()->json([
            'success' => true,
            'sessions' => $sessions
        ], 200);
    }

    /**
     * API to fetch full training session details (worker, owner, speedLogs, chats, instructions).
     */
    public function getSessionDetailsApi(Request $request)
    {
        $id = $request->input('session_id') ?? $request->input('id');

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'معرف الجلسة مطلوب.'
            ], 422);
        }

        $recentOnly = $request->boolean('recent_only') || $request->input('recent_only') == '1';
        $lightweight = $request->boolean('lightweight') || $request->input('lightweight') == '1';
        $sinceId = $request->input('since_id') ? (int)$request->input('since_id') : null;

        // Load session + non-log relations first; fetch speed_logs via lean DB query (10x faster than Eloquent hydration)
        $relations = [$lightweight ? 'worker' : 'worker.owner'];
        if (!$lightweight) {
            $relations['chats'] = function ($q) {
                $q->orderBy('created_at', 'asc');
            };
            $relations['instructions'] = function ($q) {
                $q->orderBy('created_at', 'asc');
            };
        }

        $session = TrainingSession::with($relations)->find($id);

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'الجلسة التدريبية غير موجودة.'
            ], 404);
        }

        $isEnded = ($session->round_status === 'end');
        $cacheKey = "session_compact_route_v3_{$session->id}";
        $formattedLogs = null;
        $latestLog = null;

        if ($lightweight && $sinceId === null && !$recentOnly && $isEnded) {
            $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
            if (is_array($cached) && !empty($cached)) {
                $formattedLogs = $cached;
            }
        }

        if ($formattedLogs === null) {
            $cols = $lightweight
                ? ['id', 'training_session_id', 'speed', 'latitude', 'longitude', 'created_at']
                : ['id', 'training_session_id', 'speed', 'latitude', 'longitude', 'location_name', 'created_at', 'updated_at'];

            $logsQuery = \Illuminate\Support\Facades\DB::table('session_speed_logs')
                ->where('training_session_id', $session->id)
                ->select($cols);

            if ($sinceId !== null) {
                $rawRows = $logsQuery->where('id', '>', $sinceId)->orderBy('id', 'asc')->get()->all();
                $formattedLogs = $this->formatLeanSpeedLogs($rawRows, $lightweight);
            } elseif ($recentOnly) {
                $rawRows = array_reverse($logsQuery->orderBy('id', 'desc')->limit(25)->get()->all());
                $formattedLogs = $this->formatLeanSpeedLogs($rawRows, $lightweight);
            } else {
                // Pure index range scan by id ASC (zero MySQL filesort!), then strict chronological sort + clean + compact in PHP
                $rawRows = $logsQuery->orderBy('id', 'asc')->get()->all();
                $formattedLogs = $this->cleanAndCompactSpeedLogs($rawRows, $isEnded, $lightweight);
                if ($lightweight && $isEnded && count($formattedLogs) >= 2) {
                    \Illuminate\Support\Facades\Cache::put($cacheKey, $formattedLogs, 3600);
                }
            }
        }

        if (!empty($formattedLogs)) {
            $lastArr = end($formattedLogs);
            $latestLog = (object)[
                'id' => $lastArr['id'] ?? null,
                'latitude' => $lastArr['latitude'] ?? null,
                'longitude' => $lastArr['longitude'] ?? null,
                'created_at' => $lastArr['created_at'] ?? null,
                'updated_at' => $lastArr['updated_at'] ?? null,
            ];
        } elseif ($sinceId !== null) {
            $latestLog = \Illuminate\Support\Facades\DB::table('session_speed_logs')
                ->where('training_session_id', $session->id)
                ->select(['id', 'training_session_id', 'latitude', 'longitude', 'created_at', 'updated_at'])
                ->orderBy('id', 'desc')
                ->first();
        }

        $activityStatus = $this->resolveActivityStatus($session, $latestLog);
        $isDesertMode = $activityStatus['is_desert_mode'];
        $isWeakConnection = $activityStatus['is_weak_connection'];
        $session->is_desert_mode = $isDesertMode;
        $session->is_weak_connection = $isWeakConnection;

        if ($latestLog) {
            if (!empty($latestLog->latitude)) {
                $session->latitude = (string)$latestLog->latitude;
            }
            if (!empty($latestLog->longitude)) {
                $session->longitude = (string)$latestLog->longitude;
            }
        }

        // ملاحظة هامة: لا نقوم بتغيير round_status في قاعدة البيانات قسرياً إلى 'stop' عند تأخر البيانات لثوانٍ معدودة!
        // الجلسة تظل working مع تفعيل is_desert_mode إذا انقطع النت طويلاً أو is_weak_connection إذا كان النت بطيئاً.

        $session->round_time = $this->resolveLiveRoundTime($session);

        \Carbon\Carbon::setLocale('ar');
        $now = \Carbon\Carbon::now();

        $session->start_date_time = $session->created_at ? $session->created_at->format('Y-m-d H:i:s') : null;
        $session->end_date_time = $session->session_ended_at ? $session->session_ended_at->format('Y-m-d H:i:s') : null;
        $session->started_ago = $session->created_at ? $session->created_at->diffForHumans($now) : null;
        if ($session->session_ended_at) {
            $session->ended_ago = $session->session_ended_at->diffForHumans($now);
        } else {
            if ($session->round_status === 'end') {
                $session->ended_ago = 'منتهية';
            } elseif ($session->round_status === 'stop') {
                $session->ended_ago = 'متوقفة مؤقتاً';
            } elseif ($session->is_desert_mode) {
                $session->ended_ago = 'تدريب صحراوي';
            } else {
                $session->ended_ago = 'نشطة حالياً';
            }
        }

        $sessionPayload = $session->toArray();
        $sessionPayload['speed_logs'] = $formattedLogs;

        return response()->json([
            'success' => true,
            'session' => $sessionPayload,
            'is_desert_mode' => (bool)$session->is_desert_mode,
            'is_weak_connection' => (bool)$session->is_weak_connection,
        ], 200);
    }

    /**
     * Format lean DB rows into JSON-ready arrays matching SessionSpeedLog schema.
     */
    private function formatLeanSpeedLogs(array $rows, bool $lightweight): array
    {
        $out = [];
        foreach ($rows as $r) {
            $lat = isset($r->latitude) ? (float)$r->latitude : 0.0;
            $lng = isset($r->longitude) ? (float)$r->longitude : 0.0;
            if ($lat == 0.0 && $lng == 0.0) {
                continue;
            }
            $item = [
                'id' => (int)$r->id,
                'training_session_id' => (int)$r->training_session_id,
                'speed' => isset($r->speed) ? (float)$r->speed : 0.0,
                'latitude' => $lat,
                'longitude' => $lng,
                'created_at' => $r->created_at ? (string)$r->created_at : null,
            ];
            if (!$lightweight) {
                $item['location_name'] = $r->location_name ?? null;
                $item['updated_at'] = isset($r->updated_at) && $r->updated_at ? (string)$r->updated_at : null;
            }
            $out[] = $item;
        }
        return $out;
    }

    /**
     * Strictly sort, deduplicate, untangle clock-skew/retry spiderweb chords, and compact
     * long routes using angle-preserving Ramer-Douglas-Peucker so owner map loads in <1s.
     */
    private function cleanAndCompactSpeedLogs(array $rows, bool $isEnded, bool $lightweight): array
    {
        if (empty($rows)) {
            return [];
        }

        // 1. Extract valid coordinates & timestamps
        $items = [];
        foreach ($rows as $r) {
            $lat = isset($r->latitude) ? (float)$r->latitude : 0.0;
            $lng = isset($r->longitude) ? (float)$r->longitude : 0.0;
            if ($lat == 0.0 && $lng == 0.0) {
                continue;
            }
            $ts = 0;
            if (!empty($r->created_at)) {
                $parsedTs = strtotime((string)$r->created_at);
                if ($parsedTs !== false) {
                    $ts = $parsedTs;
                }
            }
            $entry = [
                '_ts' => $ts,
                'id' => (int)$r->id,
                'training_session_id' => (int)$r->training_session_id,
                'speed' => isset($r->speed) ? (float)$r->speed : 0.0,
                'latitude' => $lat,
                'longitude' => $lng,
                'created_at' => $r->created_at ? (string)$r->created_at : null,
            ];
            if (!$lightweight) {
                $entry['location_name'] = $r->location_name ?? null;
                $entry['updated_at'] = isset($r->updated_at) && $r->updated_at ? (string)$r->updated_at : null;
            }
            $items[] = $entry;
        }

        if (count($items) <= 2) {
            return array_map(function ($it) {
                unset($it['_ts']);
                return $it;
            }, $items);
        }

        // 2. Strict total chronological order: (timestamp ASC, id ASC)
        usort($items, function ($a, $b) {
            if ($a['_ts'] !== $b['_ts'] && $a['_ts'] > 0 && $b['_ts'] > 0) {
                return $a['_ts'] <=> $b['_ts'];
            }
            return $a['id'] <=> $b['id'];
        });

        // 3. Drop consecutive sub-1.0m stationary/duplicate jitter (always keep first & last)
        $dedup = [$items[0]];
        $total = count($items);
        for ($i = 1; $i < $total - 1; $i++) {
            $prev = end($dedup);
            $curr = $items[$i];
            if ($this->distanceMetersFast($prev['latitude'], $prev['longitude'], $curr['latitude'], $curr['longitude']) >= 1.0) {
                $dedup[] = $curr;
            }
        }
        $lastItem = $items[$total - 1];
        $tailPrev = end($dedup);
        if ($this->distanceMetersFast($tailPrev['latitude'], $tailPrev['longitude'], $lastItem['latitude'], $lastItem['longitude']) >= 0.25) {
            $dedup[] = $lastItem;
        } else {
            $dedup[count($dedup) - 1] = $lastItem;
        }

        // 4. Untangle clock-skew / retry-batch interleaved backtracks & urban multipath spikes
        $cleaned = $this->untangleSpeedLogItems($dedup);

        // 5. Compact long routes on lightweight fetches via Ramer-Douglas-Peucker (0.65m lane-level accuracy)
        if ($lightweight && count($cleaned) > 500) {
            $keepTail = $isEnded ? 1 : 40;
            $prefixCount = max(2, count($cleaned) - $keepTail);
            $prefix = array_slice($cleaned, 0, $prefixCount);
            $simplified = $this->rdpSimplifyLogs($prefix, 0.65);
            if ($prefixCount < count($cleaned)) {
                $tailSlice = array_slice($cleaned, $prefixCount);
                $lastKept = end($simplified);
                foreach ($tailSlice as $tp) {
                    if ($tp['id'] !== $lastKept['id']) {
                        $simplified[] = $tp;
                        $lastKept = $tp;
                    }
                }
            }
            $cleaned = $simplified;
        }

        return array_map(function ($it) {
            unset($it['_ts']);
            return $it;
        }, $cleaned);
    }

    /**
     * Remove single-point lateral spikes and interleaved back-and-forth chords (up to 4-way interleaving)
     * without flattening real 90-degree turns or genuine 180-degree U-turns.
     */
    private function untangleSpeedLogItems(array $pts): array
    {
        $n = count($pts);
        if ($n < 3) {
            return $pts;
        }

        $cleaned = [$pts[0]];
        $i = 1;
        while ($i < $n - 1) {
            $prev = end($cleaned);
            $curr = $pts[$i];
            $dPrevCurr = $this->distanceMetersFast($prev['latitude'], $prev['longitude'], $curr['latitude'], $curr['longitude']);

            $hasPrevPrev = count($cleaned) >= 2;
            $prevPrev = $hasPrevPrev ? $cleaned[count($cleaned) - 2] : null;
            $isSharpReversal = false;
            $bIn = 0.0;
            if ($hasPrevPrev && $dPrevCurr >= 4.0) {
                $dIn = $this->distanceMetersFast($prevPrev['latitude'], $prevPrev['longitude'], $prev['latitude'], $prev['longitude']);
                if ($dIn >= 1.5) {
                    $bIn = $this->bearingDegFast($prevPrev['latitude'], $prevPrev['longitude'], $prev['latitude'], $prev['longitude']);
                    $bCurr = $this->bearingDegFast($prev['latitude'], $prev['longitude'], $curr['latitude'], $curr['longitude']);
                    if (abs($this->angleDiffDegFast($bIn, $bCurr)) >= 115.0) {
                        $isSharpReversal = true;
                    }
                }
            }

            // Look ahead 1..4 vertices to detect interleaved backtrack chords or lateral spikes
            $skipTo = -1;
            $maxLookahead = min($n - 1, $i + 4);
            for ($k = $i + 1; $k <= $maxLookahead; $k++) {
                $cand = $pts[$k];
                $dPrevCand = $this->distanceMetersFast($prev['latitude'], $prev['longitude'], $cand['latitude'], $cand['longitude']);
                $dCurrCand = $this->distanceMetersFast($curr['latitude'], $curr['longitude'], $cand['latitude'], $cand['longitude']);

                if ($dPrevCand > 95.0) {
                    continue;
                }

                // A: Interleaved backtrack chord (reverses >= 115 deg then resumes forward along the curve)
                if ($isSharpReversal &&
                    $dPrevCurr >= 4.5 &&
                    ($dPrevCurr + $dCurrCand) >= 10.0 &&
                    $dPrevCand < ($dPrevCurr + $dCurrCand) * 0.48 &&
                    $dPrevCand <= max(24.0, $dPrevCurr * 0.72)) {
                    $bResume = $this->bearingDegFast($prev['latitude'], $prev['longitude'], $cand['latitude'], $cand['longitude']);
                    if ($dPrevCand < 2.0 || abs($this->angleDiffDegFast($bIn, $bResume)) < 105.0) {
                        $skipTo = $k;
                        break;
                    }
                }

                // B: Classic single-point lateral rooftop multipath spike (k == i + 1)
                if ($k === $i + 1 &&
                    ($dPrevCurr + $dCurrCand) > 28.0 &&
                    $dPrevCurr >= 14.0 &&
                    $dCurrCand >= 14.0 &&
                    $dPrevCand < ($dPrevCurr + $dCurrCand) * 0.42) {
                    $latErr = $this->perpDistToSegMeters($curr['latitude'], $curr['longitude'], $prev['latitude'], $prev['longitude'], $cand['latitude'], $cand['longitude']);
                    if ($latErr >= 12.0) {
                        $skipTo = $k;
                        break;
                    }
                }
            }

            if ($skipTo > $i) {
                $i = $skipTo;
                continue;
            }

            $cleaned[] = $curr;
            $i++;
        }

        $cleaned[] = $pts[$n - 1];
        return $cleaned;
    }

    /**
     * Iterative Ramer-Douglas-Peucker simplification preserving every turn within $epsilonMeters.
     */
    private function rdpSimplifyLogs(array $pts, float $epsilonMeters): array
    {
        $n = count($pts);
        if ($n <= 2) {
            return $pts;
        }
        $keep = array_fill(0, $n, false);
        $keep[0] = true;
        $keep[$n - 1] = true;

        $stack = [[0, $n - 1]];
        while (!empty($stack)) {
            [$start, $end] = array_pop($stack);
            $maxDist = -1.0;
            $maxIdx = $start;

            $sLat = $pts[$start]['latitude'];
            $sLng = $pts[$start]['longitude'];
            $eLat = $pts[$end]['latitude'];
            $eLng = $pts[$end]['longitude'];

            for ($i = $start + 1; $i < $end; $i++) {
                $d = $this->perpDistToSegMeters($pts[$i]['latitude'], $pts[$i]['longitude'], $sLat, $sLng, $eLat, $eLng);
                if ($d > $maxDist) {
                    $maxDist = $d;
                    $maxIdx = $i;
                }
            }

            if ($maxDist > $epsilonMeters && $maxIdx > $start && $maxIdx < $end) {
                $keep[$maxIdx] = true;
                $stack[] = [$start, $maxIdx];
                $stack[] = [$maxIdx, $end];
            }
        }

        $out = [];
        for ($i = 0; $i < $n; $i++) {
            if ($keep[$i]) {
                $out[] = $pts[$i];
            }
        }
        return $out;
    }

    private function distanceMetersFast(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = ($lat2 - $lat1) * 111132.92;
        $dLng = ($lng2 - $lng1) * 111320.0 * cos(deg2rad(($lat1 + $lat2) * 0.5));
        return sqrt($dLat * $dLat + $dLng * $dLng);
    }

    private function bearingDegFast(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = ($lat2 - $lat1) * 111132.92;
        $dLng = ($lng2 - $lng1) * 111320.0 * cos(deg2rad(($lat1 + $lat2) * 0.5));
        return fmod(rad2deg(atan2($dLng, $dLat)) + 360.0, 360.0);
    }

    private function angleDiffDegFast(float $fromDeg, float $toDeg): float
    {
        $diff = fmod($toDeg - $fromDeg, 360.0);
        if ($diff < -180.0) {
            $diff += 360.0;
        }
        if ($diff > 180.0) {
            $diff -= 360.0;
        }
        return $diff;
    }

    private function perpDistToSegMeters(float $pLat, float $pLng, float $aLat, float $aLng, float $bLat, float $bLng): float
    {
        $mPerDegLng = max(1e-6, 111320.0 * cos(deg2rad($aLat)));
        $bx = ($bLng - $aLng) * $mPerDegLng;
        $by = ($bLat - $aLat) * 111132.92;
        $px = ($pLng - $aLng) * $mPerDegLng;
        $py = ($pLat - $aLat) * 111132.92;
        $abLenSq = $bx * $bx + $by * $by;
        if ($abLenSq < 1e-6) {
            return sqrt($px * $px + $py * $py);
        }
        $t = max(0.0, min(1.0, ($px * $bx + $py * $by) / $abLenSq));
        $dx = $px - $t * $bx;
        $dy = $py - $t * $by;
        return sqrt($dx * $dx + $dy * $dy);
    }

    /**
     * API to start a new training session for a worker.
     */
    public function startSessionApi(Request $request)
    {
        $request->validate([
            'camel_worker_id' => 'required|exists:camel_workers,id',
            'location_name'   => 'nullable|string|max:255',
            'latitude'        => 'nullable|numeric|between:-90,90',
            'longitude'       => 'nullable|numeric|between:-180,180',
            'round_status'    => 'nullable|in:pending,working,stop,end',
        ], [
            'camel_worker_id.required' => 'معرف العامل مطلوب.',
            'camel_worker_id.exists'   => 'العامل غير موجود.',
            'latitude.numeric'         => 'خط العرض يجب أن يكون رقماً.',
            'longitude.numeric'        => 'خط الطول يجب أن يكون رقماً.',
            'round_status.in'          => 'حالة الجلسة غير صالحة.',
        ]);

        $hasUuidCol = \Illuminate\Support\Facades\Schema::hasColumn('training_sessions', 'client_session_uuid');

        // Idempotency guard: if a retried start request arrives with the same client_session_uuid,
        // return the already-created session immediately instead of spawning a duplicate row.
        if ($hasUuidCol && $request->filled('client_session_uuid')) {
            $existingByUuid = TrainingSession::with('worker.owner')
                ->where('camel_worker_id', $request->camel_worker_id)
                ->where('client_session_uuid', $request->client_session_uuid)
                ->first();
            if ($existingByUuid) {
                \Carbon\Carbon::setLocale('ar');
                $existingByUuid->start_date_time = $existingByUuid->created_at ? $existingByUuid->created_at->format('Y-m-d H:i:s') : null;
                $existingByUuid->end_date_time = $existingByUuid->session_ended_at ? $existingByUuid->session_ended_at->format('Y-m-d H:i:s') : null;
                $existingByUuid->started_ago = $existingByUuid->created_at ? $existingByUuid->created_at->diffForHumans() : null;
                $existingByUuid->ended_ago = $existingByUuid->round_status === 'end' ? 'منتهية' : 'نشطة حالياً';
                return response()->json([
                    'success' => true,
                    'message' => 'تم بدء جلسة التتبع بنجاح.',
                    'session' => $existingByUuid,
                ], 200);
            }
        }

        // End any active/pending sessions for this worker to prevent overlaps
        TrainingSession::where('camel_worker_id', $request->camel_worker_id)
            ->whereIn('round_status', ['pending', 'working', 'stop'])
            ->update([
                'round_status' => 'end',
                'session_ended_at' => now()
            ]);

        // Update worker status
        CamelWorker::where('id', $request->camel_worker_id)->update([
            'is_online' => 1,
            'online_status' => 'online',
            'last_activity_at' => now(),
        ]);

        // Only treat coordinates as valid if non-zero (prevents seeding 0,0 / fake fallback into speed logs)
        $hasInitialCoords = $request->filled('latitude')
            && $request->filled('longitude')
            && ((float)$request->latitude != 0.0 || (float)$request->longitude != 0.0);

        // Create the new tracking session
        $sessionData = [
            'camel_worker_id' => $request->camel_worker_id,
            'location_name'   => $request->location_name,
            'latitude'        => $hasInitialCoords ? $request->latitude : null,
            'longitude'       => $hasInitialCoords ? $request->longitude : null,
            'round_status'    => $request->round_status ?? 'working',
            'speed'           => 0.00,
            'average_speed'   => 0.00,
            'round_distance_km'=> 0.00,
            'logs_count'      => $hasInitialCoords ? 1 : 0,
            'round_time'      => '00:00:00',
            'performance'     => 0.00,
        ];
        if ($hasUuidCol && $request->filled('client_session_uuid')) {
            $sessionData['client_session_uuid'] = $request->client_session_uuid;
        }
        $session = TrainingSession::create($sessionData);

        // Initialize exact distance cache & seed initial coordinate log so start-to-first-move distance is never lost
        \Illuminate\Support\Facades\Cache::put('session_exact_dist_' . $session->id, 0.0, 86400);
        if ($hasInitialCoords) {
            $initLat = (float)$request->latitude;
            $initLng = (float)$request->longitude;
            \Illuminate\Support\Facades\Cache::put('session_last_coord_' . $session->id, [
                'latitude' => $initLat,
                'longitude' => $initLng,
            ], 86400);
            \App\Models\SessionSpeedLog::create([
                'training_session_id' => $session->id,
                'latitude' => $initLat,
                'longitude' => $initLng,
                'speed' => 0.00,
                'location_name' => $request->location_name,
            ]);
        }

        // Load worker details and associated owner
        $session->load('worker.owner');
        $worker = $session->worker;
        $owner = $worker ? $worker->owner : null;

        // Broadcast the start event to the owner channel
        try {
            broadcast(new \App\Events\TrainingSessionCreated($session));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("WebSocket training session created broadcast failed: " . $e->getMessage());
        }

        // Send Push Notification and create In-App Notification for Owner
        if ($owner) {
            try {
                \Carbon\Carbon::setLocale('ar');
                $startTimeFormatted = now()->format('h:i A');
                $locText = !empty($session->location_name) ? " في {$session->location_name}" : "";
                $workerName = $worker ? $worker->full_name : 'العامل';
                $notifTitle = "بدء تدريب جديد 🏃‍♂️";
                $notifBody = "بدأ العامل {$workerName} التدريب الآن الساعة ({$startTimeFormatted}){$locText}.";

                // Save in-app notification in database
                \App\Models\NotificationForApp::create([
                    'user_id'   => $owner->id,
                    'title'     => $notifTitle,
                    'des'       => $notifBody,
                    'date'      => now()->format('Y-m-d'),
                    'user_view' => 'no',
                    'created_at'=> now(),
                ]);

                // Send FCM Push Notification to owner's device
                $deviceToken = $owner->token ?: ($owner->firebase_token ?? null);
                \Illuminate\Support\Facades\Log::info("Training start push: owner_id={$owner->id}, token=" . (!empty($deviceToken) ? substr($deviceToken, 0, 15) . "..." : "NONE"));
                if (!empty($deviceToken)) {
                    $firebaseService = new \App\Services\FirebaseNotificationService();
                    $unreadCount = \App\Models\NotificationForApp::where('user_id', $owner->id)
                        ->where('user_view', 'no')
                        ->count();

                    $pushRes = $firebaseService->sendNotification(
                        $deviceToken,
                        $notifTitle,
                        $notifBody,
                        (string)$session->id,
                        'training_started',
                        $unreadCount
                    );
                    \Illuminate\Support\Facades\Log::info("Training start FCM push result: " . json_encode($pushRes));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Owner notification for training start failed: " . $e->getMessage());
            }
        }

        // Append started_ago and ended_ago fields
        \Carbon\Carbon::setLocale('ar');
        $session->start_date_time = $session->created_at ? $session->created_at->format('Y-m-d H:i:s') : null;
        $session->end_date_time = $session->session_ended_at ? $session->session_ended_at->format('Y-m-d H:i:s') : null;
        $session->started_ago = $session->created_at ? $session->created_at->diffForHumans() : null;
        $session->ended_ago = 'نشطة حالياً';

        return response()->json([
            'success' => true,
            'message' => 'تم بدء جلسة التتبع بنجاح.',
            'session' => $session
        ], 201);
    }

    /**
     * API to end an active training session.
     */
    public function endSessionApi(Request $request)
    {
        $id = $request->input('session_id') ?? $request->input('id');

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'معرف الجلسة مطلوب.'
            ], 422);
        }

        $session = TrainingSession::find($id);

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'الجلسة التدريبية غير موجودة.'
            ], 404);
        }

        if ($session->round_status === 'end') {
            return response()->json([
                'success' => false,
                'message' => 'هذه الجلسة منتهية بالفعل.'
            ], 422);
        }

        $request->validate([
            'summary_text'      => 'nullable|string',
            'summary_audio'     => 'nullable', // file or string path
            'summary_image'     => 'nullable', // file or string path
            'average_speed'     => 'nullable|numeric|min:0',
            'round_distance_km' => 'nullable|numeric|min:0',
            'round_time'        => 'nullable|string|max:255',
            'performance'       => 'nullable|numeric|min:0',
        ], [
            'average_speed.numeric'     => 'متوسط السرعة يجب أن يكون رقماً.',
            'round_distance_km.numeric' => 'المسافة يجب أن تكون رقماً.',
            'performance.numeric'       => 'الأداء يجب أن يكون رقماً.',
        ]);

        // Process summary audio
        $audioPath = $session->summary_audio;
        if ($request->hasFile('summary_audio')) {
            // Delete old file if exists
            if ($session->summary_audio && file_exists(public_path($session->summary_audio))) {
                @unlink(public_path($session->summary_audio));
            }
            
            $file = $request->file('summary_audio');
            $filename = date('YmdHi') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/summaries/audio'), $filename);
            $audioPath = 'upload/summaries/audio/' . $filename;
        } elseif ($request->has('summary_audio')) {
            if ($request->filled('summary_audio') && is_string($request->summary_audio) && $request->summary_audio !== "") {
                $audioPath = $request->summary_audio;
            } else {
                if ($session->summary_audio && file_exists(public_path($session->summary_audio))) {
                    @unlink(public_path($session->summary_audio));
                }
                $audioPath = null;
            }
        }

        // Process summary image
        $imagePath = $session->summary_image;
        if ($request->hasFile('summary_image')) {
            // Delete old file if exists
            if ($session->summary_image && file_exists(public_path($session->summary_image))) {
                @unlink(public_path($session->summary_image));
            }
            
            $file = $request->file('summary_image');
            $filename = date('YmdHi') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/summaries/images'), $filename);
            $imagePath = 'upload/summaries/images/' . $filename;
        } elseif ($request->has('summary_image')) {
            if ($request->filled('summary_image') && is_string($request->summary_image) && $request->summary_image !== "") {
                $imagePath = $request->summary_image;
            } else {
                if ($session->summary_image && file_exists(public_path($session->summary_image))) {
                    @unlink(public_path($session->summary_image));
                }
                $imagePath = null;
            }
        }

        // Monotonic distance & duration protection on end
        $cachedDist = \Illuminate\Support\Facades\Cache::get('session_exact_dist_' . $id);
        $baseDist = max(
            $cachedDist !== null ? (float)$cachedDist : 0.0,
            (float)($session->round_distance_km ?? 0.0)
        );
        $finalDist = $request->has('round_distance_km') && is_numeric($request->round_distance_km)
            ? max($baseDist, (float)$request->round_distance_km)
            : $baseDist;
        \Illuminate\Support\Facades\Cache::put('session_exact_dist_' . $id, $finalDist, 86400);

        $existingTime = $session->round_time ?? '00:00:00';
        $finalTime = $existingTime;
        if ($request->filled('round_time')) {
            $incomingTime = (string)$request->round_time;
            if ($this->parseDurationSec($incomingTime) >= $this->parseDurationSec($existingTime)) {
                $finalTime = $incomingTime;
            }
        }

        // Update the session details and end it
        $resolvedAvgSpeed = $finalDist < 0.003
            ? 0.00
            : ($request->has('average_speed') ? (float)$request->average_speed : (float)($session->average_speed ?? 0.00));

        $session->update([
            'round_status'      => 'end',
            'speed'             => 0.00,
            'session_ended_at'  => now(),
            'summary_text'      => $request->has('summary_text') ? $request->summary_text : $session->summary_text,
            'summary_audio'     => $audioPath,
            'summary_image'     => $imagePath,
            'average_speed'     => round(min(85.0, max(0.0, $resolvedAvgSpeed)), 2),
            'round_distance_km' => round($finalDist, 2),
            'round_time'        => $finalTime,
            'performance'       => $request->has('performance') ? $request->performance : $session->performance,
        ]);
        \Illuminate\Support\Facades\Cache::forget("session_compact_route_v3_{$session->id}");

        // Clean up any other dangling active/paused sessions for this worker so no zombie paused session resurfaces
        TrainingSession::where('camel_worker_id', $session->camel_worker_id)
            ->where('id', '!=', $session->id)
            ->whereIn('round_status', ['pending', 'working', 'stop'])
            ->update([
                'round_status' => 'end',
                'speed' => 0.00,
                'session_ended_at' => now(),
            ]);

        // Broadcast the ended status update to the WebSocket channel
        try {
            $latestLog = \App\Models\SessionSpeedLog::where('training_session_id', $id)->orderBy('id', 'desc')->first();
            broadcast(new \App\Events\LocationUpdated($latestLog, null, $session));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("WebSocket end session broadcast failed: " . $e->getMessage());
        }

        $session->load('worker.owner');
        $worker = $session->worker;
        $owner = $worker ? $worker->owner : null;

        // Broadcast the end event to the owner channel
        try {
            broadcast(new \App\Events\TrainingSessionEnded($session));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("WebSocket training session ended broadcast failed: " . $e->getMessage());
        }

        // Send Push Notification and create In-App Notification for Owner upon training completion
        if ($owner) {
            try {
                \Carbon\Carbon::setLocale('ar');
                $endTimeFormatted = now()->format('h:i A');
                $workerName = $worker ? $worker->full_name : 'العامل';
                $distText = !empty($session->round_distance_km) && $session->round_distance_km > 0
                    ? " (المسافة: {$session->round_distance_km} كم)"
                    : "";
                $durationText = !empty($session->round_time) && $session->round_time !== '00:00:00'
                    ? " واستغرقت {$session->round_time}"
                    : "";

                $notifTitle = "انتهاء جلسة التدريب 🏁";
                $notifBody = "أنهى العامل {$workerName} جلسة التدريب الآن الساعة ({$endTimeFormatted}){$durationText}{$distText}.";

                // Save in-app notification in database
                \App\Models\NotificationForApp::create([
                    'user_id'   => $owner->id,
                    'title'     => $notifTitle,
                    'des'       => $notifBody,
                    'date'      => now()->format('Y-m-d'),
                    'user_view' => 'no',
                    'created_at'=> now(),
                ]);

                // Send FCM Push Notification to owner's device
                $deviceToken = $owner->token ?: ($owner->firebase_token ?? null);
                \Illuminate\Support\Facades\Log::info("Training end push: owner_id={$owner->id}, token=" . (!empty($deviceToken) ? substr($deviceToken, 0, 15) . "..." : "NONE"));
                if (!empty($deviceToken)) {
                    $firebaseService = new \App\Services\FirebaseNotificationService();
                    $unreadCount = \App\Models\NotificationForApp::where('user_id', $owner->id)
                        ->where('user_view', 'no')
                        ->count();

                    $pushRes = $firebaseService->sendNotification(
                        $deviceToken,
                        $notifTitle,
                        $notifBody,
                        (string)$session->id,
                        'training_ended',
                        $unreadCount
                    );
                    \Illuminate\Support\Facades\Log::info("Training end FCM push result: " . json_encode($pushRes));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Owner notification for training end failed: " . $e->getMessage());
            }
        }

        // Append formatted datetimes and relative times
        \Carbon\Carbon::setLocale('ar');
        $now = \Carbon\Carbon::now();
        $session->start_date_time = $session->created_at ? $session->created_at->format('Y-m-d H:i:s') : null;
        $session->end_date_time = $session->session_ended_at ? $session->session_ended_at->format('Y-m-d H:i:s') : null;
        $session->started_ago = $session->created_at ? $session->created_at->diffForHumans($now) : null;
        $session->ended_ago = $session->session_ended_at ? $session->session_ended_at->diffForHumans($now) : 'منتهية';

        return response()->json([
            'success' => true,
            'message' => 'تم إنهاء جلسة التتبع بنجاح وتسجيل الملخص.',
            'session' => $session
        ], 200);
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

    /**
     * Parse any incoming date/time string safely in the application timezone.
     */
    private function parseDateTimeSafe($rawTime, $fallback = null): \Carbon\Carbon
    {
        $fallbackCarbon = $fallback ? \Carbon\Carbon::parse($fallback) : now();
        if (empty($rawTime)) {
            return $fallbackCarbon;
        }
        try {
            return \Carbon\Carbon::parse($rawTime)->timezone(config('app.timezone'));
        } catch (\Exception $e) {
            return $fallbackCarbon;
        }
    }

    /**
     * Parse "HH:MM:SS" duration string into total seconds for safe max-value comparisons.
     */
    private function parseRoundTimeToSeconds(?string $roundTime): int
    {
        if (empty($roundTime)) {
            return 0;
        }
        $parts = explode(':', trim($roundTime));
        if (count($parts) === 3) {
            return ((int)$parts[0] * 3600) + ((int)$parts[1] * 60) + (int)$parts[2];
        }
        if (count($parts) === 2) {
            return ((int)$parts[0] * 60) + (int)$parts[1];
        }
        return 0;
    }

    /**
     * Resolve whether an active session is currently in Desert Mode or Weak Connection,
     * using server timestamps so client clock skew never triggers or suppresses Desert Mode falsely.
     */
    private function resolveActivityStatus(TrainingSession $session, $latestLog): array
    {
        $now = now();
        $candidates = [];

        if ($session->updated_at) {
            $candidates[] = \Carbon\Carbon::parse($session->updated_at);
        }
        if ($latestLog && $latestLog->updated_at) {
            $candidates[] = \Carbon\Carbon::parse($latestLog->updated_at);
        }
        // Include log created_at only when it isn't ahead of server time (avoids timezone skew locking status)
        if ($latestLog && $latestLog->created_at) {
            $logCreated = \Carbon\Carbon::parse($latestLog->created_at);
            if ($logCreated->lessThanOrEqualTo($now->copy()->addSeconds(60))) {
                $candidates[] = $logCreated;
            }
        }

        $lastActivity = null;
        foreach ($candidates as $candidate) {
            if ($lastActivity === null || $candidate->greaterThan($lastActivity)) {
                $lastActivity = $candidate;
            }
        }

        $diffSeconds = $lastActivity
            ? max(0, (int)$lastActivity->diffInSeconds($now, false))
            : 999;

        $worker = $session->worker;
        $isWorkerOffline = ($worker && ($worker->online_status === 'offline' || $worker->is_online == 0));

        // الوضع الصحراوي ينطبق فقط إذا كانت الجلسة نشطة (working) وانقطع الاتصال تماماً
        $isDesertMode = ($session->round_status === 'working') && ($diffSeconds >= 40 || ($isWorkerOffline && $diffSeconds >= 25));
        $isWeakConnection = ($session->round_status === 'working') && !$isDesertMode && ($diffSeconds >= 12);

        return [
            'is_desert_mode'     => $isDesertMode,
            'is_weak_connection' => $isWeakConnection,
            'diff_seconds'       => $diffSeconds,
        ];
    }

    /**
     * Normalize, chronologically sort, deduplicate against existing DB logs & intra-batch
     * duplicates (matching within +-1s & ~2.5m), and atomically insert new GPS logs.
     */
    private function prepareAndInsertUniqueSpeedLogs(TrainingSession $session, array $rawLogs): array
    {
        $now = now();
        $nowFormatted = $now->format('Y-m-d H:i:s');
        $normalized = [];
        $firstLat = null;
        $firstLng = null;

        foreach ($rawLogs as $idx => $log) {
            if (!is_array($log)) {
                continue;
            }
            $lat = isset($log['latitude']) ? (float)$log['latitude'] : null;
            $lng = isset($log['longitude']) ? (float)$log['longitude'] : null;
            if ($lat === null || $lng === null || ($lat == 0.0 && $lng == 0.0)) {
                continue;
            }

            if ($firstLat === null) {
                $firstLat = $lat;
                $firstLng = $lng;
            }

            $rawTime = !empty($log['recorded_at']) ? $log['recorded_at'] : ($log['created_at'] ?? null);
            $logTime = $this->parseDateTimeSafe($rawTime, $now);

            $normalized[] = [
                '_idx'          => $idx,
                '_ts'           => $logTime->timestamp,
                'latitude'      => $lat,
                'longitude'     => $lng,
                'speed'         => isset($log['speed']) ? (float)$log['speed'] : 0.00,
                'location_name' => $log['location_name'] ?? null,
                'created_at'    => $logTime->format('Y-m-d H:i:s'),
                'updated_at'    => $nowFormatted,
            ];
        }

        if (empty($normalized)) {
            return [
                'inserted_count'  => 0,
                'inserted_models' => collect(),
                'first_lat'       => $firstLat,
                'first_lng'       => $firstLng,
            ];
        }

        usort($normalized, function ($a, $b) {
            if ($a['_ts'] === $b['_ts']) {
                return $a['_idx'] <=> $b['_idx'];
            }
            return $a['_ts'] <=> $b['_ts'];
        });

        $existingLogs = \App\Models\SessionSpeedLog::where('training_session_id', $session->id)
            ->orderBy('id', 'asc')
            ->get(['id', 'latitude', 'longitude', 'created_at']);

        $prevMaxId = (int)($existingLogs->max('id') ?? 0);
        $coordBucketBySec = [];
        foreach ($existingLogs as $ex) {
            if (!$ex->created_at) {
                continue;
            }
            $exTs = \Carbon\Carbon::parse($ex->created_at)->timestamp;
            $coordBucketBySec[$exTs][] = [(float)$ex->latitude, (float)$ex->longitude];
        }

        $logsToInsert = [];
        $lastAcceptedLat = null;
        $lastAcceptedLng = null;
        $normCount = count($normalized);
        foreach ($normalized as $nIdx => $item) {
            $ts = $item['_ts'];
            $lat = $item['latitude'];
            $lng = $item['longitude'];
            $isLastItem = ($nIdx === $normCount - 1);
            $isDuplicate = false;

            // Widen time window to +-20s so phone-vs-server clock skew never duplicates online points!
            for ($t = $ts - 20; $t <= $ts + 20; $t++) {
                if (empty($coordBucketBySec[$t])) {
                    continue;
                }
                foreach ($coordBucketBySec[$t] as [$exLat, $exLng]) {
                    if (abs($lat - $exLat) < 0.000018 && abs($lng - $exLng) < 0.000018) {
                        $isDuplicate = true;
                        break 2;
                    }
                }
            }

            if ($isDuplicate) {
                continue;
            }

            // Skip consecutive sub-1.0m stationary clutter inside offline batches (keep first & last)
            if (!$isLastItem && $lastAcceptedLat !== null && $lastAcceptedLng !== null) {
                if ($this->distanceMetersFast($lastAcceptedLat, $lastAcceptedLng, $lat, $lng) < 1.0 && $item['speed'] < 0.5) {
                    continue;
                }
            }

            $coordBucketBySec[$ts][] = [$lat, $lng];
            $lastAcceptedLat = $lat;
            $lastAcceptedLng = $lng;
            $logsToInsert[] = [
                'training_session_id' => $session->id,
                'latitude'            => $lat,
                'longitude'           => $lng,
                'speed'               => $item['speed'],
                'location_name'       => $item['location_name'],
                'created_at'          => $item['created_at'],
                'updated_at'          => $item['updated_at'],
            ];
        }

        $insertedModels = collect();
        if (!empty($logsToInsert)) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($session, $logsToInsert) {
                foreach (array_chunk($logsToInsert, 250) as $chunk) {
                    \App\Models\SessionSpeedLog::insert($chunk);
                }
                $session->logs_count = \App\Models\SessionSpeedLog::where('training_session_id', $session->id)->count();
                $session->save();
            });
            \Illuminate\Support\Facades\Cache::forget("session_compact_route_v3_{$session->id}");

            $insertedModels = \App\Models\SessionSpeedLog::where('training_session_id', $session->id)
                ->where('id', '>', $prevMaxId)
                ->orderBy('id', 'asc')
                ->get();
        }

        return [
            'inserted_count'  => count($logsToInsert),
            'inserted_models' => $insertedModels,
            'first_lat'       => $firstLat,
            'first_lng'       => $firstLng,
        ];
    }

    /**
     * API to synchronize an offline training session recorded while disconnected from the internet.
     */
    public function syncOfflineSessionApi(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'camel_worker_id'     => 'required|exists:camel_workers,id',
            'client_session_uuid' => 'required|string',
            'session_id'          => 'nullable|integer',
            'location_name'       => 'nullable|string|max:255',
            'start_date_time'     => 'nullable|string',
            'end_date_time'       => 'nullable|string',
            'round_time'          => 'nullable|string|max:255',
            'round_distance_km'   => 'nullable|numeric|min:0',
            'speed'               => 'nullable|numeric|min:0',
            'average_speed'       => 'nullable|numeric|min:0',
            'round_status'        => 'nullable|string',
            'performance'         => 'nullable|numeric|min:0',
            'summary_text'        => 'nullable|string',
            'summary_audio'       => 'nullable',
            'summary_image'       => 'nullable',
            'speed_logs'          => 'nullable',
        ], [
            'camel_worker_id.required'     => 'معرف العامل مطلوب.',
            'camel_worker_id.exists'       => 'العامل غير موجود.',
            'client_session_uuid.required' => 'المعرف الفريد للجلسة مطلوب.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات المزامنة غير صالحة.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $worker = CamelWorker::with('owner')->find($request->camel_worker_id);
        if (!$worker) {
            return response()->json([
                'success' => false,
                'message' => 'العامل غير موجود.'
            ], 404);
        }

        $isWorkerActive = ($worker->status === 'active');
        $accountStatus = $isWorkerActive ? 'active' : 'inactive';

        // 1. Idempotency & Promotion Check: Look up existing session by explicit session_id or client_session_uuid
        $hasUuidCol = \Illuminate\Support\Facades\Schema::hasColumn('training_sessions', 'client_session_uuid');
        $existingSession = null;

        if ($request->filled('session_id') && (int)$request->session_id > 0) {
            $candidate = TrainingSession::find((int)$request->session_id);
            if ($candidate && (int)$candidate->camel_worker_id === (int)$request->camel_worker_id) {
                $existingSession = $candidate;
            }
        }

        if (!$existingSession && $hasUuidCol && $request->filled('client_session_uuid')) {
            $existingSession = TrainingSession::where('client_session_uuid', $request->client_session_uuid)->first();
        }

        // Fallback C: only match a dangling legacy session without conflicting UUID
        if (!$existingSession) {
            $existingSession = TrainingSession::where('camel_worker_id', $request->camel_worker_id)
                ->whereIn('round_status', ['working', 'pending', 'stop'])
                ->when($hasUuidCol, function ($q) use ($request) {
                    $q->where(function ($sub) use ($request) {
                        $sub->whereNull('client_session_uuid')
                            ->orWhere('client_session_uuid', $request->client_session_uuid);
                    });
                })
                ->latest()
                ->first();
        }

        // Process summary audio & image if attached
        $audioPath = null;
        if ($request->hasFile('summary_audio')) {
            $file = $request->file('summary_audio');
            $filename = date('YmdHi') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/summaries/audio'), $filename);
            $audioPath = 'upload/summaries/audio/' . $filename;
        } elseif ($request->filled('summary_audio') && is_string($request->summary_audio)) {
            $audioPath = $request->summary_audio;
        }

        $imagePath = null;
        if ($request->hasFile('summary_image')) {
            $file = $request->file('summary_image');
            $filename = date('YmdHi') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/summaries/images'), $filename);
            $imagePath = 'upload/summaries/images/' . $filename;
        } elseif ($request->filled('summary_image') && is_string($request->summary_image)) {
            $imagePath = $request->summary_image;
        }

        // Parse speed logs
        $rawLogs = $request->input('speed_logs', []);
        if (is_string($rawLogs)) {
            $decoded = json_decode($rawLogs, true);
            $rawLogs = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($rawLogs)) {
            $rawLogs = [];
        }

        // If session already marked ended on server, still merge any trailing unsynced GPS logs & missing summaries
        if ($existingSession && $existingSession->round_status === 'end') {
            $insertRes = $this->prepareAndInsertUniqueSpeedLogs($existingSession, $rawLogs);
            $changed = false;

            $incomingDist = $request->filled('round_distance_km') ? (float)$request->round_distance_km : 0.0;
            if ($incomingDist > (float)$existingSession->round_distance_km) {
                $existingSession->round_distance_km = $incomingDist;
                $changed = true;
            }
            if ($request->filled('round_time') &&
                $this->parseRoundTimeToSeconds($request->round_time) > $this->parseRoundTimeToSeconds($existingSession->round_time)) {
                $existingSession->round_time = $request->round_time;
                if ($request->filled('average_speed')) {
                    $existingSession->average_speed = (float)$request->average_speed;
                }
                $changed = true;
            }
            if (empty($existingSession->summary_text) && $request->filled('summary_text')) {
                $existingSession->summary_text = $request->summary_text;
                $changed = true;
            }
            if (empty($existingSession->summary_audio) && !empty($audioPath)) {
                $existingSession->summary_audio = $audioPath;
                $changed = true;
            }
            if (empty($existingSession->summary_image) && !empty($imagePath)) {
                $existingSession->summary_image = $imagePath;
                $changed = true;
            }
            if ($changed) {
                $existingSession->save();
            }

            // Ensure no other orphan active/paused sessions remain open for this worker after offline sync
            TrainingSession::where('camel_worker_id', $request->camel_worker_id)
                ->where('id', '!=', $existingSession->id)
                ->whereIn('round_status', ['pending', 'working', 'stop'])
                ->update([
                    'round_status' => 'end',
                    'speed' => 0.00,
                    'session_ended_at' => now(),
                ]);
            if ($changed || $insertRes['inserted_count'] > 0) {
                try {
                    broadcast(new \App\Events\TrainingSessionEnded($existingSession));
                } catch (\Exception $e) {}
            }

            \Carbon\Carbon::setLocale('ar');
            $existingSession->start_date_time = $existingSession->created_at ? $existingSession->created_at->format('Y-m-d H:i:s') : null;
            $existingSession->end_date_time = $existingSession->session_ended_at ? $existingSession->session_ended_at->format('Y-m-d H:i:s') : null;

            return response()->json([
                'success'             => true,
                'account_status'      => $accountStatus,
                'message'             => $isWorkerActive ? 'تمت مزامنة الجلسة مسبقاً.' : 'تم حفظ الجلسة، ولكن حسابك غير نشط.',
                'session'             => $existingSession,
                'already_synced'      => true,
                'newly_inserted_logs' => $insertRes['inserted_count'],
            ], 200);
        }

        // Resolve first coordinates safely from logs
        $firstLat = null;
        $firstLng = null;
        foreach ($rawLogs as $log) {
            if (is_array($log) && isset($log['latitude'], $log['longitude'])) {
                $lat = (float)$log['latitude'];
                $lng = (float)$log['longitude'];
                if ($lat != 0.0 || $lng != 0.0) {
                    $firstLat = $lat;
                    $firstLng = $lng;
                    break;
                }
            }
        }

        if ($existingSession) {
            $session = $existingSession;
            $session->round_status = $request->round_status ?? 'end';
            if (empty($session->location_name) && $request->filled('location_name')) {
                $session->location_name = $request->location_name;
            }
            if (empty($session->latitude) && ($firstLat ?? $request->latitude)) {
                $session->latitude = $firstLat ?? $request->latitude;
            }
            if (empty($session->longitude) && ($firstLng ?? $request->longitude)) {
                $session->longitude = $firstLng ?? $request->longitude;
            }
            $incomingDist = $request->filled('round_distance_km') ? (float)$request->round_distance_km : 0.0;
            $session->round_distance_km = max((float)($session->round_distance_km ?? 0), $incomingDist);
            if ($request->filled('round_time') &&
                $this->parseRoundTimeToSeconds($request->round_time) >= $this->parseRoundTimeToSeconds($session->round_time)) {
                $session->round_time = $request->round_time;
            }
            if ($session->round_distance_km < 0.003) {
                $session->average_speed = 0.00;
                $session->speed = 0.00;
            } else {
                if ($request->filled('average_speed')) {
                    $session->average_speed = min(85.0, max(0.0, (float)$request->average_speed));
                }
                if ($request->filled('speed')) {
                    $session->speed = min(85.0, max(0.0, (float)$request->speed));
                }
            }
            if ($request->filled('performance')) {
                $session->performance = (float)$request->performance;
            }
            if ($request->filled('summary_text')) {
                $session->summary_text = $request->summary_text;
            }
            if (!empty($audioPath)) {
                $session->summary_audio = $audioPath;
            }
            if (!empty($imagePath)) {
                $session->summary_image = $imagePath;
            }
            if ($hasUuidCol && empty($session->client_session_uuid)) {
                $session->client_session_uuid = $request->client_session_uuid;
            }
        } else {
            $syncDistKm = (float)($request->round_distance_km ?? 0.00);
            $sessionData = [
                'camel_worker_id'   => $request->camel_worker_id,
                'location_name'     => $request->location_name,
                'latitude'          => $firstLat ?? $request->latitude,
                'longitude'         => $firstLng ?? $request->longitude,
                'round_status'      => $request->round_status ?? 'end',
                'speed'             => $syncDistKm < 0.003 ? 0.00 : min(85.0, max(0.0, (float)($request->speed ?? 0.00))),
                'average_speed'     => $syncDistKm < 0.003 ? 0.00 : min(85.0, max(0.0, (float)($request->average_speed ?? 0.00))),
                'round_distance_km' => $syncDistKm,
                'round_time'        => $request->round_time ?? '00:00:00',
                'performance'       => $request->performance ?? 0.00,
                'summary_text'      => $request->summary_text,
                'summary_audio'     => $audioPath,
                'summary_image'     => $imagePath,
            ];
            if ($hasUuidCol) {
                $sessionData['client_session_uuid'] = $request->client_session_uuid;
            }
            $session = new TrainingSession($sessionData);
            if (!empty($request->start_date_time)) {
                $session->created_at = $this->parseDateTimeSafe($request->start_date_time, now());
            }
        }

        $session->session_ended_at = !empty($request->end_date_time)
            ? $this->parseDateTimeSafe($request->end_date_time, now())
            : now();

        $session->save();

        // Deduplicated atomic bulk insert of GPS speed logs
        $insertRes = $this->prepareAndInsertUniqueSpeedLogs($session, $rawLogs);

        // If worker is active, update online status/activity
        if ($isWorkerActive) {
            $worker->is_online = 1;
            $worker->online_status = 'online';
            $worker->last_activity_at = now();
            $worker->save();
        }

        // Clean up any remaining dangling active sessions for this worker so the owner never sees ghost active sessions
        TrainingSession::where('camel_worker_id', $request->camel_worker_id)
            ->where('id', '!=', $session->id)
            ->whereIn('round_status', ['pending', 'working', 'stop'])
            ->update([
                'round_status' => 'end',
                'session_ended_at' => now(),
            ]);

        // Broadcast to Owner Channel so dashboard updates
        try {
            broadcast(new \App\Events\TrainingSessionEnded($session));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Broadcast offline session sync failed: " . $e->getMessage());
        }

        // Notify Owner
        $owner = $worker->owner;
        if ($owner) {
            try {
                \Carbon\Carbon::setLocale('ar');
                $workerName = $worker->full_name ?? 'العامل';
                $dist = number_format((float)$session->round_distance_km, 2);
                $duration = $session->round_time ?? '00:00:00';
                $locText = !empty($session->location_name) ? " في {$session->location_name}" : "";
                $startTimeFormatted = $session->created_at ? $session->created_at->format('h:i A') : now()->format('h:i A');
                $endTimeFormatted = $session->session_ended_at ? $session->session_ended_at->format('h:i A') : now()->format('h:i A');

                $startTitle = "بدء تدريب جديد 🏃‍♂️";
                $startBody = "بدأ العامل {$workerName} التدريب الساعة ({$startTimeFormatted}){$locText}.";

                $endTitle = "انتهاء جلسة التدريب 🏁";
                $endBody = "أنهى العامل {$workerName} جلسة التدريب الساعة ({$endTimeFormatted}) واستغرقت {$duration} (المسافة: {$dist} كم).";

                if (!$existingSession) {
                    \App\Models\NotificationForApp::create([
                        'user_id'   => $owner->id,
                        'title'     => $startTitle,
                        'des'       => $startBody,
                        'date'      => $session->created_at ? $session->created_at->format('Y-m-d') : now()->format('Y-m-d'),
                        'user_view' => 'no',
                        'created_at'=> $session->created_at ?? now(),
                    ]);
                }

                \App\Models\NotificationForApp::create([
                    'user_id'   => $owner->id,
                    'title'     => $endTitle,
                    'des'       => $endBody,
                    'date'      => now()->format('Y-m-d'),
                    'user_view' => 'no',
                    'created_at'=> now(),
                ]);

                $deviceToken = $owner->token ?: ($owner->firebase_token ?? null);
                if (!empty($deviceToken)) {
                    $firebaseService = new \App\Services\FirebaseNotificationService();
                    $unreadCount = \App\Models\NotificationForApp::where('user_id', $owner->id)
                        ->where('user_view', 'no')
                        ->count();

                    if (!$existingSession) {
                        $firebaseService->sendNotification(
                            $deviceToken,
                            $startTitle,
                            $startBody,
                            (string)$session->id,
                            'training_started',
                            $unreadCount
                        );
                    }

                    $firebaseService->sendNotification(
                        $deviceToken,
                        $endTitle,
                        $endBody,
                        (string)$session->id,
                        'training_ended',
                        $unreadCount
                    );
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Owner notification for offline session sync failed: " . $e->getMessage());
            }
        }

        \Carbon\Carbon::setLocale('ar');
        $session->start_date_time = $session->created_at ? $session->created_at->format('Y-m-d H:i:s') : null;
        $session->end_date_time = $session->session_ended_at ? $session->session_ended_at->format('Y-m-d H:i:s') : null;
        $session->started_ago = $session->created_at ? $session->created_at->diffForHumans() : null;
        $session->ended_ago = $session->session_ended_at ? $session->session_ended_at->diffForHumans() : 'منتهية';

        $returnMessage = $isWorkerActive
            ? 'تمت مزامنة وحفظ جلسة التدريب المسجلة بدون إنترنت بنجاح.'
            : 'تم حفظ بيانات تدريب الهجن بنجاح، ولكن حسابك تم إيقافه من قبل المالك.';

        return response()->json([
            'success'             => true,
            'account_status'      => $accountStatus,
            'message'             => $returnMessage,
            'session'             => $session,
            'newly_inserted_logs' => $insertRes['inserted_count'],
        ], 201);
    }

    /**
     * API to promote/transition an active offline session (or recover an active online session
     * that went offline mid-training) to live tracking when internet connection is restored.
     */
    public function goOnlineSessionApi(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'camel_worker_id'     => 'required|exists:camel_workers,id',
            'client_session_uuid' => 'required|string',
            'session_id'          => 'nullable|integer',
            'location_name'       => 'nullable|string|max:255',
            'latitude'            => 'nullable|numeric|between:-90,90',
            'longitude'           => 'nullable|numeric|between:-180,180',
            'heading'             => 'nullable|numeric',
            'round_status'        => 'nullable|string|in:working,stop',
            'speed'               => 'nullable|numeric|min:0',
            'average_speed'       => 'nullable|numeric|min:0',
            'round_distance_km'   => 'nullable|numeric|min:0',
            'round_time'          => 'nullable|string|max:255',
            'start_date_time'     => 'nullable|string',
            'speed_logs'          => 'nullable',
        ], [
            'camel_worker_id.required'     => 'معرف العامل مطلوب.',
            'camel_worker_id.exists'       => 'العامل غير موجود.',
            'client_session_uuid.required' => 'المعرف الفريد للجلسة مطلوب.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات الانتقال للمباشر غير صالحة.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $worker = CamelWorker::with('owner')->find($request->camel_worker_id);
        if (!$worker) {
            return response()->json([
                'success' => false,
                'message' => 'العامل غير موجود.'
            ], 404);
        }

        $isWorkerActive = ($worker->status === 'active');
        $accountStatus = $isWorkerActive ? 'active' : 'inactive';

        if (!$isWorkerActive) {
            return response()->json([
                'success'        => false,
                'account_status' => 'inactive',
                'message'        => 'تم إيقاف حسابك من قبل المالك.',
            ], 403);
        }

        // Parse speed logs sent so far
        $rawLogs = $request->input('speed_logs', []);
        if (is_string($rawLogs)) {
            $decoded = json_decode($rawLogs, true);
            $rawLogs = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($rawLogs)) {
            $rawLogs = [];
        }

        $firstLat = null;
        $firstLng = null;
        foreach ($rawLogs as $log) {
            if (is_array($log) && isset($log['latitude'], $log['longitude'])) {
                $lat = (float)$log['latitude'];
                $lng = (float)$log['longitude'];
                if ($lat != 0.0 || $lng != 0.0) {
                    $firstLat = $lat;
                    $firstLng = $lng;
                    break;
                }
            }
        }

        $hasUuidCol = \Illuminate\Support\Facades\Schema::hasColumn('training_sessions', 'client_session_uuid');
        $session = null;

        if ($request->filled('session_id') && (int)$request->session_id > 0) {
            $candidate = TrainingSession::find((int)$request->session_id);
            if ($candidate && (int)$candidate->camel_worker_id === (int)$request->camel_worker_id) {
                $session = $candidate;
            }
        }
        if (!$session && $hasUuidCol) {
            $session = TrainingSession::where('client_session_uuid', $request->client_session_uuid)->first();
        }

        // Do not reopen a session that has already been legitimately ended; merge trailing logs and notify client
        if ($session && $session->round_status === 'end') {
            $insertRes = $this->prepareAndInsertUniqueSpeedLogs($session, $rawLogs);
            \Carbon\Carbon::setLocale('ar');
            $session->start_date_time = $session->created_at ? $session->created_at->format('Y-m-d H:i:s') : null;
            $session->end_date_time = $session->session_ended_at ? $session->session_ended_at->format('Y-m-d H:i:s') : null;

            return response()->json([
                'success'             => true,
                'account_status'      => $accountStatus,
                'already_ended'       => true,
                'message'             => 'الجلسة منتهية بالفعل، تم إلحاق أي نقاط متبقية.',
                'session'             => $session,
                'newly_inserted_logs' => $insertRes['inserted_count'],
            ], 200);
        }

        $isNewSession = false;

        if ($session) {
            // Session already registered on server, update current metrics safely (preserve max cumulative distance & time)
            $session->round_status = $request->round_status ?? 'working';
            if ($request->filled('latitude')) $session->latitude = $request->latitude;
            if ($request->filled('longitude')) $session->longitude = $request->longitude;
            if ($request->filled('speed')) $session->speed = $request->speed;
            if ($request->filled('average_speed')) $session->average_speed = $request->average_speed;
            if ($request->filled('round_distance_km')) {
                $session->round_distance_km = max((float)($session->round_distance_km ?? 0), (float)$request->round_distance_km);
            }
            if ($request->filled('round_time') &&
                $this->parseRoundTimeToSeconds($request->round_time) >= $this->parseRoundTimeToSeconds($session->round_time)) {
                $session->round_time = $request->round_time;
            }
            if ($request->filled('location_name')) $session->location_name = $request->location_name;
            if ($hasUuidCol && empty($session->client_session_uuid)) {
                $session->client_session_uuid = $request->client_session_uuid;
            }
            $session->session_ended_at = null;
            $session->touch();
            $session->save();
        } else {
            // End any older active/pending sessions for this worker
            TrainingSession::where('camel_worker_id', $request->camel_worker_id)
                ->whereIn('round_status', ['pending', 'working', 'stop'])
                ->update([
                    'round_status' => 'end',
                    'session_ended_at' => now()
                ]);

            $sessionData = [
                'camel_worker_id'   => $request->camel_worker_id,
                'location_name'     => $request->location_name ?? "تدريب الهجن",
                'latitude'          => $request->latitude ?? $firstLat,
                'longitude'         => $request->longitude ?? $firstLng,
                'round_status'      => $request->round_status ?? 'working',
                'speed'             => $request->speed ?? 0.00,
                'average_speed'     => $request->average_speed ?? 0.00,
                'round_distance_km' => $request->round_distance_km ?? 0.00,
                'round_time'        => $request->round_time ?? '00:00:00',
                'performance'       => 0.00,
                'session_ended_at'  => null,
            ];

            if ($hasUuidCol) {
                $sessionData['client_session_uuid'] = $request->client_session_uuid;
            }

            $session = new TrainingSession($sessionData);
            $session->created_at = !empty($request->start_date_time)
                ? $this->parseDateTimeSafe($request->start_date_time, now())
                : now();

            $session->save();
            $isNewSession = true;
        }

        // Deduplicated atomic bulk insert of missed GPS logs
        $insertRes = $this->prepareAndInsertUniqueSpeedLogs($session, $rawLogs);

        // Update worker online status
        $worker->is_online = 1;
        $worker->online_status = 'online';
        $worker->last_activity_at = now();
        $worker->save();

        // Sync exact distance & last coordinate cache when promoting/recovering session to online
        if ($request->filled('round_distance_km')) {
            \Illuminate\Support\Facades\Cache::put('session_exact_dist_' . $session->id, (float)$session->round_distance_km, 86400);
        }
        $latestLog = \App\Models\SessionSpeedLog::where('training_session_id', $session->id)->orderBy('id', 'desc')->first();
        $cacheLat = $request->filled('latitude') ? (float)$request->latitude : ($latestLog ? (float)$latestLog->latitude : null);
        $cacheLng = $request->filled('longitude') ? (float)$request->longitude : ($latestLog ? (float)$latestLog->longitude : null);
        if ($cacheLat !== null && $cacheLng !== null) {
            \Illuminate\Support\Facades\Cache::put('session_last_coord_' . $session->id, [
                'latitude' => $cacheLat,
                'longitude' => $cacheLng,
            ], 86400);
        }

        // Broadcast to Owner Channel & live tracking channel so dashboard & map recover instantaneously
        try {
            if ($isNewSession) {
                broadcast(new \App\Events\TrainingSessionCreated($session));
            }
            $recentLogs = $insertRes['inserted_models']->isNotEmpty()
                ? $insertRes['inserted_models']->slice(-30)->values()
                : \App\Models\SessionSpeedLog::where('training_session_id', $session->id)
                    ->orderBy('id', 'desc')
                    ->take(25)
                    ->get()
                    ->reverse()
                    ->values();
            $headingVal = $request->filled('heading') ? (float)$request->heading : null;
            broadcast(new \App\Events\LocationUpdated($latestLog, $headingVal, $session, $recentLogs));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("WebSocket broadcast in goOnlineSessionApi failed: " . $e->getMessage());
        }

        // Send Push Notification & In-App Notification to Owner if new session was just promoted
        $owner = $worker->owner;
        if ($owner && $isNewSession) {
            try {
                \Carbon\Carbon::setLocale('ar');
                $startTimeFormatted = $session->created_at ? $session->created_at->format('h:i A') : now()->format('h:i A');
                $locText = !empty($session->location_name) ? " في {$session->location_name}" : "";
                $workerName = $worker->full_name ?? 'العامل';
                $notifTitle = "بدء تدريب جديد 🏃‍♂️";
                $notifBody = "بدأ العامل {$workerName} التدريب الآن الساعة ({$startTimeFormatted}){$locText}.";

                \App\Models\NotificationForApp::create([
                    'user_id'   => $owner->id,
                    'title'     => $notifTitle,
                    'des'       => $notifBody,
                    'date'      => now()->format('Y-m-d'),
                    'user_view' => 'no',
                    'created_at'=> now(),
                ]);

                $deviceToken = $owner->token ?: ($owner->firebase_token ?? null);
                if (!empty($deviceToken)) {
                    $firebaseService = new \App\Services\FirebaseNotificationService();
                    $unreadCount = \App\Models\NotificationForApp::where('user_id', $owner->id)
                        ->where('user_view', 'no')
                        ->count();

                    $firebaseService->sendNotification(
                        $deviceToken,
                        $notifTitle,
                        $notifBody,
                        (string)$session->id,
                        'training_started',
                        $unreadCount
                    );
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Owner notification for online transition failed: " . $e->getMessage());
            }
        }

        \Carbon\Carbon::setLocale('ar');
        $session->start_date_time = $session->created_at ? $session->created_at->format('Y-m-d H:i:s') : null;
        $session->end_date_time = null;
        $session->started_ago = $session->created_at ? $session->created_at->diffForHumans() : null;
        $session->ended_ago = 'نشطة حالياً';

        return response()->json([
            'success'             => true,
            'account_status'      => $accountStatus,
            'message'             => 'تم تحويل الجلسة إلى البث المباشر بنجاح.',
            'session'             => $session,
            'newly_inserted_logs' => $insertRes['inserted_count'],
        ], 200);
    }

    public function updateStatusApi(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:training_sessions,id',
            'status' => 'required|in:pending,working,stop,end',
        ], [
            'session_id.required' => 'معرف الجلسة مطلوب.',
            'session_id.exists' => 'الجلسة غير موجودة.',
            'status.required' => 'الحالة مطلوبة.',
            'status.in' => 'الحالة غير صالحة.',
        ]);

        $session = TrainingSession::findOrFail($request->session_id);
        
        $oldStatus = $session->round_status;
        $newStatus = $request->status;

        $updateData = [
            'round_status' => $newStatus,
        ];

        $incomingTime = $request->filled('round_time')
            ? (string)$request->round_time
            : ($request->filled('duration') ? (string)$request->duration : null);
        if ($incomingTime !== null &&
            $this->parseDurationSec($incomingTime) >= $this->parseDurationSec($session->round_time)) {
            $updateData['round_time'] = $incomingTime;
        }

        if ($newStatus === 'stop' || $newStatus === 'end') {
            $updateData['speed'] = 0.00;
        }
        if ($newStatus === 'end') {
            $updateData['session_ended_at'] = now();
        }
        
        $session->update($updateData);
        $session->refresh();

        // Load worker relation for owner channel
        $session->load('worker');

        // Broadcast status change or location update if logs exist
        try {
            $latestLog = \App\Models\SessionSpeedLog::where('training_session_id', $session->id)->orderBy('id', 'desc')->first();
            broadcast(new \App\Events\LocationUpdated($latestLog, null, $session));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("WebSocket status update log broadcast failed: " . $e->getMessage());
        }

        // Broadcast TrainingSessionCreated or TrainingSessionEnded to owner if applicable
        try {
            if ($newStatus === 'working') {
                \Illuminate\Support\Facades\Cache::forget("worker_closed_notif_sent_{$session->id}");
                if ($session->worker) {
                    $session->worker->update([
                        'is_online' => 1,
                        'online_status' => 'online',
                        'last_activity_at' => now(),
                    ]);
                    try {
                        broadcast(new \App\Events\WorkerStatusUpdated($session->worker));
                    } catch (\Exception $e) {}
                }
                if ($oldStatus === 'stop') {
                    broadcast(new \App\Events\TrainingSessionCreated($session));
                }
            } else if ($newStatus === 'stop' && $oldStatus === 'working') {
                broadcast(new \App\Events\TrainingSessionEnded($session));
            } else if ($newStatus === 'end') {
                broadcast(new \App\Events\TrainingSessionEnded($session));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("WebSocket status change owner broadcast failed: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الجلسة بنجاح.',
            'round_status' => $session->round_status,
            'session' => $session
        ], 200);
    }

    /**
     * API to handle worker completely closing the app during training session.
     * Pauses the session (round_status = 'stop'), preserves metrics, logs final position, and notifies owner.
     */
    public function workerAppClosedApi(Request $request)
    {
        $workerId = $request->input('worker_id') ?? $request->input('camel_worker_id');
        $sessionId = $request->input('session_id') ?? $request->input('training_session_id');

        if (!$workerId && !$sessionId) {
            return response()->json([
                'success' => false,
                'message' => 'معرف العامل أو معرف الجلسة مطلوب.'
            ], 422);
        }

        $sessionQuery = TrainingSession::whereIn('round_status', ['working', 'pending']);
        if ($sessionId) {
            $sessionQuery->where('id', $sessionId);
        } elseif ($workerId) {
            $sessionQuery->where('camel_worker_id', $workerId);
        }
        $session = $sessionQuery->latest()->first();

        if ($session) {
            $updateData = [
                'round_status' => 'stop',
            ];
            if ($request->filled('elapsed_time') && $request->input('elapsed_time') !== '00:00:00') {
                $updateData['round_time'] = $request->input('elapsed_time');
            }
            if ($request->filled('distance_km')) {
                $updateData['round_distance_km'] = (double)$request->input('distance_km');
            }
            $session->update($updateData);

            // Log final location coordinate if provided
            if ($request->filled('latitude') && $request->filled('longitude')) {
                \App\Models\SessionSpeedLog::create([
                    'training_session_id' => $session->id,
                    'speed' => 0.00,
                    'latitude' => $request->input('latitude'),
                    'longitude' => $request->input('longitude'),
                    'location_name' => 'نقطة انقطاع التدريب (إغلاق التطبيق)',
                ]);
            }

            // Broadcast to Reverb WebSocket so owner live screen immediately reflects pause
            try {
                $latestLog = \App\Models\SessionSpeedLog::where('training_session_id', $session->id)->orderBy('id', 'desc')->first();
                broadcast(new \App\Events\LocationUpdated($latestLog, null, $session));
                broadcast(new \App\Events\TrainingSessionEnded($session));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("WebSocket broadcast worker app closed failed: " . $e->getMessage());
            }

            // Send notification to owner
            $this->notifyOwnerWorkerClosedApp($session);
        }

        // Set worker status to offline
        if ($workerId) {
            $worker = CamelWorker::find($workerId);
            if ($worker) {
                \Illuminate\Support\Facades\Cache::put("worker_explicit_leave_{$worker->id}", 'offline', 6);
                $worker->online_status = 'offline';
                $worker->is_online = 0;
                $worker->last_activity_at = null;
                $worker->save();
                try {
                    broadcast(new \App\Events\WorkerStatusUpdated($worker));
                } catch (\Exception $e) {}
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ نقطة الانقطاع وإشعار المالك بنجاح.',
            'session' => $session
        ], 200);
    }

    /**
     * Send notification to owner when worker closes app completely during active training.
     */
    public function notifyOwnerWorkerClosedApp($session, $worker = null)
    {
        if (!$session) return;

        // Prevent duplicate notifications within 3 minutes for this session
        $cacheKey = "worker_closed_notif_sent_{$session->id}";
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return;
        }
        \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addMinutes(3));

        if (!$worker) {
            $session->load('worker.owner');
            $worker = $session->worker;
        } elseif (!$worker->relationLoaded('owner')) {
            $worker->load('owner');
        }
        $owner = $worker ? $worker->owner : null;
        if (!$owner) return;

        try {
            \Carbon\Carbon::setLocale('ar');
            $timeFormatted = now()->format('h:i A');
            $workerName = $worker ? $worker->full_name : 'العامل';

            $notifTitle = "تنبيه: إغلاق التطبيق أثناء التدريب ⚠️";
            $notifBody = "قام العامل {$workerName} بإغلاق التطبيق نهائياً أثناء جلسة التدريب في تمام الساعة ({$timeFormatted}). تم حفظ نقطة الانقطاع لاستئناف التدريب لاحقاً.";

            // Save in-app notification in database
            \App\Models\NotificationForApp::create([
                'user_id'   => $owner->id,
                'title'     => $notifTitle,
                'des'       => $notifBody,
                'date'      => now()->format('Y-m-d'),
                'user_view' => 'no',
                'created_at'=> now(),
            ]);

            // Send FCM Push Notification to owner's device
            $deviceToken = $owner->token ?: ($owner->firebase_token ?? null);
            \Illuminate\Support\Facades\Log::info("Worker app closed push: owner_id={$owner->id}, token=" . (!empty($deviceToken) ? substr($deviceToken, 0, 15) . "..." : "NONE"));
            if (!empty($deviceToken)) {
                $firebaseService = new \App\Services\FirebaseNotificationService();
                $unreadCount = \App\Models\NotificationForApp::where('user_id', $owner->id)
                    ->where('user_view', 'no')
                    ->count();

                $pushRes = $firebaseService->sendNotification(
                    $deviceToken,
                    $notifTitle,
                    $notifBody,
                    (string)$session->id,
                    'worker_app_closed',
                    $unreadCount
                );
                \Illuminate\Support\Facades\Log::info("Worker app closed FCM push result: " . json_encode($pushRes));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Owner notification for worker app closed failed: " . $e->getMessage());
        }
    }

    /**
     * API to delete a training session.
     */
    public function deleteSessionApi(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:training_sessions,id',
            'owner_id'   => 'nullable|exists:users,id',
        ], [
            'session_id.required' => 'معرف الجلسة التدريبية مطلوب.',
            'session_id.exists'   => 'الجلسة التدريبية غير موجودة.',
            'owner_id.exists'     => 'المالك غير موجود.',
        ]);

        $session = TrainingSession::findOrFail($request->session_id);

        if ($request->filled('owner_id')) {
            $owner = User::find($request->owner_id);
            if ($owner && $owner->role === 'owner') {
                $workerIds = CamelWorker::where('owner_id', $owner->id)->pluck('id');
                if (!$workerIds->contains($session->camel_worker_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'غير مصرح لك بحذف هذه الجلسة التدريبية.'
                    ], 403);
                }
            }
        }

        // Delete associated files if any
        if ($session->summary_audio && file_exists(public_path($session->summary_audio))) {
            @unlink(public_path($session->summary_audio));
        }
        if ($session->summary_image && file_exists(public_path($session->summary_image))) {
            @unlink(public_path($session->summary_image));
        }

        // Delete child relations
        \App\Models\SessionSpeedLog::where('training_session_id', $session->id)->delete();
        \App\Models\SessionInstruction::where('training_session_id', $session->id)->delete();
        \App\Models\SessionChat::where('training_session_id', $session->id)->delete();

        // Clear route cache if exists
        \Illuminate\Support\Facades\Cache::forget("session_route_{$session->id}");
        \Illuminate\Support\Facades\Cache::forget("session_compact_route_v3_{$session->id}");

        // Delete the session record
        $session->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الجلسة التدريبية بنجاح'
        ], 200);
    }
}
