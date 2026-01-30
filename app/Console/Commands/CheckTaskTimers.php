<?php

namespace App\Console\Commands;

use App\Models\TeamTask;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckTaskTimers extends Command
{
    protected $signature = 'ctf:check-timers';
    protected $description = 'Проверяет истекшие таймеры заданий';

    public function handle()
    {
        $this->info('Начинаю проверку таймеров заданий...');

        $expiredTasks = TeamTask::whereNull('completed_at')
            ->whereHas('task', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['team', 'task'])
            ->get()
            ->filter(function ($teamTask) {
                $endTime = $teamTask->started_at->addMinutes($teamTask->task->duration_minutes);
                return now()->greaterThan($endTime);
            });

        $count = 0;

        foreach ($expiredTasks as $teamTask) {
            $teamTask->update(['completed_at' => now()]);
            
            $this->info("Задание #{$teamTask->task_id} для команды '{$teamTask->team->name}' истекло.");
            
            Log::info('Task timer expired', [
                'team_task_id' => $teamTask->id,
                'team' => $teamTask->team->name,
                'task' => $teamTask->task->name,
                'completed_at' => now()->toDateTimeString(),
            ]);

            $count++;
        }

        $this->info("Проверка завершена. Истекло заданий: {$count}");

        return Command::SUCCESS;
    }
}