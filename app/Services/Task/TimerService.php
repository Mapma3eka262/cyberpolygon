<?php

namespace App\Services\Task;

use App\Models\TeamTask;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TimerService
{
    public function checkExpiredTasks(): array
    {
        $expiredTasks = TeamTask::whereNull('completed_at')
            ->whereHas('task', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['team', 'task'])
            ->get()
            ->filter(function ($teamTask) {
                return $teamTask->isExpired();
            });

        $results = [];
        
        foreach ($expiredTasks as $teamTask) {
            try {
                $teamTask->markCompleted();
                
                $results[] = [
                    'team_task_id' => $teamTask->id,
                    'team' => $teamTask->team->name,
                    'task' => $teamTask->task->name,
                    'completed_at' => now()->toDateTimeString(),
                ];

                Log::info('Task expired and marked as completed', [
                    'team_task_id' => $teamTask->id,
                    'team' => $teamTask->team->name,
                    'task' => $teamTask->task->name,
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to mark task as completed', [
                    'team_task_id' => $teamTask->id,
                    'error' => $e->getMessage(),
                ]);
                
                $results[] = [
                    'team_task_id' => $teamTask->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    public function getTaskRemainingTime(int $teamTaskId): ?array
    {
        $cacheKey = "task_timer_{$teamTaskId}";
        
        return Cache::remember($cacheKey, 10, function () use ($teamTaskId) {
            $teamTask = TeamTask::with('task')->find($teamTaskId);
            
            if (!$teamTask || $teamTask->completed_at) {
                return null;
            }

            $remaining = $teamTask->getRemainingTime();
            
            if ($remaining <= 0) {
                $teamTask->markCompleted();
                return null;
            }

            return [
                'remaining_seconds' => $remaining,
                'remaining_minutes' => floor($remaining / 60),
                'remaining_hours' => floor($remaining / 3600),
                'percentage' => $teamTask->task->duration_minutes > 0 
                    ? (($teamTask->task->duration_minutes * 60 - $remaining) / ($teamTask->task->duration_minutes * 60)) * 100
                    : 0,
                'is_expired' => false,
                'expires_at' => $teamTask->started_at->addMinutes($teamTask->task->duration_minutes)->toDateTimeString(),
            ];
        });
    }

    public function broadcastTimers(): void
    {
        $activeTasks = TeamTask::whereNull('completed_at')
            ->with(['team', 'task'])
            ->get()
            ->map(function ($teamTask) {
                $remaining = $teamTask->getRemainingTime();
                
                return [
                    'team_task_id' => $teamTask->id,
                    'team_id' => $teamTask->team_id,
                    'team_name' => $teamTask->team->name,
                    'task_name' => $teamTask->task->name,
                    'remaining_seconds' => $remaining,
                    'is_expired' => $remaining <= 0,
                    'progress' => [
                        'flag1_found' => (bool) $teamTask->flag1_found,
                        'flag2_found' => (bool) $teamTask->flag2_found,
                    ],
                ];
            });

        // Здесь можно добавить broadcast через WebSocket (Laravel Echo, Pusher и т.д.)
        // event(new \App\Events\TaskTimersUpdated($activeTasks));
        
        Cache::put('active_tasks_timers', $activeTasks, 5);
    }

    public function validateTaskTime(TeamTask $teamTask): bool
    {
        if ($teamTask->completed_at) {
            return true;
        }

        $remaining = $teamTask->getRemainingTime();
        
        if ($remaining <= 0) {
            $teamTask->markCompleted();
            return false;
        }

        return true;
    }

    public function pauseTask(TeamTask $teamTask, bool $pause): array
    {
        if ($teamTask->completed_at) {
            return [
                'success' => false,
                'message' => 'Невозможно изменить статус завершенного задания.',
            ];
        }

        // Для простой реализации мы можем "продлить" время на время паузы
        // В реальной системе нужно хранить время паузы отдельно
        
        if ($pause) {
            // Пауза - добавляем 24 часа к времени выполнения
            $task = $teamTask->task;
            $task->update([
                'duration_minutes' => $task->duration_minutes + (24 * 60),
            ]);
            
            $message = 'Задание поставлено на паузу.';
        } else {
            // Снятие с паузы - возвращаем оригинальное время
            // В реальной системе нужно вычислять сколько времени прошло без учета паузы
            $message = 'Задание возобновлено.';
        }

        return [
            'success' => true,
            'message' => $message,
        ];
    }
}