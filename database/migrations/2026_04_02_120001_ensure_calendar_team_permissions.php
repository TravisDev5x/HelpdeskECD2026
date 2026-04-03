<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Calendario de equipo: quién ve y quién gestiona eventos compartidos (matriz de roles).
 */
return new class extends Migration
{
    private const PERMISSIONS = [
        'read team calendar',
        'manage team calendar',
    ];

    public function up(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $guard = config('auth.defaults.guard', 'web');

        foreach (self::PERMISSIONS as $name) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => $guard]
            );
        }

        $admin = Role::where('name', 'Admin')->where('guard_name', $guard)->first();
        if ($admin) {
            foreach (self::PERMISSIONS as $name) {
                $p = Permission::where('name', $name)->where('guard_name', $guard)->first();
                if ($p && ! $admin->hasPermissionTo($p)) {
                    $admin->givePermissionTo($p);
                }
            }
        }
    }

    public function down(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $guard = config('auth.defaults.guard', 'web');
        foreach (self::PERMISSIONS as $name) {
            Permission::where('name', $name)->where('guard_name', $guard)->delete();
        }
    }
};
