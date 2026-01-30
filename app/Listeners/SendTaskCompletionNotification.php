<?php

namespace App\Listeners;

use App\Events\TaskCompleted;
use App\Models\User;
use App\Notifications\TaskCompletedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendTaskCompletionNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(TaskCompleted $event)
    {
        $teamTask = $event->teamTask;
        $team = $teamTask->team;
        $task = $teamTask->task;
        
        // Отправляем уведомление всем участникам команды
        $members = $team->members;
        
        foreach ($members as $member) {
            $member->notify(new TaskCompletedNotification($teamTask));
        }

        // Также отправляем уведомление администраторам
        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new \App\Notifications\TaskCompletedNotification($teamTask, true));
    }
}