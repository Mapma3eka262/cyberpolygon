<?php

namespace App\Console\Commands;

use App\Models\FlagAttempt;
use App\Models\EventLog;
use Illuminate\Console\Command;

class CleanOldAttempts extends Command
{
    protected $signature = 'ctf:clean-old-data {--days=30 : Удалить данные старше N дней}';
    protected $description = 'Очищает старые данные для оптимизации базы';

    public function handle()
    {
        $days = $this->option('days');
        $date = now()->subDays($days);

        $this->info("Удаление данных старше {$days} дней...");

        // Удаляем старые попытки флагов
        $attemptsDeleted = FlagAttempt::where('created_at', '<', $date)->delete();
        $this->info("Удалено попыток флагов: {$attemptsDeleted}");

        // Удаляем старые логи событий
        $logsDeleted = EventLog::where('created_at', '<', $date)->delete();
        $this->info("Удалено логов событий: {$logsDeleted}");

        // Удаляем старые системные логи (оставляем только за последние 7 дней для аналитики)
        $systemLogsDeleted = \App\Models\SystemLog::where('created_at', '<', now()->subDays(7))->delete();
        $this->info("Удалено системных логов: {$systemLogsDeleted}");

        // Оптимизируем таблицы
        if ($attemptsDeleted > 0) {
            \DB::statement('OPTIMIZE TABLE flag_attempts');
            $this->info('Таблица flag_attempts оптимизирована.');
        }

        if ($logsDeleted > 0) {
            \DB::statement('OPTIMIZE TABLE event_logs');
            $this->info('Таблица event_logs оптимизирована.');
        }

        $this->info('Очистка данных завершена.');

        return Command::SUCCESS;
    }
}