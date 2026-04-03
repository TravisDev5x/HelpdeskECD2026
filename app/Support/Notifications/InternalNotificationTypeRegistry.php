<?php

namespace App\Support\Notifications;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Tipos almacenados en data.type de {@see \App\Notifications\InternalUserNotification} y filtros del panel.
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

    /** Ocultos en listados para usuarios sin rol Admin (p. ej. avisos de login). */
    public const HIDDEN_UNLESS_ADMIN = [
        'user_login',
    ];

    public static function normalizedFilter(?string $type): ?string
    {
        if ($type === null || $type === '') {
            return null;
        }

        return in_array($type, self::FILTERABLE, true) ? $type : null;
    }

    /**
     * @param  Relation|\Illuminate\Database\Eloquent\Builder  $query  p. ej. MorphMany de {@see \Illuminate\Notifications\Notifiable::notifications()}
     */
    public static function applyHiddenTypesForNonAdmin(Relation|Builder $query): void
    {
        foreach (self::HIDDEN_UNLESS_ADMIN as $hiddenType) {
            $query->where('data', 'not like', '%"type":"'.$hiddenType.'"%');
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
