<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Team\TeamManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    protected $teamService;

    public function __construct(TeamManagementService $teamService)
    {
        $this->teamService = $teamService;
    }

    public function stats($teamId)
    {
        $user = Auth::user();
        
        // Проверяем доступ к статистике команды
        if (!$user->isAdmin() && $user->team_id != $teamId) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ к статистике этой команды запрещен.',
            ], 403);
        }

        $team = \App\Models\Team::find($teamId);
        
        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Команда не найдена.',
            ], 404);
        }

        $stats = $this->teamService->getTeamStats($team);
        
        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function leaderboard(Request $request)
    {
        $limit = $request->get('limit', 20);
        $offset = $request->get('offset', 0);
        
        $teams = \App\Models\Team::where('is_active', true)
            ->orderBy('score', 'desc')
            ->orderBy('flags_found', 'desc')
            ->orderBy('wrong_attempts', 'asc')
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(function ($team, $index) use ($offset) {
                return [
                    'position' => $offset + $index + 1,
                    'id' => $team->id,
                    'name' => $team->name,
                    'score' => $team->score,
                    'flags_found' => $team->flags_found,
                    'wrong_attempts' => $team->wrong_attempts,
                    'captain' => $team->captain ? $team->captain->full_name : 'N/A',
                    'members_count' => $team->members()->count(),
                    'last_activity' => $team->updated_at->diffForHumans(),
                ];
            });

        $totalTeams = \App\Models\Team::where('is_active', true)->count();
        $userTeamPosition = null;

        if (Auth::check() && Auth::user()->team) {
            $userTeam = Auth::user()->team;
            $userTeamPosition = \App\Models\Team::where('is_active', true)
                ->orderBy('score', 'desc')
                ->orderBy('flags_found', 'desc')
                ->orderBy('wrong_attempts', 'asc')
                ->pluck('id')
                ->search($userTeam->id) + 1;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'leaderboard' => $teams,
                'pagination' => [
                    'total' => $totalTeams,
                    'limit' => $limit,
                    'offset' => $offset,
                    'has_more' => ($offset + $limit) < $totalTeams,
                ],
                'user_team_position' => $userTeamPosition,
                'updated_at' => now()->toDateTimeString(),
            ],
        ]);
    }

    public function invite(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isCaptain()) {
            return response()->json([
                'success' => false,
                'message' => 'Только капитаны могут отправлять приглашения.',
            ], 403);
        }

        $team = $user->team;
        
        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Вы не состоите в команде.',
            ], 400);
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['sometimes', 'in:participant,captain'],
        ]);

        try {
            $invitation = $this->teamService->createInvitation($team, $validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Приглашение успешно отправлено.',
                'data' => [
                    'invitation_id' => $invitation->id,
                    'email' => $invitation->email,
                    'expires_at' => $invitation->expires_at->toDateTimeString(),
                    'invite_url' => route('invitation.accept', ['token' => $invitation->token]),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function members(Request $request, $teamId = null)
    {
        $user = Auth::user();
        
        // Если teamId не указан, используем команду текущего пользователя
        if (!$teamId) {
            if (!$user->team) {
                return response()->json([
                    'success' => false,
                    'message' => 'Вы не состоите в команде.',
                ], 400);
            }
            $teamId = $user->team->id;
        }

        // Проверяем доступ
        if (!$user->isAdmin() && $user->team_id != $teamId) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ к информации о команде запрещен.',
            ], 403);
        }

        $team = \App\Models\Team::with(['members' => function ($query) {
            $query->select(['id', 'username', 'surname', 'name', 'patronymic', 'email', 'phone', 'role', 'created_at']);
        }])->find($teamId);

        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Команда не найдена.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'team' => [
                    'id' => $team->id,
                    'name' => $team->name,
                    'invite_code' => $team->invite_code,
                ],
                'members' => $team->members,
                'total_members' => $team->members->count(),
            ],
        ]);
    }

    public function activity(Request $request, $teamId = null)
    {
        $user = Auth::user();
        
        if (!$teamId) {
            if (!$user->team) {
                return response()->json([
                    'success' => false,
                    'message' => 'Вы не состоите в команде.',
                ], 400);
            }
            $teamId = $user->team->id;
        }

        // Проверяем доступ
        if (!$user->isAdmin() && $user->team_id != $teamId) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ к активности команды запрещен.',
            ], 403);
        }

        $limit = $request->get('limit', 50);
        
        $activities = \App\Models\EventLog::where('team_id', $teamId)
            ->with(['user' => function ($query) {
                $query->select(['id', 'username', 'full_name']);
            }])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'event_type' => $activity->event_type,
                    'user' => $activity->user ? [
                        'id' => $activity->user->id,
                        'username' => $activity->user->username,
                        'full_name' => $activity->user->full_name,
                    ] : null,
                    'details' => $activity->details,
                    'created_at' => $activity->created_at->toDateTimeString(),
                    'time_ago' => $activity->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }
}