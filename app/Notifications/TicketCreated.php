<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($service, $failure)
    {
      $this->service = $service;
      $this->failure = $failure;
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
                    ->subject('Nuevo ticket ' . $this->service->id)
                    ->greeting('Hola')
                    ->line('Se levanto un nuevo ticket')
                    ->line('ID: ' . $this->service->id)
                    ->line('Falla: ' . $this->failure)
                    ->line('Descripción: ' . $this->service->description)
                    ->action('Acción de notificación', url('/home?id='.$this->service->id))
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
