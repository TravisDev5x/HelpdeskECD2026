<?php

namespace App\Support\Tickets;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Visibilidad de tickets por rol: Admin ve todo; roles con mapa ven áreas o propios; el resto solo propios.
 * Extraído de HomeController::getServices y ServicesController::get_finalizados para un solo criterio.
 */
final class TicketQueryByRole
{
    /**
     * Relaciones eager comunes en listados de tickets.
     *
     * @return list<string>
     */
    public static function eagerRelationsForLists(): array
    {
        return ['failure', 'failure.area', 'responsable', 'user', 'user.department', 'user.campaign', 'user.position'];
    }

    /**
     * Restringe la consulta a los servicios que el usuario puede ver según rol (no aplica ampliación a Admin).
     */
    public static function applyUserVisibilityScope(Builder $query, User $user): void
    {
        $roleName = $user->roles->first()->name ?? '';

        if ($roleName === 'Admin') {
            return;
        }

        $usuario = $user->id;
        $areaIds = TicketRoleAreaMap::areaIdsForRole($roleName);

        if ($areaIds !== null) {
            $query->where(function ($q) use ($areaIds, $usuario) {
                $q->whereHas('failure.area', function ($q) use ($areaIds) {
                    $q->whereIn('area_id', $areaIds);
                })->orWhere('user_id', $usuario);
            });
        } else {
            $query->where('user_id', $usuario);
        }
    }

    /**
     * Query base con relaciones para listados; el llamador añade filtros de status/fecha.
     */
    public static function queryWithListRelations(): Builder
    {
        return Service::query()->with(self::eagerRelationsForLists());
    }

    /**
     * Comprueba si el usuario puede ver/actuar sobre un ticket concreto (misma regla que los listados).
     */
    public static function userCanAccessService(User $user, Service $service): bool
    {
        $query = Service::query()->whereKey($service->getKey());

        self::applyUserVisibilityScope($query, $user);

        return $query->exists();
    }
}
