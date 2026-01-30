<?php

namespace App\Events;

use App\Models\Task;
use App\Models\Team;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;
    public $team;
    public $message;

    public function __construct(Task $task, Team $team)
    {
        $this->task = $task;
        $this->team = $team;
        $this->message = "Новое задание '{$task->name}' назначено вашей команде!";
    }

    public function broadcastOn()
    {
        return new PrivateChannel('team.' . $this->team->id);
    }

    public function broadcastWith()
    {
        return [
            'task_id' => $this->task->id,
            'task_name' => $this->task->name,
            'team_id' => $this->team->id,
            'team_name' => $this->team->name,
            'message' => $this->message,
            'duration_minutes' => $this->task->duration_minutes,
            'started_at' => now()->toDateTimeString(),
        ];
    }

    public function broadcastAs()
    {
        return 'task.assigned';
    }
}