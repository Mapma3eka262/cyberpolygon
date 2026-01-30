<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $task;
    public $team;
    public $isAdminNotification;

    public function __construct(Task $task, Team $team, $isAdminNotification = false)
    {
        $this->task = $task;
        $this->team = $team;
        $this->isAdminNotification = $isAdminNotification;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        if ($this->isAdminNotification) {
            return $this->getAdminMailMessage();
        }

        return (new MailMessage)
            ->subject('🎯 Новое задание назначено вашей команде!')
            ->greeting('Здравствуйте, ' . $notifiable->name . '!')
            ->line('Вашей команде "' . $this->team->name . '" назначено новое задание.')
            ->line('**Название задания:** ' . $this->task->name)
            ->line('**Продолжительность:** ' . $this->task->duration_minutes . ' минут')
            ->line('**Целевая машина:** ' . ($this->team->target_ip ?: $this->task->target_ip_subnet))
            ->line('**Баллы за флаги:** Первый - ' . $this->task->flag1_points . ', Второй - ' . $this->task->flag2_points)
            ->action('Перейти к заданию', url('/arena'))
            ->line('Удачи в решении задачи!')
            ->salutation('С уважением, команда CTF Platform');
    }

    private function getAdminMailMessage()
    {
        return (new MailMessage)
            ->subject('📋 Задание назначено команде')
            ->line('Задание "' . $this->task->name . '" было назначено команде "' . $this->team->name . '".')
            ->line('**Время начала:** ' . now()->format('d.m.Y H:i:s'))
            ->line('**Продолжительность:** ' . $this->task->duration_minutes . ' минут')
            ->line('**Количество участников:** ' . $this->team->members()->count())
            ->salutation('Система CTF Platform');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => $this->isAdminNotification ? 'task_assigned_admin' : 'task_assigned',
            'task_id' => $this->task->id,
            'task_name' => $this->task->name,
            'team_id' => $this->team->id,
            'team_name' => $this->team->name,
            'duration_minutes' => $this->task->duration_minutes,
            'target_ip' => $this->team->target_ip ?: $this->task->target_ip_subnet,
            'message' => $this->isAdminNotification 
                ? "Задание '{$this->task->name}' назначено команде '{$this->team->name}'"
                : "Вашей команде назначено задание '{$this->task->name}'",
            'timestamp' => now()->toDateTimeString(),
        ];
    }

    public function toDatabase($notifiable)
    {
        return $this->toArray($notifiable);
    }
}