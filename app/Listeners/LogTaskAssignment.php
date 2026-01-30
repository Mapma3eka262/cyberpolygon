<?php

namespace App\Listeners;

use App\Events\TaskAssigned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogTaskAssignment implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(TaskAssigned $event)
    {
        $task = $event->task;
        $team = $event->team;
        
        \App\Models\EventLog::log('task_assigned', [
            'user_id' => auth()->id() ?? null,
            'team_id' => $team->id,
            'task_id' => $task->id,
            'details' => [
                'task_name' => $task->name,
                'team_name' => $team->name,
                'duration_minutes' => $task->duration_minutes,
                'assigned_by' => auth()->user() ? auth()->user()->full_name : 'system',
            ],
        ]);
    }
}