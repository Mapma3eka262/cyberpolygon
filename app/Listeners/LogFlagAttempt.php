<?php

namespace App\Listeners;

use App\Events\FlagSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogFlagAttempt implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(FlagSubmitted $event)
    {
        $attempt = $event->attempt;
        
        \App\Models\EventLog::log('flag_attempt', [
            'user_id' => $attempt->user_id,
            'team_id' => $attempt->teamTask->team_id,
            'task_id' => $attempt->teamTask->task_id,
            'details' => [
                'flag_type' => $attempt->flag_type,
                'is_correct' => $attempt->is_correct,
                'attempt' => substr($attempt->attempt, 0, 50) . (strlen($attempt->attempt) > 50 ? '...' : ''),
                'team_task_id' => $attempt->team_task_id,
            ],
        ]);
    }

    public function failed(FlagSubmitted $event, $exception)
    {
        // Логируем ошибку, но не прерываем выполнение
        \Log::error('Failed to log flag attempt', [
            'attempt_id' => $event->attempt->id,
            'error' => $exception->getMessage(),
        ]);
    }
}