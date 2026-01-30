<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $userId = auth()->id();

        return [
            'email' => ['required', 'email', "unique:users,email,{$userId}"],
            'phone' => ['required', 'string', 'max:20'],
            'current_password' => ['sometimes', 'required_with:new_password', 'current_password'],
            'new_password' => ['sometimes', 'nullable', 'string', 'min:6', 'confirmed'],
            'new_password_confirmation' => ['sometimes', 'required_with:new_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email обязателен для заполнения.',
            'email.email' => 'Введите корректный email адрес.',
            'email.unique' => 'Этот email уже используется другим пользователем.',
            'phone.required' => 'Телефон обязателен для заполнения.',
            'phone.max' => 'Телефон не должен превышать 20 символов.',
            'current_password.required' => 'Текущий пароль обязателен для смены пароля.',
            'current_password.current_password' => 'Текущий пароль указан неверно.',
            'new_password.min' => 'Новый пароль должен содержать минимум 6 символов.',
            'new_password.confirmed' => 'Подтверждение пароля не совпадает.',
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'Email',
            'phone' => 'Телефон',
            'current_password' => 'Текущий пароль',
            'new_password' => 'Новый пароль',
            'new_password_confirmation' => 'Подтверждение нового пароля',
        ];
    }
}