<?php

namespace App\Support;

use App\Models\AssetUser;
use App\Models\Service;
use App\Models\User;
use App\Support\Tickets\TicketStatus;

/**
 * KPIs del panel de tickets: una sola base de consulta (scope allowed) y agregaciones ligeras.
 */
class DashboardTicketStats
{
    /**
     * @return array{generados: int, pendientes: int, seguimientos: int, finalizados: int, ticketMal: int, count: int, countChecks: int}
     */
    public static function counts(): array
    {
        $base = Service::allowed();

        $generados = (clone $base)->count();
        $pendientes = (clone $base)->where('status', TicketStatus::PENDIENTE)->count();
        $seguimientos = (clone $base)->where('status', TicketStatus::SEGUIMIENTO)->count();
        $finalizados = (clone $base)->where('status', TicketStatus::FINALIZADO)->count();
        $ticketMal = (clone $base)->where('status', TicketStatus::TICKET_ERRONEO)->count();

        $count = User::where('certification', '0')->count();

        $countChecks = User::query()
            ->where('certification', '1')
            ->whereNotIn('id', AssetUser::query()->select('user_id')->distinct())
            ->count();

        return compact('generados', 'pendientes', 'seguimientos', 'finalizados', 'ticketMal', 'count', 'countChecks');
    }
}
