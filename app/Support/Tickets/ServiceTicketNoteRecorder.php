<?php

namespace App\Support\Tickets;

use App\Models\Service;
use App\Models\ServiceTicketNote;
use App\Models\User;
use App\Notifications\InternalUserNotification;
use App\Support\Notifications\InternalNotificationRecipients;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class ServiceTicketNoteRecorder
{
    public static function addStaffNote(Service $service, User $author, string $body, string $visibility): ServiceTicketNote
    {
        $vis = $visibility === ServiceTicketNote::VIS_REQUESTER_VISIBLE
            ? ServiceTicketNote::VIS_REQUESTER_VISIBLE
            : ServiceTicketNote::VIS_INTERNAL;

        return ServiceTicketNote::query()->create([
            'service_id' => $service->id,
            'user_id' => $author->id,
            'body' => $body,
            'visibility' => $vis,
            'notify_support' => false,
        ]);
    }

    public static function addRequesterNote(Service $service, User $author, string $body, bool $notifySupport): ServiceTicketNote
    {
        $note = ServiceTicketNote::query()->create([
            'service_id' => $service->id,
            'user_id' => $author->id,
            'body' => $body,
            'visibility' => ServiceTicketNote::VIS_REQUESTER_VISIBLE,
            'notify_support' => $notifySupport,
        ]);

        if ($notifySupport) {
            self::notifySupportStaff($service, $author, $body);
        }

        return $note;
    }

    private static function notifySupportStaff(Service $service, User $author, string $preview): void
    {
        $permission = 'receive internal notification ticket requester alert';

        $recipients = InternalNotificationRecipients::withPermission($permission)
            ->filter(static fn (User $u): bool => (int) $u->id !== (int) $author->id)
            ->values();

        if ($recipients->isEmpty()) {
            return;
        }

        $url = route('home').'?id='.$service->id;
        $title = 'Alerta del solicitante — ticket #'.$service->id;

        foreach ($recipients as $recipient) {
            try {
                $recipient->notify(new InternalUserNotification(
                    $title,
                    Str::limit(trim($preview), 200),
                    $url,
                    'ticket_requester_alert'
                ));
            } catch (\Throwable $e) {
                Log::error('No se pudo guardar notificación de alerta de ticket (solicitante)', [
                    'service_id' => $service->id,
                    'recipient_id' => $recipient->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
