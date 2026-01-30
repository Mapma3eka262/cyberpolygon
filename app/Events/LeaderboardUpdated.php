<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeaderboardUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $leaderboard;
    public $timestamp;

    public function __construct()
    {
        $this->leaderboard = $this->getLeaderboardData();
        $this->timestamp = now()->toDateTimeString();
    }

    public function broadcastOn()
    {
        return new Channel('leaderboard');
    }

    public function broadcastWith()
    {
        return [
            'leaderboard' => $this->leaderboard,
            'timestamp' => $this->timestamp,
        ];
    }

    public function broadcastAs()
    {
        return 'leaderboard.updated';
    }

    private function getLeaderboardData()
    {
        return \App\Models\Team::where('is_active', true)
            ->orderBy('score', 'desc')
            ->orderBy('flags_found', 'desc')
            ->orderBy('wrong_attempts', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($team, $index) {
                return [
                    'position' => $index + 1,
                    'id' => $team->id,
                    'name' => $team->name,
                    'score' => $team->score,
                    'flags_found' => $team->flags_found,
                    'wrong_attempts' => $team->wrong_attempts,
                ];
            });
    }
}