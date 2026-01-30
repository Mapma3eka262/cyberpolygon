<?php

namespace App\Http\Controllers\Web;

use App\Models\Task;
use App\Models\FlagAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ArenaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $team = $user->team;
        $activeTask = $team->activeTask();

        $remainingTime = null;
        if ($activeTask && $activeTask->pivot->started_at) {
            $endsAt = $activeTask->pivot->started_at->addMinutes($activeTask->duration_minutes);
            $remainingTime = max(0, $endsAt->diffInSeconds(now()));
        }

        return view('arena', compact('team', 'activeTask', 'remainingTime'));
    }

    public function submitFlag(Request $request)
    {
        $request->validate([
            'flag_type' => 'required|in:flag1,flag2',
            'flag' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $team = $user->team;
        $activeTask = $team->activeTask();

        if (!$activeTask) {
            return back()->with('error', 'У вас нет активного задания.');
        }

        if ($activeTask->pivot->completed_at) {
            return back()->with('error', 'Задание уже завершено.');
        }

        // Проверка времени
        $endsAt = $activeTask->pivot->started_at->addMinutes($activeTask->duration_minutes);
        if (now()->greaterThan($endsAt)) {
            $activeTask->pivot->update(['completed_at' => now()]);
            return back()->with('error', 'Время на выполнение задания истекло.');
        }

        // Проверка флага
        $isCorrect = $activeTask->verifyFlag($request->flag, $request->flag_type);
        
        // Логирование попытки
        FlagAttempt::create([
            'user_id' => $user->id,
            'team_task_id' => $activeTask->pivot->id,
            'flag_type' => $request->flag_type,
            'attempt' => $request->flag,
            'is_correct' => $isCorrect,
            'ip_address' => $request->ip(),
        ]);

        if ($isCorrect) {
            // Обновление прогресса
            $flagColumn = $request->flag_type . '_found';
            $pointsColumn = $request->flag_type . '_points';
            
            DB::transaction(function () use ($activeTask, $flagColumn, $pointsColumn) {
                $pivot = $activeTask->pivot;
                
                if (!$pivot->$flagColumn) {
                    $pivot->$flagColumn = true;
                    $pivot->score += $activeTask->$pointsColumn;
                    $pivot->save();
                    
                    // Обновить общий счет команды
                    $team = $pivot->team;
                    $team->increment('score', $activeTask->$pointsColumn);
                    $team->increment('flags_found');
                }
            });

            // Проверка завершения задания
            if ($activeTask->pivot->flag1_found && $activeTask->pivot->flag2_found) {
                $activeTask->pivot->update(['completed_at' => now()]);
                return back()->with('success', 'Оба флага найдены! Задание завершено.');
            }

            return back()->with('success', 'Флаг принят!');
        } else {
            // Штраф за неправильную попытку
            DB::transaction(function () use ($activeTask, $team) {
                $activeTask->pivot->increment('wrong_attempts');
                $activeTask->pivot->decrement('score', 3);
                
                $team->increment('wrong_attempts');
                $team->decrement('score', 3);
            });

            return back()->with('error', 'Неверный флаг. Команда потеряла 3 балла.');
        }
    }
}