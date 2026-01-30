<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Task\TimerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimerController extends Controller
{
    protected $timerService;

    public function __construct(TimerService $timerService)
    {
        $this->timerService = $timerService;
    }

    public function remaining($teamTaskId)
    {
        $remaining = $this->timerService->getTaskRemainingTime($teamTaskId);
        
        if (!$remaining) {
            return response()->json([
                'success' => false,
                'message' => 'Задание не найдено или уже завершено.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $remaining,
        ]);
    }

    public function active(Request $request)
    {
        $user = Auth::user();
        $team = $user->team;
        
        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Вы не состоите в команде.',
            ], 400);
        }

        $activeTask = $team->activeTask();
        
        if (!$activeTask) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Нет активных заданий.',
            ]);
        }

        $remaining = $this->timerService->getTaskRemainingTime($activeTask->pivot->id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'task' => [
                    'id' => $activeTask->id,
                    'name' => $activeTask->name,
                    'duration_minutes' => $activeTask->duration_minutes,
                ],
                'timer' => $remaining,
                'progress' => [
                    'flag1_found' => (bool) $activeTask->pivot->flag1_found,
                    'flag2_found' => (bool) $activeTask->pivot->flag2_found,
                    'score' => $activeTask->pivot->score,
                    'wrong_attempts' => $activeTask->pivot->wrong_attempts,
                ],
            ],
        ]);
    }

    public function checkExpired(Request $request)
    {
        // Этот метод обычно доступен только админам
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен.',
            ], 403);
        }

        $results = $this->timerService->checkExpiredTasks();
        
        return response()->json([
            'success' => true,
            'data' => $results,
            'message' => 'Проверка завершена. Истекло заданий: ' . count($results),
        ]);
    }

    public function broadcast(Request $request)
    {
        // Этот метод обычно доступен только админам
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен.',
            ], 403);
        }

        $this->timerService->broadcastTimers();
        
        return response()->json([
            'success' => true,
            'message' => 'Таймеры обновлены и отправлены всем клиентам.',
        ]);
    }
}