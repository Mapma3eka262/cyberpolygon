<?php

namespace App\Services\Team;

use App\Models\Team;
use App\Models\User;
use App\Models\Invitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeamManagementService
{
    public function createTeam(array $data, User $captain): Team
    {
        return DB::transaction(function () use ($data, $captain) {
            // Создаем команду
            $team = Team::create([
                'name' => $data['name'],
                'target_ip' => $data['target_ip'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // Генерируем код приглашения
            $team->generateInviteCode();

            // Назначаем капитана
            $captain->update([
                'team_id' => $team->id,
                'role' => 'captain',
            ]);

            // Создаем приглашения для других участников, если указаны
            if (isset($data['invitations']) && is_array($data['invitations'])) {
                foreach ($data['invitations'] as $invitationData) {
                    $this->createInvitation($team, $invitationData);
                }
            }

            \App\Models\EventLog::log('team_created', [
                'user_id' => $captain->id,
                'team_id' => $team->id,
                'details' => ['name' => $team->name],
            ]);

            return $team;
        });
    }

    public function updateTeam(Team $team, array $data): Team
    {
        $oldName = $team->name;
        
        $team->update($data);

        if (isset($data['name']) && $data['name'] !== $oldName) {
            \App\Models\EventLog::log('team_renamed', [
                'user_id' => auth()->id(),
                'team_id' => $team->id,
                'details' => [
                    'old_name' => $oldName,
                    'new_name' => $data['name'],
                ],
            ]);
        }

        return $team->fresh();
    }

    public function addTeamMember(Team $team, array $data): User
    {
        if ($team->members()->count() >= config('ctf.teams.max_members', 5)) {
            throw new \Exception('Команда уже достигла максимального количества участников.');
        }

        return DB::transaction(function () use ($team, $data) {
            $user = User::create([
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
                'surname' => $data['surname'],
                'name' => $data['name'],
                'patronymic' => $data['patronymic'] ?? null,
                'phone' => $data['phone'],
                'email' => $data['email'],
                'role' => 'participant',
                'team_id' => $team->id,
            ]);

            \App\Models\EventLog::log('team_member_added', [
                'user_id' => auth()->id(),
                'team_id' => $team->id,
                'details' => [
                    'member_id' => $user->id,
                    'member_name' => $user->full_name,
                ],
            ]);

            return $user;
        });
    }

    public function removeTeamMember(Team $team, User $user): bool
    {
        if ($user->team_id !== $team->id) {
            throw new \Exception('Пользователь не состоит в этой команде.');
        }

        if ($user->isCaptain()) {
            throw new \Exception('Невозможно удалить капитана команды.');
        }

        $user->update([
            'team_id' => null,
            'role' => 'participant',
        ]);

        \App\Models\EventLog::log('team_member_removed', [
            'user_id' => auth()->id(),
            'team_id' => $team->id,
            'details' => [
                'member_id' => $user->id,
                'member_name' => $user->full_name,
            ],
        ]);

        return true;
    }

    public function createInvitation(Team $team, array $data): Invitation
    {
        // Проверяем, не отправлялось ли уже приглашение
        $existingInvitation = Invitation::where('email', $data['email'])
            ->where('team_id', $team->id)
            ->active()
            ->first();

        if ($existingInvitation) {
            throw new \Exception('Приглашение этому email уже отправлено.');
        }

        // Проверяем, не состоит ли уже пользователь с таким email в команде
        $existingUser = User::where('email', $data['email'])->first();
        if ($existingUser && $existingUser->team_id === $team->id) {
            throw new \Exception('Пользователь с таким email уже состоит в команде.');
        }

        $invitation = Invitation::create([
            'team_id' => $team->id,
            'email' => $data['email'],
            'token' => Str::random(64),
            'role' => $data['role'] ?? 'participant',
            'expires_at' => now()->addHours(config('ctf.teams.default_invite_expiry', 24)),
        ]);

        // Здесь можно отправить email с приглашением
        // $invitation->notify(new \App\Notifications\TeamInvitationNotification($invitation));

        \App\Models\EventLog::log('team_invitation_created', [
            'user_id' => auth()->id(),
            'team_id' => $team->id,
            'details' => [
                'invitation_id' => $invitation->id,
                'email' => $data['email'],
                'role' => $data['role'] ?? 'participant',
            ],
        ]);

        return $invitation;
    }

    public function acceptInvitation(string $token, array $userData = null): User
    {
        $invitation = Invitation::where('token', $token)
            ->active()
            ->firstOrFail();

        if ($invitation->isExpired()) {
            throw new \Exception('Срок действия приглашения истек.');
        }

        return DB::transaction(function () use ($invitation, $userData) {
            $team = $invitation->team;
            
            // Проверяем лимит участников
            if ($team->members()->count() >= config('ctf.teams.max_members', 5)) {
                throw new \Exception('Команда уже достигла максимального количества участников.');
            }

            if ($userData) {
                // Создаем нового пользователя
                $user = User::create([
                    'username' => $userData['username'],
                    'password' => Hash::make($userData['password']),
                    'surname' => $userData['surname'],
                    'name' => $userData['name'],
                    'patronymic' => $userData['patronymic'] ?? null,
                    'phone' => $userData['phone'],
                    'email' => $invitation->email,
                    'role' => $invitation->role,
                    'team_id' => $team->id,
                ]);
            } else {
                // Пользователь уже существует
                $user = User::where('email', $invitation->email)->firstOrFail();
                $user->update([
                    'team_id' => $team->id,
                    'role' => $invitation->role,
                ]);
            }

            // Помечаем приглашение как принятое
            $invitation->update(['accepted_at' => now()]);

            \App\Models\EventLog::log('team_invitation_accepted', [
                'user_id' => $user->id,
                'team_id' => $team->id,
                'details' => [
                    'invitation_id' => $invitation->id,
                    'role' => $invitation->role,
                ],
            ]);

            return $user;
        });
    }

    public function getTeamStats(Team $team): array
    {
        return Cache::remember("team_stats_{$team->id}", 60, function () use ($team) {
            $members = $team->members()
                ->select(['id', 'username', 'full_name', 'role', 'email', 'created_at'])
                ->get();

            $tasks = $team->tasks()
                ->withPivot(['started_at', 'completed_at', 'flag1_found', 'flag2_found', 'wrong_attempts', 'score'])
                ->orderBy('team_tasks.created_at', 'desc')
                ->get();

            $totalTasks = $tasks->count();
            $completedTasks = $tasks->where('pivot.completed_at')->count();
            $totalScore = $team->score;
            $totalFlags = $team->flags_found;

            $recentActivity = \App\Models\EventLog::where('team_id', $team->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return [
                'basic_info' => [
                    'name' => $team->name,
                    'invite_code' => $team->invite_code,
                    'target_ip' => $team->target_ip,
                    'is_active' => $team->is_active,
                    'created_at' => $team->created_at,
                ],
                'members' => [
                    'total' => $members->count(),
                    'captain' => $members->where('role', 'captain')->first(),
                    'participants' => $members->where('role', 'participant'),
                    'list' => $members,
                ],
                'performance' => [
                    'total_score' => $totalScore,
                    'total_flags_found' => $totalFlags,
                    'wrong_attempts' => $team->wrong_attempts,
                    'accuracy_rate' => $totalFlags > 0 
                        ? round(($totalFlags / ($totalFlags + $team->wrong_attempts)) * 100, 2)
                        : 0,
                    'tasks_completed' => $completedTasks,
                    'tasks_total' => $totalTasks,
                    'completion_rate' => $totalTasks > 0 
                        ? round(($completedTasks / $totalTasks) * 100, 2)
                        : 0,
                ],
                'tasks' => $tasks->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'name' => $task->name,
                        'started_at' => $task->pivot->started_at,
                        'completed_at' => $task->pivot->completed_at,
                        'flags_found' => ($task->pivot->flag1_found ? 1 : 0) + ($task->pivot->flag2_found ? 1 : 0),
                        'score' => $task->pivot->score,
                        'wrong_attempts' => $task->pivot->wrong_attempts,
                        'status' => $task->pivot->completed_at ? 'completed' : ($task->pivot->started_at ? 'active' : 'pending'),
                    ];
                }),
                'recent_activity' => $recentActivity,
            ];
        });
    }
}