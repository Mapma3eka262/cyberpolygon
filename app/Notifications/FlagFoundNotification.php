<?php

namespace App\Notifications;

use App\Models\FlagAttempt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FlagFoundNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $attempt;
    public $isAdminNotification;

    public function __construct(FlagAttempt $attempt, $isAdminNotification = false)
    {
        $this->attempt = $attempt;
        $this->isAdminNotification = $isAdminNotification;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $team = $this->attempt->teamTask->team;
        $task = $this->attempt->teamTask->task;
        
        if ($this->isAdminNotification) {
            return $this->getAdminMailMessage($team, $task);
        }

        $points = $this->attempt->flag_type === 'flag1' 
            ? $task->flag1_points 
            : $task->flag2_points;

        return (new MailMessage)
            ->subject('🎉 Флаг найден!')
            ->greeting('Поздравляем, ' . $notifiable->name . '!')
            ->line('Участник вашей команды успешно нашел флаг в задании.')
            ->line('**Команда:** ' . $team->name)
            ->line('**Задание:** ' . $task->name)
            ->line('**Тип флага:** ' . ($this->attempt->flag_type === 'flag1' ? 'Первый флаг' : 'Второй флаг'))
            ->line('**Начислено баллов:** ' . $points)
            ->line('**Текущий счет команды:** ' . $team->score)
            ->action('Продолжить соревнование', url('/arena'))
            ->line('Так держать! Продолжайте в том же духе!')
            ->salutation('С уважением, команда CTF Platform');
    }

    private function getAdminMailMessage($team, $task)
    {
        $points = $this->attempt->flag_type === 'flag1' 
            ? $task->flag1_points 
            : $task->flag2_points;

        return (new MailMessage)
            ->subject('📊 Флаг найден командой')
            ->line('Команда "' . $team->name . '" нашла флаг в задании "' . $task->name . '".')
            ->line('**Тип флага:** ' . ($this->attempt->flag_type === 'flag1' ? 'Первый флаг' : 'Второй флаг'))
            ->line('**Начислено баллов:** ' . $points)
            ->line('**Новый счет команды:** ' . $team->score)
            ->line('**Участник:** ' . $this->attempt->user->full_name)
            ->line('**Время:** ' . $this->attempt->created_at->format('d.m.Y H:i:s'))
            ->salutation('Система мониторинга CTF Platform');
    }

    public function toArray($notifiable)
    {
        $team = $this->attempt->teamTask->team;
        $task = $this->attempt->teamTask->task;
        $points = $this->attempt->flag_type === 'flag1' 
            ? $task->flag1_points 
            : $task->flag2_points;

        return [
            'type' => $this->isAdminNotification ? 'flag_found_admin' : 'flag_found',
            'attempt_id' => $this->attempt->id,
            'user_id' => $this->attempt->user_id,
            'user_name' => $this->attempt->user->full_name,
            'team_id' => $team->id,
            'team_name' => $team->name,
            'task_id' => $task->id,
            'task_name' => $task->name,
            'flag_type' => $this->attempt->flag_type,
            'points' => $points,
            'new_team_score' => $team->score,
            'message' => $this->isAdminNotification
                ? "Команда '{$team->name}' нашла {$this->attempt->flag_type} в задании '{$task->name}'"
                : "Ваша команда нашла {$this->attempt->flag_type} в задании '{$task->name}'",
            'timestamp' => $this->attempt->created_at->toDateTimeString(),
        ];
    }

    public function toDatabase($notifiable)
    {
        return $this->toArray($notifiable);
    }
}