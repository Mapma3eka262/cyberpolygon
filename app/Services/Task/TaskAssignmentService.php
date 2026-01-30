<?php

namespace App\Services\Task;

use App\Models\Task;
use App\Models\Team;
use App\Models\TeamTask;
use Illuminate\Support\Facades\DB;

class TaskAssignmentService
{
    public function assignTaskToTeam(Task $task, Team $team): array
    {
        return DB::transaction(function () use ($task, $team) {
            // Проверяем, не назначено ли уже задание
            if ($team->tasks()->where('task_id', $task->id)->exists()) {
                return [
                    'success' => false,
                    'message' => 'Задание уже назначено этой команде.',
                ];
            }

            // Проверяем, есть ли у команды активное задание
            if ($team->activeTask()) {
                return [
                    'success' => false,
                    'message' => 'У команды уже есть активное задание.',
                ];
            }

            // Назначаем задание
            $teamTask = TeamTask::create([
                'team_id' => $team->id,
                'task_id' => $task->id,
                'started_at' => now(),
                'flag1_found' => false,
                'flag2_found' => false,
                'wrong_attempts' => 0,
                'score' => 0,
            ]);

            // Обновляем IP команды, если задано в задании
            if ($task->target_ip_subnet && !$team->target_ip) {
                $team->update(['target_ip' => $task->target_ip_subnet]);
            }

            // Логируем назначение
            \App\Models\EventLog::log('task_assigned', [
                'user_id' => auth()->id(),
                'team_id' => $team->id,
                'task_id' => $task->id,
                'details' => [
                    'task_name' => $task->name,
                    'team_name' => $team->name,
                    'duration' => $task->duration_minutes,
                ],
            ]);

            return [
                'success' => true,
                'message' => 'Задание успешно назначено команде.',
                'team_task' => $teamTask,
            ];
        });
    }

    public function assignTaskToMultipleTeams(Task $task, array $teamIds): array
    {
        $results = [];
        $successCount = 0;

        foreach ($teamIds as $teamId) {
            $team = Team::find($teamId);
            
            if (!$team) {
                $results[] = [
                    'team_id' => $teamId,
                    'success' => false,
                    'message' => 'Команда не найдена.',
                ];
                continue;
            }

            $result = $this->assignTaskToTeam($task, $team);
            $result['team_id'] = $teamId;
            $results[] = $result;

            if ($result['success']) {
                $successCount++;
            }
        }

        return [
            'results' => $results,
            'total_assigned' => $successCount,
            'total_failed' => count($teamIds) - $successCount,
        ];
    }

    public function unassignTask(TeamTask $teamTask): array
    {
        if ($teamTask->completed_at) {
            return [
                'success' => false,
                'message' => 'Невозможно отменить завершенное задание.',
            ];
        }

        DB::transaction(function () use ($teamTask) {
            // Удаляем все попытки флагов
            $teamTask->flagAttempts()->delete();
            
            // Удаляем связь
            $teamTask->delete();

            // Логируем отмену
            \App\Models\EventLog::log('task_unassigned', [
                'user_id' => auth()->id(),
                'team_id' => $teamTask->team_id,
                'task_id' => $teamTask->task_id,
                'details' => [
                    'team_name' => $teamTask->team->name,
                    'task_name' => $teamTask->task->name,
                ],
            ]);
        });

        return [
            'success' => true,
            'message' => 'Задание успешно отменено для команды.',
        ];
    }

    public function extendTaskTime(TeamTask $teamTask, int $additionalMinutes): array
    {
        if ($teamTask->completed_at) {
            return [
                'success' => false,
                'message' => 'Невозможно продлить завершенное задание.',
            ];
        }

        // Обновляем задание через расширение времени в задаче
        $task = $teamTask->task;
        $task->update([
            'duration_minutes' => $task->duration_minutes + $additionalMinutes,
        ]);

        \App\Models\EventLog::log('task_time_extended', [
            'user_id' => auth()->id(),
            'team_id' => $teamTask->team_id,
            'task_id' => $teamTask->task_id,
            'details' => [
                'additional_minutes' => $additionalMinutes,
                'new_duration' => $task->duration_minutes,
            ],
        ]);

        return [
            'success' => true,
            'message' => "Время задания продлено на {$additionalMinutes} минут.",
            'new_duration' => $task->duration_minutes,
        ];
    }

    public function getTeamProgress(Team $team): array
    {
        $activeTask = $team->activeTask();
        
        if (!$activeTask) {
            return [
                'has_active_task' => false,
                'message' => 'Нет активных заданий.',
            ];
        }

        $teamTask = $activeTask->pivot;
        $remainingTime = $teamTask->getRemainingTime();

        return [
            'has_active_task' => true,
            'task' => [
                'id' => $activeTask->id,
                'name' => $activeTask->name,
                'description' => $activeTask->description,
                'duration_minutes' => $activeTask->duration_minutes,
                'target_ip' => $team->target_ip ?: $activeTask->target_ip_subnet,
                'flag1_points' => $activeTask->flag1_points,
                'flag2_points' => $activeTask->flag2_points,
            ],
            'progress' => [
                'started_at' => $teamTask->started_at,
                'completed_at' => $teamTask->completed_at,
                'flag1_found' => (bool) $teamTask->flag1_found,
                'flag2_found' => (bool) $teamTask->flag2_found,
                'wrong_attempts' => $teamTask->wrong_attempts,
                'score' => $teamTask->score,
                'remaining_time' => $remainingTime,
                'is_expired' => $teamTask->isExpired(),
                'is_completed' => $teamTask->isCompleted(),
            ],
            'time' => [
                'remaining_minutes' => floor($remainingTime / 60),
                'remaining_seconds' => $remainingTime % 60,
                'total_minutes' => $activeTask->duration_minutes,
                'elapsed_minutes' => now()->diffInMinutes($teamTask->started_at),
                'percentage' => $activeTask->duration_minutes > 0 
                    ? min(100, (now()->diffInMinutes($teamTask->started_at) / $activeTask->duration_minutes) * 100)
                    : 0,
            ],
        ];
    }
}