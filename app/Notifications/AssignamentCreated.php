<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignamentCreated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($product, $user)
    {
      $this->product = $product;
      $this->user = $user;
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
                    ->subject('Equipo asignado')
                    ->greeting('Hola '.$this->user->name)
                    ->line('Se te ha asignado un nuevo equipo.')
                    ->line('Nombre: ' . $this->product->name)
                    ->line('Serie: ' . $this->product->serie)
                    ->line('Marca: ' . $this->product->marca)
                    ->action('Ver equipos asignados', url('admin/assignments/'.$this->user->id))
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
