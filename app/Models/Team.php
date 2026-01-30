<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'invite_code',
        'target_ip',
        'task_timer',
        'task_started_at',
        'task_ends_at',
        'flags_found',
        'wrong_attempts',
        'score',
        'is_active',
    ];

    protected $casts = [
        'task_started_at' => 'datetime',
        'task_ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function members()
    {
        return $this->hasMany(User::class);
    }

    public function captain()
    {
        return $this->hasOne(User::class)->where('role', 'captain');
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'team_tasks')
            ->withPivot(['started_at', 'completed_at', 'flag1_found', 'flag2_found', 'wrong_attempts', 'score'])
            ->withTimestamps();
    }

    public function activeTask()
    {
        return $this->tasks()
            ->wherePivot('completed_at', null)
            ->where('is_active', true)
            ->first();
    }

    public function generateInviteCode()
    {
        $this->invite_code = strtoupper(substr(md5(uniqid()), 0, 8));
        $this->save();
        return $this->invite_code;
    }
}