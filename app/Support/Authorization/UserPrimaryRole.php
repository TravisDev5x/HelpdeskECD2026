<?php

namespace App\Support\Authorization;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Rol "principal" del usuario (primer rol de Spatie), mismo criterio que el código legado.
 * Centraliza el acceso para jobs, exportaciones, Livewire y controladores sin repetir roles->first().
 */
final class UserPrimaryRole
{
    /**
     * Nombre del primer rol, o null si no hay usuario o no tiene roles.
     */
    public static function name(?User $user = null): ?string
    {
        $user = $user ?? Auth::user();

        return $user?->roles->first()?->name;
    }
}
