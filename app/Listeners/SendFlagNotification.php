<?php

namespace App\Listeners;

use App\Events\FlagSubmitted;
use App\Models\User;
use App\Notifications\FlagFoundNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendFlagNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(FlagSubmitted $event)
    {
        $attempt = $event->attempt;
        
        if (!$attempt->is_correct) {
            return;
        }

        $team = $attempt->teamTask->team;
        $task = $attempt->teamTask->task;
        
        // Отправляем уведомление капитану
        $captain = $team->captain;
        if ($captain) {
            $captain->notify(new FlagFoundNotification($attempt));
        }

        // Отправляем уведомление администраторам
        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new FlagFoundNotification($attempt, true));
    }

    public function shouldQueue(FlagSubmitted $event)
    {
        return $event->attempt->is_correct;
    }
}