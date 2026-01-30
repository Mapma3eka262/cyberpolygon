<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Team;

class AssignTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'team_ids' => ['required', 'array', 'min:1'],
            'team_ids.*' => ['required', 'integer', 'exists:teams,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'team_ids.required' => 'Необходимо выбрать хотя бы одну команду.',
            'team_ids.array' => 'Неверный формат списка команд.',
            'team_ids.*.exists' => 'Одна или несколько выбранных команд не существуют.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $teamIds = $this->input('team_ids', []);
            $invalidTeams = [];

            foreach ($teamIds as $teamId) {
                $team = Team::find($teamId);
                
                if ($team && !$team->is_active) {
                    $invalidTeams[] = "Команда '{$team->name}' деактивирована.";
                }
            }

            if (!empty($invalidTeams)) {
                foreach ($invalidTeams as $error) {
                    $validator->errors()->add('team_ids', $error);
                }
            }
        });
    }
}