<?php

namespace App\Http\Requests\Auth;

use App\Rules\ValidInviteCode;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return config('ctf.competition.registration_open', true);
    }

    public function rules(): array
    {
        $rules = [
            'username' => ['required', 'string', 'unique:users', 'min:3', 'max:50', 'regex:/^[a-zA-Z0-9_]+$/'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'surname' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:100'],
            'patronymic' => ['nullable', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^\+?[1-9]\d{1,14}$/'],
            'email' => ['required', 'email', 'unique:users'],
            'role' => ['required', 'in:captain,participant'],
            'privacy_policy' => ['accepted'],
        ];

        if ($this->input('role') === 'captain') {
            $rules['team_name'] = ['required', 'string', 'max:100', 'unique:teams,name'];
        } else {
            $rules['invite_code'] = ['required', 'string', 'size:8', new ValidInviteCode];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'Имя пользователя может содержать только буквы, цифры и символ подчеркивания.',
            'phone.regex' => 'Некорректный формат номера телефона.',
            'privacy_policy.accepted' => 'Вы должны принять политику конфиденциальности.',
            'invite_code.required' => 'Код приглашения обязателен для участников.',
            'team_name.required' => 'Название команды обязательно для капитанов.',
        ];
    }
}