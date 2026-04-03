<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetByAdmin extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $temporalPassword,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu contraseña ha sido restablecida')
            ->greeting('Hola ' . $notifiable->name)
            ->line('Un administrador ha restablecido tu contraseña de acceso al sistema HelpDesk.')
            ->line('Tu nueva contraseña temporal es:')
            ->line('**' . $this->temporalPassword . '**')
            ->line('Por seguridad, deberás cambiarla la próxima vez que inicies sesión.')
            ->action('Ir al HelpDesk', url('/'))
            ->salutation('Saludos.')
            ->from('helpdesk@ecd.com');
    }
}
