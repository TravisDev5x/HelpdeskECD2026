<?php

namespace App\Support\Tickets;

/**
 * Mapeo rol → áreas (area_id en failure.area) para visibilidad de tickets.
 * Debe mantenerse alineado con la lógica histórica de mesa de ayuda.
 */
final class TicketRoleAreaMap
{
    /**
     * @return array<string, list<int>>
     */
    public static function all(): array
    {
        return [
            'Soporte' => [1],
            'Metricas' => [2],
            'Telecomunicaciones' => [3],
            'Mantenimiento' => [4, 5, 6],
            'Proyectos' => [8],
            'Infraestructura' => [9],
        ];
    }

    /**
     * @return list<int>|null null si el rol no tiene áreas dedicadas (se usa solo "mis tickets").
     */
    public static function areaIdsForRole(string $roleName): ?array
    {
        return self::all()[$roleName] ?? null;
    }
}
