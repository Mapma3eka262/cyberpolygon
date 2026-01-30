<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Flag\SubmitFlagRequest;
use App\Services\Flag\FlagValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FlagController extends Controller
{
    protected $flagService;

    public function __construct(FlagValidationService $flagService)
    {
        $this->flagService = $flagService;
    }

    public function submit(SubmitFlagRequest $request)
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
                'success' => false,
                'message' => 'У вашей команды нет активного задания.',
            ], 400);
        }

        $teamTask = $activeTask->pivot;
        
        if ($teamTask->isCompleted() || $teamTask->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Задание уже завершено или время истекло.',
            ], 400);
        }

        $result = $this->flagService->validateFlag(
            $teamTask,
            $request->flag,
            $request->flag_type
        );

        return response()->json($result);
    }

    public function attempts(Request $request)
    {
        $user = Auth::user();
        
        $query = $user->flagAttempts()->with(['teamTask.task', 'teamTask.team']);
        
        if ($request->has('task_id')) {
            $query->whereHas('teamTask', function ($q) use ($request) {
                $q->where('task_id', $request->task_id);
            });
        }
        
        if ($request->has('flag_type')) {
            $query->where('flag_type', $request->flag_type);
        }
        
        $attempts = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));
        
        return response()->json([
            'success' => true,
            'data' => $attempts,
        ]);
    }

    public function stats(Request $request)
    {
        $user = Auth::user();
        $team = $user->team;
        
        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Вы не состоите в команде.',
            ], 400);
        }

        $stats = $this->flagService->getTeamAttemptsStats($team->id);
        
        // Общая статистика команды
        $teamStats = [
            'total_score' => $team->score,
            'flags_found' => $team->flags_found,
            'wrong_attempts' => $team->wrong_attempts,
            'accuracy_rate' => $team->flags_found > 0 
                ? round(($team->flags_found / ($team->flags_found + $team->wrong_attempts)) * 100, 2)
                : 0,
        ];
        
        return response()->json([
            'success' => true,
            'data' => [
                'flag_stats' => $stats,
                'team_stats' => $teamStats,
            ],
        ]);
    }
}