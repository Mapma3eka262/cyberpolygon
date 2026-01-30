<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Team;
use App\Http\Requests\Admin\CreateTaskRequest;
use App\Http\Requests\Admin\AssignTaskRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::withCount('teams');

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        if ($request->has('status')) {
            $query->where('is_active', $request->get('status') === 'active');
        }

        $tasks = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.tasks.index', compact('tasks'));
    }

    public function create()
    {
        return view('admin.tasks.create');
    }

    public function store(CreateTaskRequest $request)
    {
        DB::transaction(function () use ($request) {
            $task = Task::create([
                'name' => $request->name,
                'description' => $request->description,
                'target_ip_subnet' => $request->target_ip_subnet,
                'duration_minutes' => $request->duration_minutes,
                'flag1_points' => $request->flag1_points,
                'flag2_points' => $request->flag2_points,
            ]);

            // Устанавливаем флаги через сеттеры
            $task->flag1 = $request->flag1;
            $task->flag2 = $request->flag2;
            $task->save();

            // Логируем создание задания
            \App\Models\EventLog::log('task_created', [
                'user_id' => auth()->id(),
                'task_id' => $task->id,
                'details' => ['name' => $task->name],
            ]);
        });

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Задание успешно создано.');
    }

    public function edit(Task $task)
    {
        return view('admin.tasks.edit', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_ip_subnet' => 'required|ip',
            'duration_minutes' => 'required|integer|min:1|max:180',
            'flag1_points' => 'required|integer|min:1',
            'flag2_points' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $task->update($validated);

        if ($request->has('flag1') && !empty($request->flag1)) {
            $task->flag1 = $request->flag1;
        }

        if ($request->has('flag2') && !empty($request->flag2)) {
            $task->flag2 = $request->flag2;
        }

        $task->save();

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Задание успешно обновлено.');
    }

    public function destroy(Task $task)
    {
        if ($task->teams()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Невозможно удалить задание, так как оно уже назначено командам.');
        }

        $task->delete();

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Задание успешно удалено.');
    }

    public function assignForm(Task $task)
    {
        $teams = Team::where('is_active', true)->get();
        $assignedTeams = $task->teams()->pluck('teams.id')->toArray();

        return view('admin.tasks.assign', compact('task', 'teams', 'assignedTeams'));
    }

    public function assign(AssignTaskRequest $request, Task $task)
    {
        $teamIds = $request->team_ids;

        DB::transaction(function () use ($task, $teamIds) {
            foreach ($teamIds as $teamId) {
                if (!$task->teams()->where('team_id', $teamId)->exists()) {
                    $task->teams()->attach($teamId, [
                        'started_at' => now(),
                    ]);

                    // Отправляем уведомление команде
                    $team = Team::find($teamId);
                    \App\Events\TaskAssigned::dispatch($task, $team);
                }
            }
        });

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Задание успешно назначено выбранным командам.');
    }

    public function toggleStatus(Task $task)
    {
        $task->update(['is_active' => !$task->is_active]);

        $status = $task->is_active ? 'активировано' : 'деактивировано';
        
        return redirect()->back()
            ->with('success', "Задание успешно {$status}.");
    }
}