<?php

namespace App\Http\Requests\Flag;

use Illuminate\Foundation\Http\FormRequest;

class SubmitFlagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'flag_type' => ['required', 'in:flag1,flag2'],
            'flag' => ['required', 'string', 'min:10', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'flag_type.required' => 'Тип флага обязателен.',
            'flag_type.in' => 'Некорректный тип флага.',
            'flag.required' => 'Флаг обязателен для заполнения.',
            'flag.min' => 'Флаг должен содержать минимум 10 символов.',
            'flag.max' => 'Флаг не должен превышать 100 символов.',
        ];
    }
}