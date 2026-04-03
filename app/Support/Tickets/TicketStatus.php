<?php

namespace App\Support\Tickets;

final class TicketStatus
{
    const PENDIENTE = 'Pendiente';
    const SEGUIMIENTO = 'Seguimiento';
    const FINALIZADO = 'Finalizado';
    const TICKET_ERRONEO = 'Ticket erróneo';

    public static function all(): array
    {
        return [
            self::PENDIENTE,
            self::SEGUIMIENTO,
            self::FINALIZADO,
            self::TICKET_ERRONEO,
        ];
    }

    public static function closed(): array
    {
        return [self::FINALIZADO, self::TICKET_ERRONEO];
    }

    public static function isClosed(string $status): bool
    {
        return in_array($status, self::closed(), true);
    }

    public static function badgeColor(string $status): string
    {
        return match ($status) {
            self::PENDIENTE => 'danger',
            self::SEGUIMIENTO => 'warning',
            self::FINALIZADO => 'success',
            self::TICKET_ERRONEO => 'secondary',
            default => 'secondary',
        };
    }

    public static function label(string $status): string
    {
        return match ($status) {
            self::PENDIENTE => 'Pendiente',
            self::SEGUIMIENTO => 'Seguimiento',
            self::FINALIZADO => 'Finalizado',
            self::TICKET_ERRONEO => 'Ticket erróneo',
            default => $status,
        };
    }
}
