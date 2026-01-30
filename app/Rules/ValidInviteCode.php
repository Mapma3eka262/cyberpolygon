<?php

namespace App\Rules;

use App\Models\Team;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidInviteCode implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $team = Team::where('invite_code', $value)->first();

        if (!$team) {
            $fail('Код приглашения недействителен.');
            return;
        }

        if (!$team->is_active) {
            $fail('Команда с этим кодом приглашения деактивирована.');
            return;
        }

        if ($team->members()->count() >= config('ctf.teams.max_members', 5)) {
            $fail('Команда уже достигла максимального количества участников.');
        }
    }
}