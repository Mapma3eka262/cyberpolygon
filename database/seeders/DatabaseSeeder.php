<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем администратора
        User::create([
            'username' => 'admin',
            'password' => Hash::make('admin'),
            'surname' => 'Admin',
            'name' => 'System',
            'email' => 'admin@ctfplatform.local',
            'role' => 'admin',
        ]);

        // Можно добавить тестовые данные
        if (app()->environment('local')) {
            $this->call(TestDataSeeder::class);
        }
    }
}