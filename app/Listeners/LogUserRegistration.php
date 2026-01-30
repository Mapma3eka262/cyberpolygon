<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogUserRegistration implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(UserRegistered $event)
    {
        $user = $event->user;
        
        \App\Models\EventLog::log('user_registered', [
            'user_id' => $user->id,
            'details' => [
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'team_id' => $user->team_id,
            ],
        ]);
    }
}