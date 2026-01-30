<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamTask extends Model
{
    use HasFactory;

    protected $table = 'team_tasks';

    protected $fillable = [
        'team_id',
        'task_id',
        'started_at',
        'completed_at',
        'flag1_found',
        'flag2_found',
        'wrong_attempts',
        'score',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'flag1_found' => 'boolean',
        'flag2_found' => 'boolean',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function flagAttempts()
    {
        return $this->hasMany(FlagAttempt::class);
    }

    public function isCompleted()
    {
        return !is_null($this->completed_at);
    }

    public function isExpired()
    {
        if (!$this->started_at || $this->isCompleted()) {
            return false;
        }

        $endTime = $this->started_at->addMinutes($this->task->duration_minutes);
        return now()->greaterThan($endTime);
    }

    public function getRemainingTime()
    {
        if (!$this->started_at || $this->isCompleted()) {
            return 0;
        }

        $endTime = $this->started_at->addMinutes($this->task->duration_minutes);
        $remaining = max(0, $endTime->diffInSeconds(now()));

        return $remaining;
    }

    public function markCompleted()
    {
        $this->update(['completed_at' => now()]);
    }
}