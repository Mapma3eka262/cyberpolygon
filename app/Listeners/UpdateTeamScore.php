<?php

namespace App\Listeners;

use App\Events\FlagSubmitted;
use App\Models\Team;
use Illuminate\Support\Facades\Cache;

class UpdateTeamScore
{
    public function handle(FlagSubmitted $event)
    {
        $attempt = $event->attempt;
        
        if (!$attempt->is_correct) {
            return;
        }

        $teamTask = $attempt->teamTask;
        $team = $teamTask->team;
        $task = $teamTask->task;

        // Очищаем кэш лидерборда
        Cache::forget('leaderboard');
        Cache::forget("team_stats_{$team->id}");

        // Логируем обновление счета
        \App\Models\EventLog::log('score_updated', [
            'user_id' => $attempt->user_id,
            'team_id' => $team->id,
            'task_id' => $task->id,
            'details' => [
                'flag_type' => $attempt->flag_type,
                'points' => $attempt->flag_type === 'flag1' 
                    ? $task->flag1_points 
                    : $task->flag2_points,
                'new_score' => $team->score,
            ],
        ]);
    }
}