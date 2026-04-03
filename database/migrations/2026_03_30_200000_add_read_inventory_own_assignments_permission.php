<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Inventario V2: ver solo los activos asignados al usuario autenticado (sin read inventory completo).
 * Se otorga a todos los roles para equiparar el criterio de «lista de asignación» del inventario V1.
 */
return new class extends Migration
{
    public function up(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $guard = config('auth.defaults.guard', 'web');
        $permission = Permission::firstOrCreate(
            ['name' => 'read inventory own assignments', 'guard_name' => $guard]
        );

        foreach (Role::query()->where('guard_name', $guard)->get() as $role) {
            if (! $role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }
    }

    public function down(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        Permission::where('name', 'read inventory own assignments')->delete();
    }
};
