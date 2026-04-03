<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Ruta post-login / post-registro / post-reset.
     * Accesible para cualquier usuario autenticado (sin permiso extra).
     */
    public const HOME = '/admin/profile';
}
