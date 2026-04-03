<?php

namespace App\Support\Tickets;

use App\Models\Service;
use App\Models\User;

/**
 * Comentarios del solicitante sobre su propio ticket (sin usar Policy: Admin tiene before() que autorizaría cualquier ticket).
 */
final class TicketRequesterNote
{
    public static function userMayAddNote(User $user, Service $service): bool
    {
        if (! $user->can('view', $service)) {
            return false;
        }

        if (TicketStatus::isClosed((string) ($service->status ?? ''))) {
            return false;
        }

        if ($service->user_id === null) {
            return false;
        }

        return (int) $service->user_id === (int) $user->id;
    }
}
