<?php

namespace App\Support\Notifications;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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
        return User::query()
            ->permission($permission)
            ->whereNull('deleted_at')
            ->get();
    }

    /**
     * @param  (callable(Builder): void)|null  $constraints
     * @return Collection<int, User>
     */
    public static function withPermissionScoped(string $permission, ?callable $constraints = null): Collection
    {
        $q = User::query()
            ->permission($permission)
            ->whereNull('deleted_at');

        if ($constraints !== null) {
            $constraints($q);
        }

        return $q->get();
    }
}
