<?php

namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendTaskAssignedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(TaskAssigned $event)
    {
        $task = $event->task;
        $team = $event->team;
        
        // Отправляем уведомление всем участникам команды
        $members = $team->members;
        
        foreach ($members as $member) {
            $member->notify(new TaskAssignedNotification($task, $team));
        }

        // Также отправляем уведомление администраторам
        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new TaskAssignedNotification($task, $team, true));
    }

    public function failed(TaskAssigned $event, $exception)
    {
        \Log::error('Failed to send task assigned notification', [
            'task_id' => $event->task->id,
            'team_id' => $event->team->id,
            'error' => $exception->getMessage(),
        ]);
    }
}