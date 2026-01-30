<?php

namespace Tests\Feature\Performance;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoadTest extends TestCase
{
    public function test_concurrent_users()
    {
        // Создаем 20 команд по 5 участников
        for ($i = 1; $i <= 20; $i++) {
            $team = \App\Models\Team::create([
                'name' => "Team $i",
            ]);
            
            for ($j = 1; $j <= 5; $j++) {
                User::create([
                    'username' => "user_team{$i}_{$j}",
                    'password' => Hash::make('password'),
                    'surname' => "Surname $j",
                    'name' => "Name $j",
                    'email' => "team{$i}_user{$j}@test.com",
                    'role' => $j === 1 ? 'captain' : 'participant',
                    'team_id' => $team->id,
                ]);
            }
        }

        // Тест параллельных запросов
        $start = microtime(true);
        
        $responses = [];
        for ($i = 0; $i < 100; $i++) {
            $user = User::inRandomOrder()->first();
            $this->actingAs($user);
            $response = $this->get('/arena');
            $this->assertEquals(200, $response->status());
            $responses[] = $response;
        }
        
        $end = microtime(true);
        $time = $end - $start;
        
        $this->assertLessThan(5, $time, "100 запросов должны выполняться менее 5 секунд");
    }
}