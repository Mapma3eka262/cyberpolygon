<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Analytics\SystemMetricsService;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    protected $metricsService;

    public function __construct(SystemMetricsService $metricsService)
    {
        $this->metricsService = $metricsService;
    }

    public function index()
    {
        // Собираем текущие метрики
        $metrics = $this->metricsService->collectMetrics();

        // Получаем историю за последние 24 часа
        $logs = SystemLog::where('created_at', '>', now()->subDay())
            ->orderBy('created_at')
            ->get()
            ->groupBy(function ($log) {
                return $log->created_at->format('H:00');
            })
            ->map(function ($group) {
                return [
                    'cpu_avg' => $group->avg('cpu_usage'),
                    'memory_avg' => $group->avg('memory_usage'),
                    'active_users_avg' => $group->avg('active_users'),
                ];
            });

        // Статистика попыток флагов
        $flagStats = Cache::remember('flag_stats_daily', 300, function () {
            return \App\Models\FlagAttempt::selectRaw('
                DATE(created_at) as date,
                flag_type,
                is_correct,
                COUNT(*) as count
            ')
            ->where('created_at', '>', now()->subWeek())
            ->groupBy('date', 'flag_type', 'is_correct')
            ->get()
            ->groupBy('date');
        });

        // Топ команд
        $topTeams = Cache::remember('top_teams_weekly', 600, function () {
            return \App\Models\Team::with('captain')
                ->where('is_active', true)
                ->orderBy('score', 'desc')
                ->limit(10)
                ->get();
        });

        return view('admin.analytics.index', compact(
            'metrics',
            'logs',
            'flagStats',
            'topTeams'
        ));
    }

    public function systemMetrics()
    {
        $metrics = $this->metricsService->collectMetrics();

        return response()->json([
            'success' => true,
            'data' => $metrics,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    public function userActivity()
    {
        $activities = Cache::remember('user_activity_recent', 60, function () {
            return \App\Models\EventLog::with('user', 'team')
                ->where('created_at', '>', now()->subHours(6))
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->groupBy(function ($log) {
                    return $log->created_at->format('Y-m-d H:00');
                });
        });

        return view('admin.analytics.activity', compact('activities'));
    }

    public function databaseStats()
    {
        $stats = [];

        // Размеры таблиц
        $tables = \DB::select("
            SELECT 
                table_name AS `table`,
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS `size_mb`,
                table_rows AS `rows`
            FROM information_schema.TABLES
            WHERE table_schema = DATABASE()
            ORDER BY (data_length + index_length) DESC
        ");

        // Медленные запросы (если включен slow query log)
        $slowQueries = [];
        if (config('database.slow_query_log', false)) {
            $slowQueries = \DB::table('slow_query_log')
                ->where('query_time', '>', 1) // Более 1 секунды
                ->orderBy('query_time', 'desc')
                ->limit(20)
                ->get();
        }

        // Активные соединения
        $connections = \DB::select('SHOW PROCESSLIST');

        return view('admin.analytics.database', compact('tables', 'slowQueries', 'connections'));
    }

    public function performance()
    {
        $responseTimes = Cache::remember('response_times', 300, function () {
            return \App\Models\EventLog::selectRaw('
                event_type,
                AVG(TIMESTAMPDIFF(MICROSECOND, created_at, updated_at)) as avg_time,
                COUNT(*) as count
            ')
            ->where('created_at', '>', now()->subDay())
            ->whereIn('event_type', ['flag_submitted', 'page_view', 'api_call'])
            ->groupBy('event_type')
            ->get();
        });

        $memoryUsage = SystemLog::selectRaw('
            HOUR(created_at) as hour,
            AVG(memory_usage) as avg_memory,
            MAX(memory_usage) as max_memory
        ')
        ->where('created_at', '>', now()->subWeek())
        ->groupBy('hour')
        ->orderBy('hour')
        ->get();

        return view('admin.analytics.performance', compact('responseTimes', 'memoryUsage'));
    }
}