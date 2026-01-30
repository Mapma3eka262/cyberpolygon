<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index()
    {
        $stats = $this->getLiveStats();
        
        return view('admin.stats.index', compact('stats'));
    }

    public function live()
    {
        $stats = $this->getLiveStats();
        
        return response()->json([
            'success' => true,
            'data' => $stats,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        $stats = $this->getDetailedStats();
        
        if ($format === 'json') {
            return response()->json($stats);
        }
        
        // CSV экспорт
        $csvData = $this->convertToCSV($stats);
        
        return response($csvData, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="ctf-stats-' . now()->format('Y-m-d-H-i') . '.csv"',
        ]);
    }

    private function getLiveStats()
    {
        return Cache::remember('live_stats', 10, function () {
            $teams = Team::with('activeTask')
                ->where('is_active', true)
                ->orderBy('score', 'desc')
                ->get()
                ->map(function ($team) {
                    $activeTask = $team->activeTask();
                    
                    return [
                        'id' => $team->id,
                        'name' => $team->name,
                        'score' => $team->score,
                        'flags_found' => $team->flags_found,
                        'wrong_attempts' => $team->wrong_attempts,
                        'active_task' => $activeTask ? [
                            'name' => $activeTask->name,
                            'progress' => [
                                'flag1' => $activeTask->pivot->flag1_found,
                                'flag2' => $activeTask->pivot->flag2_found,
                            ],
                            'wrong_attempts' => $activeTask->pivot->wrong_attempts,
                            'task_score' => $activeTask->pivot->score,
                        ] : null,
                        'members_count' => $team->members()->count(),
                    ];
                });

            $tasks = Task::where('is_active', true)
                ->withCount(['teams as completed_count' => function ($query) {
                    $query->whereNotNull('completed_at');
                }])
                ->get()
                ->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'name' => $task->name,
                        'assigned_teams' => $task->teams()->count(),
                        'completed_teams' => $task->completed_count,
                        'average_score' => round($task->teams()->avg('score') ?? 0, 2),
                    ];
                });

            $globalStats = [
                'total_teams' => Team::where('is_active', true)->count(),
                'active_teams' => Team::where('is_active', true)
                    ->whereHas('activeTask')
                    ->count(),
                'total_participants' => DB::table('users')
                    ->whereNotNull('team_id')
                    ->where('role', '!=', 'admin')
                    ->count(),
                'total_flag_attempts' => DB::table('flag_attempts')
                    ->where('created_at', '>', now()->subHour())
                    ->count(),
                'correct_attempts' => DB::table('flag_attempts')
                    ->where('is_correct', true)
                    ->where('created_at', '>', now()->subHour())
                    ->count(),
                'accuracy_rate' => DB::table('flag_attempts')
                    ->selectRaw('ROUND(SUM(CASE WHEN is_correct THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as rate')
                    ->where('created_at', '>', now()->subHour())
                    ->value('rate') ?? 0,
            ];

            return [
                'teams' => $teams,
                'tasks' => $tasks,
                'global' => $globalStats,
            ];
        });
    }

    private function getDetailedStats()
    {
        return [
            'teams_ranking' => Team::where('is_active', true)
                ->orderBy('score', 'desc')
                ->get()
                ->map(function ($team, $index) {
                    return [
                        'position' => $index + 1,
                        'name' => $team->name,
                        'score' => $team->score,
                        'flags_found' => $team->flags_found,
                        'wrong_attempts' => $team->wrong_attempts,
                        'captain' => $team->captain ? $team->captain->full_name : 'N/A',
                        'members_count' => $team->members()->count(),
                    ];
                }),
            'task_statistics' => Task::with(['teams' => function ($query) {
                $query->withPivot(['flag1_found', 'flag2_found', 'wrong_attempts', 'score']);
            }])
            ->get()
            ->map(function ($task) {
                $teams = $task->teams;
                
                return [
                    'task_name' => $task->name,
                    'total_assigned' => $teams->count(),
                    'completed_teams' => $teams->where('pivot.completed_at', '!=', null)->count(),
                    'flag1_found' => $teams->where('pivot.flag1_found', true)->count(),
                    'flag2_found' => $teams->where('pivot.flag2_found', true)->count(),
                    'average_score' => round($teams->avg('pivot.score') ?? 0, 2),
                    'total_wrong_attempts' => $teams->sum('pivot.wrong_attempts'),
                ];
            }),
            'hourly_activity' => DB::table('flag_attempts')
                ->selectRaw('HOUR(created_at) as hour, COUNT(*) as attempts')
                ->where('created_at', '>', now()->subDay())
                ->groupBy('hour')
                ->orderBy('hour')
                ->get(),
        ];
    }

    private function convertToCSV($data)
    {
        $output = fopen('php://temp', 'r+');
        
        // Команды
        fputcsv($output, ['Позиция', 'Название команды', 'Счет', 'Флагов найдено', 'Неправильных попыток', 'Капитан', 'Участников']);
        
        foreach ($data['teams_ranking'] as $team) {
            fputcsv($output, [
                $team['position'],
                $team['name'],
                $team['score'],
                $team['flags_found'],
                $team['wrong_attempts'],
                $team['captain'],
                $team['members_count'],
            ]);
        }
        
        fputcsv($output, []); // Пустая строка
        
        // Задания
        fputcsv($output, ['Задание', 'Назначено команд', 'Завершили', 'Флаг1 найдено', 'Флаг2 найдено', 'Средний счет', 'Всего ошибок']);
        
        foreach ($data['task_statistics'] as $task) {
            fputcsv($output, [
                $task['task_name'],
                $task['total_assigned'],
                $task['completed_teams'],
                $task['flag1_found'],
                $task['flag2_found'],
                $task['average_score'],
                $task['total_wrong_attempts'],
            ]);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}