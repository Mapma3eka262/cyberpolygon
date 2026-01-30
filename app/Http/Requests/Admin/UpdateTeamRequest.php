<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Team;

class UpdateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        $teamId = $this->route('team')->id;

        return [
            'name' => ['sometimes', 'string', 'max:100', "unique:teams,name,{$teamId}"],
            'target_ip' => ['nullable', 'string', 'max:50'],
            'task_timer' => ['nullable', 'integer', 'min:1', 'max:480'],
            'is_active' => ['boolean'],
            'score' => ['sometimes', 'integer', 'min:0'],
            'flags_found' => ['sometimes', 'integer', 'min:0'],
            'wrong_attempts' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Название команды обязательно для заполнения.',
            'name.unique' => 'Команда с таким названием уже существует.',
            'target_ip.max' => 'IP-адрес не должен превышать 50 символов.',
            'task_timer.min' => 'Таймер должен быть не менее 1 минуты.',
            'task_timer.max' => 'Таймер не должен превышать 8 часов.',
            'score.min' => 'Счет не может быть отрицательным.',
            'flags_found.min' => 'Количество найденных флагов не может быть отрицательным.',
            'wrong_attempts.min' => 'Количество неправильных попыток не может быть отрицательным.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->has('score') && $this->has('flags_found')) {
                $maxPossibleScore = $this->input('flags_found') * 100; // Максимум 100 за флаг
                
                if ($this->input('score') > $maxPossibleScore) {
                    $validator->errors()->add('score', 
                        "Счет ({$this->input('score')}) превышает максимально возможный ({$maxPossibleScore}) для {$this->input('flags_found')} флагов.");
                }
            }
        });
    }
}