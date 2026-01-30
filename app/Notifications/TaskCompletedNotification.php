<?php

namespace App\Notifications;

use App\Models\TeamTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $teamTask;
    public $isAdminNotification;

    public function __construct(TeamTask $teamTask, $isAdminNotification = false)
    {
        $this->teamTask = $teamTask;
        $this->isAdminNotification = $isAdminNotification;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $team = $this->teamTask->team;
        $task = $this->teamTask->task;
        $flagsFound = ($this->teamTask->flag1_found ? 1 : 0) + ($this->teamTask->flag2_found ? 1 : 0);

        if ($this->isAdminNotification) {
            return $this->getAdminMailMessage($team, $task, $flagsFound);
        }

        $completionType = $this->teamTask->completed_at 
            ? 'успешно завершено' 
            : 'время истекло';

        return (new MailMessage)
            ->subject($this->teamTask->completed_at ? '✅ Задание завершено!' : '⏰ Время истекло')
            ->greeting('Уведомление о задании')
            ->line('Задание вашей команды "' . $task->name . '" было ' . $completionType . '.')
            ->line('**Команда:** ' . $team->name)
            ->line('**Задание:** ' . $task->name)
            ->line('**Найдено флагов:** ' . $flagsFound . '/2')
            ->line('**Заработано баллов:** ' . $this->teamTask->score)
            ->line('**Неправильных попыток:** ' . $this->teamTask->wrong_attempts)
            ->line('**Время выполнения:** ' . $this->teamTask->started_at->diffForHumans($this->teamTask->completed_at ?: now(), true))
            ->action('Посмотреть результаты', url('/arena'))
            ->line('Продолжайте участие в других заданиях!')
            ->salutation('С уважением, команда CTF Platform');
    }

    private function getAdminMailMessage($team, $task, $flagsFound)
    {
        $completionType = $this->teamTask->completed_at 
            ? 'завершено командой' 
            : 'время истекло';

        return (new MailMessage)
            ->subject('📊 Задание ' . $completionType)
            ->line('Задание "' . $task->name . '" было ' . $completionType . ' для команды "' . $team->name . '".')
            ->line('**Результаты:**')
            ->line('- Найдено флагов: ' . $flagsFound . '/2')
            ->line('- Заработано баллов: ' . $this->teamTask->score)
            ->line('- Неправильных попыток: ' . $this->teamTask->wrong_attempts)
            ->line('**Временные метки:**')
            ->line('- Начало: ' . $this->teamTask->started_at->format('d.m.Y H:i:s'))
            ->line('- Завершение: ' . ($this->teamTask->completed_at ? $this->teamTask->completed_at->format('d.m.Y H:i:s') : 'по таймауту'))
            ->salutation('Система мониторинга CTF Platform');
    }

    public function toArray($notifiable)
    {
        $team = $this->teamTask->team;
        $task = $this->teamTask->task;
        $flagsFound = ($this->teamTask->flag1_found ? 1 : 0) + ($this->teamTask->flag2_found ? 1 : 0);
        $completionType = $this->teamTask->completed_at ? 'completed' : 'expired';

        return [
            'type' => $this->isAdminNotification ? 'task_completed_admin' : 'task_completed',
            'team_task_id' => $this->teamTask->id,
            'team_id' => $team->id,
            'team_name' => $team->name,
            'task_id' => $task->id,
            'task_name' => $task->name,
            'completion_type' => $completionType,
            'flags_found' => $flagsFound,
            'score' => $this->teamTask->score,
            'wrong_attempts' => $this->teamTask->wrong_attempts,
            'started_at' => $this->teamTask->started_at->toDateTimeString(),
            'completed_at' => $this->teamTask->completed_at ? $this->teamTask->completed_at->toDateTimeString() : null,
            'message' => $this->isAdminNotification
                ? "Задание '{$task->name}' {$completionType} для команды '{$team->name}'"
                : "Ваша команда {$completionType} задание '{$task->name}'",
            'timestamp' => now()->toDateTimeString(),
        ];
    }

    public function toDatabase($notifiable)
    {
        return $this->toArray($notifiable);
    }
}