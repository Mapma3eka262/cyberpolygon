<?php

namespace App\Listeners;

use App\Events\FlagSubmitted;
use App\Events\TaskCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

class UpdateLeaderboard implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event)
    {
        // Очищаем кэш лидерборда
        Cache::forget('leaderboard');
        
        // Если нужно, отправляем событие обновления лидерборда
        Event::dispatch(new \App\Events\LeaderboardUpdated());
    }

    public function subscribe($events)
    {
        $events->listen(
            FlagSubmitted::class,
            [UpdateLeaderboard::class, 'handle']
        );

        $events->listen(
            TaskCompleted::class,
            [UpdateLeaderboard::class, 'handle']
        );
    }
}