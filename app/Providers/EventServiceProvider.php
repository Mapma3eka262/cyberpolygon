<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        \App\Events\UserRegistered::class => [
            \App\Listeners\LogUserRegistration::class,
        ],

        \App\Events\FlagSubmitted::class => [
            \App\Listeners\UpdateTeamScore::class,
            \App\Listeners\LogFlagAttempt::class,
            \App\Listeners\SendFlagNotification::class,
            \App\Listeners\UpdateLeaderboard::class,
        ],

        \App\Events\TaskAssigned::class => [
            \App\Listeners\SendTaskAssignedNotification::class,
            \App\Listeners\LogTaskAssignment::class,
        ],

        \App\Events\TaskCompleted::class => [
            \App\Listeners\SendTaskCompletionNotification::class,
            \App\Listeners\UpdateLeaderboard::class,
        ],

        \App\Events\LeaderboardUpdated::class => [
            \App\Listeners\CacheLeaderboard::class,
        ],
    ];

    protected $subscribe = [
        \App\Listeners\UpdateLeaderboard::class,
    ];

    public function boot(): void
    {
        parent::boot();
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}