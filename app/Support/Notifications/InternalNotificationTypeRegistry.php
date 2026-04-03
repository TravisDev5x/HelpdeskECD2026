<?php

namespace App\Support\Notifications;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Tipos almacenados en data.type de {@see \App\Notifications\InternalUserNotification} y filtros del panel.
 *
 * Tipos con permiso: solo se entregan y muestran a usuarios con el permiso correspondiente (matriz de roles).
 */
final class InternalNotificationTypeRegistry
{
    /** Tipos permitidos en ?type del listado (lista cerrada). */
    public const FILTERABLE = [
        'ticket_created',
        'ticket_assigned',
        'ticket_resolved',
        'ticket_closed',
        'user_login',
        'password_support_request',
        'user_missing_email',
        'password_expiring_soon',
        'info',
    ];

    /**
     * Permiso Spatie requerido para recibir (y ver en campana/listado) notificaciones de cada tipo.
     * Tipos no listados: sin filtro por permiso (p. ej. ticket asignado al propio usuario).
     *
     * @var array<string, string>
     */
    public const TYPE_RECEIVE_PERMISSION = [
        'ticket_created' => 'receive internal notification ticket created',
        'ticket_assigned' => 'receive internal notification ticket assigned',
        'ticket_resolved' => 'receive internal notification ticket resolved',
        'ticket_closed' => 'receive internal notification ticket closed',
        'user_login' => 'receive internal notification user login',
        'password_support_request' => 'receive internal notification password support',
        'user_missing_email' => 'receive internal notification user missing email',
        'password_expiring_soon' => 'receive internal notification password expiring soon',
        'info' => 'receive internal notification info',
    ];

    public static function normalizedFilter(?string $type): ?string
    {
        if ($type === null || $type === '') {
            return null;
        }

        return in_array($type, self::FILTERABLE, true) ? $type : null;
    }

    /**
     * Oculta filas cuyo tipo exige un permiso que el usuario no tiene (defensa en profundidad).
     *
     * @param  Relation|\Illuminate\Database\Eloquent\Builder  $query
     */
    public static function applyVisibilityByPermissions(Relation|Builder $query, User $user): void
    {
        foreach (self::TYPE_RECEIVE_PERMISSION as $type => $permission) {
            if (! $user->can($permission)) {
                $query->where('data', 'not like', '%"type":"'.$type.'"%');
            }
        }
    }

    /**
     * @param  Relation|\Illuminate\Database\Eloquent\Builder  $query
     */
    public static function applyTypeFilter(Relation|Builder $query, ?string $type): void
    {
        $type = self::normalizedFilter($type);
        if ($type !== null) {
            $query->where('data', 'like', '%"type":"'.$type.'"%');
        }
    }
}
