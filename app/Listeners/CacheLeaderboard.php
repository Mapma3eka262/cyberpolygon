<?php

namespace App\Listeners;

use App\Events\LeaderboardUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;

class CacheLeaderboard implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(LeaderboardUpdated $event)
    {
        // Кэшируем данные лидерборда на 60 секунд
        Cache::put('leaderboard_data', $event->leaderboard, 60);
        Cache::put('leaderboard_timestamp', $event->timestamp, 60);
    }
}