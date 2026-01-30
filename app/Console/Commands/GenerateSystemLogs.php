<?php

namespace App\Console\Commands;

use App\Services\Analytics\SystemMetricsService;
use Illuminate\Console\Command;

class GenerateSystemLogs extends Command
{
    protected $signature = 'ctf:generate-logs';
    protected $description = 'Генерирует системные логи для аналитики';

    public function handle(SystemMetricsService $metricsService)
    {
        $this->info('Генерация системных логов...');

        try {
            $metrics = $metricsService->collectMetrics();
            
            $this->info('Метрики собраны:');
            $this->line("CPU Usage: {$metrics['cpu']['usage']}");
            $this->line("Memory Usage: {$metrics['memory']['percentage']}%");
            $this->line("Active Users: {$metrics['users']['active']}");
            $this->line("Active Teams: {$metrics['users']['active_teams']}");

            $this->info('Логи успешно сгенерированы.');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Ошибка при генерации логов: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}