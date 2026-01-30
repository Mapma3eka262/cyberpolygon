<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Регистрация сервисов
        $this->app->singleton(\App\Services\Analytics\SystemMetricsService::class);
        $this->app->singleton(\App\Services\Flag\FlagValidationService::class);
        $this->app->singleton(\App\Services\Auth\RegistrationService::class);
        $this->app->singleton(\App\Services\Task\TaskAssignmentService::class);
        $this->app->singleton(\App\Services\Task\TimerService::class);
        $this->app->singleton(\App\Services\Team\TeamManagementService::class);
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
        
        Paginator::useBootstrapFive();
        
        // Глобальные переменные для Blade
        view()->composer('*', function ($view) {
            $view->with('competitionStart', config('ctf.competition.start_date'));
            $view->with('competitionEnd', config('ctf.competition.end_date'));
            $view->with('registrationOpen', config('ctf.competition.registration_open'));
        });
    }
}