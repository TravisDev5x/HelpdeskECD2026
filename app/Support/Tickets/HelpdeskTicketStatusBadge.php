<?php

namespace App\Support\Tickets;

/**
 * Presentación de estatus en la mesa de ayuda: textos tal cual en BD y colores
 * alineados con KPIs (indicadores) y, cuando aplica, con el punto SLA.
 */
final class HelpdeskTicketStatusBadge
{
    /**
     * Colores Bootstrap 4 (suffix tras badge-), coherentes con {@see \App\Support\DashboardTicketStats}.
     */
    public static function colorForStatus(?string $status): string
    {
        $s = trim((string) $status);

        return match ($s) {
            TicketStatus::PENDIENTE => 'danger',
            TicketStatus::SEGUIMIENTO => 'warning',
            TicketStatus::FINALIZADO => 'success',
            TicketStatus::TICKET_ERRONEO => 'secondary',
            'Abierto' => 'success',
            'En proceso' => 'warning',
            'Cerrado' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Misma lógica que el botón SLA de la tabla (antigüedad desde creación).
     */
    public static function colorForSlaMinutes(?int $minutesElapsed): ?string
    {
        if ($minutesElapsed === null) {
            return null;
        }

        if ($minutesElapsed < 15) {
            return 'success';
        }
        if ($minutesElapsed < 30) {
            return 'warning';
        }
        if ($minutesElapsed < 1440) {
            return 'danger';
        }

        return 'dark';
    }

    /**
     * @param  bool  $unifyWithSla  fila con indicador SLA (no propio, no modal activo crítico pendiente).
     */
    public static function resolveColor(?string $status, ?int $minutesElapsed, bool $unifyWithSla): string
    {
        if ($unifyWithSla) {
            $sla = self::colorForSlaMinutes($minutesElapsed);
            if ($sla !== null) {
                return $sla;
            }
        }

        if (trim((string) $status) === '') {
            return 'secondary';
        }

        return self::colorForStatus($status);
    }

    public static function badgeClasses(string $bootstrapColor): string
    {
        $base = 'badge badge-'.$bootstrapColor;
        if ($bootstrapColor === 'warning') {
            $base .= ' text-dark';
        }

        return $base;
    }
}
