<?php

namespace App\Notifications;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $invitation;

    public function __construct(Invitation $invitation)
    {
        $this->invitation = $invitation;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $team = $this->invitation->team;
        $expiresAt = $this->invitation->expires_at->format('d.m.Y H:i');
        $acceptUrl = route('invitation.accept', ['token' => $this->invitation->token]);

        return (new MailMessage)
            ->subject('🤝 Приглашение в команду CTF')
            ->greeting('Здравствуйте!')
            ->line('Вы получили приглашение присоединиться к команде на платформе CTF.')
            ->line('**Команда:** ' . $team->name)
            ->line('**Роль в команде:** ' . ($this->invitation->role === 'captain' ? 'Капитан' : 'Участник'))
            ->line('**Приглашение действует до:** ' . $expiresAt)
            ->action('Принять приглашение', $acceptUrl)
            ->line('Если вы не регистрировались на нашей платформе, система автоматически создаст учетную запись при принятии приглашения.')
            ->line('Если это письмо пришло вам по ошибке, просто проигнорируйте его.')
            ->salutation('С уважением, команда CTF Platform');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'team_invitation',
            'invitation_id' => $this->invitation->id,
            'team_id' => $this->invitation->team_id,
            'team_name' => $this->invitation->team->name,
            'role' => $this->invitation->role,
            'expires_at' => $this->invitation->expires_at->toDateTimeString(),
            'accept_url' => route('invitation.accept', ['token' => $this->invitation->token]),
        ];
    }
}