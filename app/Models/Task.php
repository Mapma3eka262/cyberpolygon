<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'target_ip_subnet',
        'duration_minutes',
        'flag1_points',
        'flag2_points',
        'flag1_hash',
        'flag2_hash',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_tasks')
            ->withPivot(['started_at', 'completed_at', 'flag1_found', 'flag2_found', 'wrong_attempts', 'score'])
            ->withTimestamps();
    }

    public function setFlag1Attribute($value)
    {
        $this->attributes['flag1_hash'] = Hash::make($value);
    }

    public function setFlag2Attribute($value)
    {
        $this->attributes['flag2_hash'] = Hash::make($value);
    }

    public function verifyFlag($flag, $type)
    {
        $hashColumn = $type === 'flag1' ? 'flag1_hash' : 'flag2_hash';
        return Hash::check($flag, $this->$hashColumn);
    }
}