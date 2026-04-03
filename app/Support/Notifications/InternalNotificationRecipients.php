<?php

namespace App\Support\Notifications;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

/**
 * Usuarios destinatarios de {@see InternalUserNotification} según permisos (matriz Spatie).
 */
final class InternalNotificationRecipients
{
    /**
     * @return Collection<int, User>
     */
    public static function withPermission(string $permission): Collection
    {
        return self::queryWithPermission($permission)
            ->whereNull('deleted_at')
            ->get();
    }

    /**
     * @param  (callable(Builder): void)|null  $constraints
     * @return Collection<int, User>
     */
    public static function withPermissionScoped(string $permission, ?callable $constraints = null): Collection
    {
        $q = self::queryWithPermission($permission)
            ->whereNull('deleted_at');

        if ($constraints !== null) {
            $constraints($q);
        }

        return $q->get();
    }

    /**
     * Equivalente al scope de Spatie sobre usuarios con el permiso (directo o vía rol),
     * sin usar {@see Builder::__call()} con el nombre `permission` (evita fallos si el scope no se registra en el builder).
     */
    private static function queryWithPermission(string $permission): Builder
    {
        $guard = (string) config('auth.defaults.guard', 'web');
        /** @var class-string<\Spatie\Permission\Contracts\Permission> $permissionClass */
        $permissionClass = config('permission.models.permission');

        try {
            $perm = $permissionClass::findByName($permission, $guard);
        } catch (PermissionDoesNotExist) {
            return User::query()->whereRaw('0 = 1');
        }

        $permTable = (new $permissionClass)->getTable();
        $permKey = $perm->getKey();

        return User::query()->where(function (Builder $q) use ($permTable, $permKey) {
            $q->whereHas('permissions', function (Builder $sub) use ($permTable, $permKey) {
                $sub->where($permTable.'.id', $permKey);
            })->orWhereHas('roles', function (Builder $sub) use ($permTable, $permKey) {
                $sub->whereHas('permissions', function (Builder $p) use ($permTable, $permKey) {
                    $p->where($permTable.'.id', $permKey);
                });
            });
        });
    }
}
