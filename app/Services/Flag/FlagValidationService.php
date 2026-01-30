<?php

namespace App\Services\Flag;

use App\Models\Task;
use App\Models\TeamTask;
use App\Models\FlagAttempt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class FlagValidationService
{
    public function validateFlag(TeamTask $teamTask, string $flag, string $flagType): array
    {
        $task = $teamTask->task;
        $team = $teamTask->team;

        // Проверяем, не найден ли уже этот флаг
        $flagFoundColumn = $flagType . '_found';
        if ($teamTask->$flagFoundColumn) {
            return [
                'success' => false,
                'message' => 'Этот флаг уже был найден.',
                'points' => 0,
            ];
        }

        // Проверяем корректность флага
        $isCorrect = $this->checkFlag($task, $flag, $flagType);

        // Записываем попытку
        $attempt = FlagAttempt::create([
            'user_id' => auth()->id(),
            'team_task_id' => $teamTask->id,
            'flag_type' => $flagType,
            'attempt' => $flag,
            'is_correct' => $isCorrect,
            'ip_address' => request()->ip(),
        ]);

        if ($isCorrect) {
            // Обновляем прогресс
            $teamTask->update([
                $flagFoundColumn => true,
            ]);

            // Начисляем очки
            $pointsColumn = $flagType . '_points';
            $points = $task->$pointsColumn;
            
            $teamTask->increment('score', $points);
            $team->increment('score', $points);
            $team->increment('flags_found');

            // Очищаем кэш лидерборда
            Cache::forget('leaderboard');

            return [
                'success' => true,
                'message' => 'Флаг принят!',
                'points' => $points,
            ];
        } else {
            // Штраф за неправильную попытку
            $penalty = config('ctf.scoring.wrong_attempt_penalty', 3);
            
            $teamTask->increment('wrong_attempts');
            $teamTask->decrement('score', $penalty);
            
            $team->increment('wrong_attempts');
            $team->decrement('score', $penalty);

            return [
                'success' => false,
                'message' => 'Неверный флаг. Команда потеряла ' . $penalty . ' баллов.',
                'points' => -$penalty,
            ];
        }
    }

    private function checkFlag(Task $task, string $flag, string $flagType): bool
    {
        $hashColumn = $flagType . '_hash';
        $providedHash = Hash::make($flag . config('ctf.flags.salt'));
        
        return hash_equals($task->$hashColumn, $providedHash);
    }

    public function getTeamAttemptsStats(int $teamId): array
    {
        return Cache::remember("team_attempts_stats_{$teamId}", 60, function () use ($teamId) {
            $attempts = FlagAttempt::whereHas('teamTask.team', function ($query) use ($teamId) {
                $query->where('id', $teamId);
            })
            ->selectRaw('flag_type, is_correct, COUNT(*) as count')
            ->groupBy('flag_type', 'is_correct')
            ->get();

            $stats = [
                'flag1' => ['correct' => 0, 'incorrect' => 0],
                'flag2' => ['correct' => 0, 'incorrect' => 0],
            ];

            foreach ($attempts as $attempt) {
                $type = $attempt->is_correct ? 'correct' : 'incorrect';
                $stats[$attempt->flag_type][$type] = $attempt->count;
            }

            return $stats;
        });
    }
}