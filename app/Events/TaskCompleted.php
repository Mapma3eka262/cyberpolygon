<?php

namespace App\Events;

use App\Models\TeamTask;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $teamTask;
    public $message;

    public function __construct(TeamTask $teamTask)
    {
        $this->teamTask = $teamTask;
        
        $flagsFound = ($teamTask->flag1_found ? 1 : 0) + ($teamTask->flag2_found ? 1 : 0);
        $this->message = "Задание '{$teamTask->task->name}' завершено! Найдено флагов: {$flagsFound}/2";
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('team.' . $this->teamTask->team_id),
            new PrivateChannel('admin.dashboard'),
        ];
    }

    public function broadcastWith()
    {
        return [
            'team_task_id' => $this->teamTask->id,
            'task_id' => $this->teamTask->task_id,
            'task_name' => $this->teamTask->task->name,
            'team_id' => $this->teamTask->team_id,
            'team_name' => $this->teamTask->team->name,
            'score' => $this->teamTask->score,
            'flags_found' => [
                'flag1' => (bool) $this->teamTask->flag1_found,
                'flag2' => (bool) $this->teamTask->flag2_found,
            ],
            'message' => $this->message,
            'completed_at' => $this->teamTask->completed_at->toDateTimeString(),
        ];
    }

    public function broadcastAs()
    {
        return 'task.completed';
    }
}