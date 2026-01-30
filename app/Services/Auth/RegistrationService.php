<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\Team;
use App\Models\Invitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Events\UserRegistered;

class RegistrationService
{
    public function registerCaptain(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Создаем команду
            $team = Team::create([
                'name' => $data['team_name'],
                'is_active' => true,
            ]);

            // Генерируем код приглашения
            $team->generateInviteCode();

            // Создаем пользователя-капитана
            $user = User::create([
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
                'surname' => $data['surname'],
                'name' => $data['name'],
                'patronymic' => $data['patronymic'] ?? null,
                'phone' => $data['phone'],
                'email' => $data['email'],
                'role' => 'captain',
                'team_id' => $team->id,
            ]);

            event(new UserRegistered($user));

            return $user;
        });
    }

    public function registerParticipant(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Находим команду по коду приглашения
            $team = Team::where('invite_code', $data['invite_code'])->firstOrFail();

            // Проверяем лимит участников
            if ($team->members()->count() >= config('ctf.teams.max_members', 5)) {
                throw new \Exception('Команда уже достигла максимального количества участников.');
            }

            // Создаем пользователя-участника
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

            event(new UserRegistered($user));

            return $user;
        });
    }

    public function createInvitation(array $data): Invitation
    {
        $user = auth()->user();
        
        if (!$user->isCaptain()) {
            throw new \Exception('Только капитаны могут отправлять приглашения.');
        }

        $team = $user->team;

        // Проверяем лимит участников
        if ($team->members()->count() >= config('ctf.teams.max_members', 5)) {
            throw new \Exception('Команда уже достигла максимального количества участников.');
        }

        // Проверяем, не отправлялось ли уже приглашение этому email
        $existingInvitation = Invitation::where('email', $data['email'])
            ->where('team_id', $team->id)
            ->active()
            ->first();

        if ($existingInvitation) {
            throw new \Exception('Приглашение этому email уже отправлено.');
        }

        // Создаем приглашение
        return Invitation::create([
            'team_id' => $team->id,
            'email' => $data['email'],
            'token' => bin2hex(random_bytes(32)),
            'role' => $data['role'] ?? 'participant',
            'expires_at' => now()->addHours(config('ctf.teams.default_invite_expiry', 24)),
        ]);
    }
}