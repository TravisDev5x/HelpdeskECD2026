<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;

final class InventoryV2FilterPermissions
{
    public static function permissionFor(string $filterKey): ?string
    {
        $p = config('inventory_v2_filter_permissions.filters.'.$filterKey);

        return is_string($p) && $p !== '' ? $p : null;
    }

    public static function userMayUse(?Authenticatable $user, string $filterKey): bool
    {
        if (! $user) {
            return false;
        }

        $perm = self::permissionFor($filterKey);

        if ($perm === null) {
            return true;
        }

        return $user->can($perm);
    }

    /**
     * Valor efectivo del filtro para consultas (ignora si no hay permiso).
     *
     * @template T
     *
     * @param  T  $incoming
     * @return T|string
     */
    public static function effectiveScalar(?Authenticatable $user, string $filterKey, $incoming, string $empty = '')
    {
        if (! self::userMayUse($user, $filterKey)) {
            return $empty;
        }

        return $incoming;
    }
}
