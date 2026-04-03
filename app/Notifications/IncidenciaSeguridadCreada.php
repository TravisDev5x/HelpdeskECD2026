<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IncidenciaSeguridadCreada extends Notification
{
    use Queueable;
    public $create;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($create)
    {
        $this->create = $create;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {

        return (new MailMessage)
            ->subject('Nuevo reporte de ciberseguridad y  protección de datos')
            ->greeting('Hola')
            ->line('Se levanto un nuevo reporte')
            ->line('ID: ' . $this->create->id)
            ->line('Reporte: ' . $this->create->categoria->contenido)
            ->line('Comentario: ' . $this->create->comentario)
            ->action('Ver Reporte', url('/admin/ciberseguridad/incidencias'))
            ->cc([
                'jorgel@ecd.mx',
                'etalavera@ecd.mx',
                'rtellez@ecd.mx',
                'jireyes@ecd.mx',
                'incidentes@ecd.mx',
            ])
            ->from('helpdesk@ecd.com')
            ->salutation('Saludos.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
