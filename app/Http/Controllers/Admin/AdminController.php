<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Team;
use App\Models\Task;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->isAdmin()) {
                abort(403, 'Доступ запрещен');
            }
            return $next($request);
        });
    }

    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function teams()
    {
        $teams = Team::with('captain', 'members')->paginate(20);
        return view('admin.teams', compact('teams'));
    }

    public function tasks()
    {
        $tasks = Task::with('teams')->paginate(20);
        return view('admin.tasks', compact('tasks'));
    }

    public function analytics()
    {
        // Используем shell для быстрого получения метрик
        $cpuUsage = sys_getloadavg()[0];
        $memoryInfo = explode("\n", file_get_contents('/proc/meminfo'));
        
        $totalMem = 0;
        $freeMem = 0;
        
        foreach ($memoryInfo as $line) {
            if (strpos($line, 'MemTotal:') === 0) {
                $totalMem = intval(preg_replace('/[^0-9]/', '', $line)) / 1024;
            }
            if (strpos($line, 'MemAvailable:') === 0) {
                $freeMem = intval(preg_replace('/[^0-9]/', '', $line)) / 1024;
            }
        }
        
        $memoryUsage = $totalMem - $freeMem;
        $memoryUsagePercent = ($memoryUsage / $totalMem) * 100;

        // Сохраняем лог
        SystemLog::create([
            'cpu_usage' => $cpuUsage,
            'memory_usage' => $memoryUsage,
            'memory_total' => $totalMem,
            'active_users' => User::where('updated_at', '>', now()->subMinutes(5))->count(),
            'active_teams' => Team::where('is_active', true)->count(),
            'total_attempts' => DB::table('flag_attempts')->where('created_at', '>', now()->subHour())->count(),
        ]);

        $logs = SystemLog::orderBy('created_at', 'desc')->limit(100)->get();

        return view('admin.analytics', compact('cpuUsage', 'memoryUsagePercent', 'logs'));
    }

    public function stats()
    {
        $stats = Team::with('activeTask')
            ->where('is_active', true)
            ->orderBy('score', 'desc')
            ->get()
            ->map(function ($team) {
                $activeTask = $team->activeTask();
                return [
                    'team' => $team,
                    'task' => $activeTask,
                    'progress' => $activeTask ? [
                        'flag1' => $activeTask->pivot->flag1_found,
                        'flag2' => $activeTask->pivot->flag2_found,
                        'wrong_attempts' => $activeTask->pivot->wrong_attempts,
                        'score' => $activeTask->pivot->score,
                    ] : null,
                ];
            });

        return view('admin.stats', compact('stats'));
    }

    public function createTask(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_ip_subnet' => 'required|ip',
            'duration_minutes' => 'required|integer|min:1',
            'flag1' => 'required|string',
            'flag2' => 'required|string',
            'flag1_points' => 'required|integer|min:1',
            'flag2_points' => 'required|integer|min:1',
        ]);

        $task = Task::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'target_ip_subnet' => $validated['target_ip_subnet'],
            'duration_minutes' => $validated['duration_minutes'],
            'flag1_points' => $validated['flag1_points'],
            'flag2_points' => $validated['flag2_points'],
        ]);

        // Устанавливаем флаги через сеттеры (они будут захэшированы)
        $task->flag1 = $validated['flag1'];
        $task->flag2 = $validated['flag2'];
        $task->save();

        return redirect()->route('admin.tasks')->with('success', 'Задание создано.');
    }

    public function assignTask(Request $request)
    {
        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id',
            'task_id' => 'required|exists:tasks,id',
        ]);

        $team = Team::find($validated['team_id']);
        $task = Task::find($validated['task_id']);

        if ($team->tasks()->where('task_id', $task->id)->exists()) {
            return back()->with('error', 'Задание уже назначено команде.');
        }

        $team->tasks()->attach($task->id, [
            'started_at' => now(),
        ]);

        return back()->with('success', 'Задание назначено команде.');
    }
}