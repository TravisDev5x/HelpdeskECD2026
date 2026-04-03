<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Notificación interna (tabla `notifications`, canal database).
 * Sin cola: debe persistir al instante para que la campana y el listado funcionen
 * aunque no haya worker de colas (entornos locales o QUEUE_CONNECTION≠sync).
 */
class InternalUserNotification extends Notification
{
    public function __construct(
        protected string $title,
        protected string $message,
        protected ?string $url = null,
        protected string $type = 'info'
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'type' => $this->type,
        ];
    }
}
