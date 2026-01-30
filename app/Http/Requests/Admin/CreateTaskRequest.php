<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CreateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:tasks,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'target_ip_subnet' => ['required', 'string', 'max:50'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:480'],
            'flag1' => ['required', 'string', 'min:10', 'max:100'],
            'flag2' => ['required', 'string', 'min:10', 'max:100'],
            'flag1_points' => ['required', 'integer', 'min:1', 'max:1000'],
            'flag2_points' => ['required', 'integer', 'min:1', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Название задания обязательно для заполнения.',
            'name.unique' => 'Задание с таким названием уже существует.',
            'target_ip_subnet.required' => 'IP-адрес или подсеть обязательны.',
            'duration_minutes.min' => 'Продолжительность должна быть не менее 1 минуты.',
            'duration_minutes.max' => 'Продолжительность не должна превышать 8 часов.',
            'flag1.required' => 'Первый флаг обязателен.',
            'flag2.required' => 'Второй флаг обязателен.',
            'flag1.min' => 'Первый флаг должен содержать минимум 10 символов.',
            'flag2.min' => 'Второй флаг должен содержать минимум 10 символов.',
            'flag1_points.required' => 'Количество баллов за первый флаг обязательно.',
            'flag2_points.required' => 'Количество баллов за второй флаг обязательно.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->flag1 === $this->flag2) {
                $validator->errors()->add('flag2', 'Флаги должны отличаться.');
            }

            if ($this->flag1_points >= $this->flag2_points) {
                $validator->errors()->add('flag2_points', 
                    'Второй флаг должен стоить больше баллов, чем первый.');
            }
        });
    }
}