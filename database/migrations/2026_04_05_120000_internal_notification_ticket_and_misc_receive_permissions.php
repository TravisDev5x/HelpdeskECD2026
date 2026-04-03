<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Permisos modulares para el resto de tipos de InternalUserNotification (tickets y avisos propios).
 */
return new class extends Migration
{
    private const NEW_PERMISSIONS = [
        'receive internal notification ticket assigned',
        'receive internal notification ticket resolved',
        'receive internal notification ticket closed',
        'receive internal notification password expiring soon',
        'receive internal notification info',
    ];

    public function up(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $guard = 'web';

        foreach (self::NEW_PERMISSIONS as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
        }

        $admin = Role::where('name', 'Admin')->first();
        if ($admin) {
            foreach (self::NEW_PERMISSIONS as $name) {
                $p = Permission::where('name', $name)->where('guard_name', $guard)->first();
                if ($p && ! $admin->hasPermissionTo($p)) {
                    $admin->givePermissionTo($p);
                }
            }
        }

        $assigned = Permission::where('name', 'receive internal notification ticket assigned')->where('guard_name', $guard)->first();
        $resolved = Permission::where('name', 'receive internal notification ticket resolved')->where('guard_name', $guard)->first();
        $closed = Permission::where('name', 'receive internal notification ticket closed')->where('guard_name', $guard)->first();
        $pwdSoon = Permission::where('name', 'receive internal notification password expiring soon')->where('guard_name', $guard)->first();

        foreach (Role::query()->cursor() as $role) {
            if ($role->name === 'Admin' || $role->name === 'Suspendido') {
                continue;
            }

            if ($pwdSoon && ! $role->hasPermissionTo($pwdSoon)) {
                $role->givePermissionTo($pwdSoon);
            }

            if ($assigned && $role->hasPermissionTo('update service') && ! $role->hasPermissionTo($assigned)) {
                $role->givePermissionTo($assigned);
            }

            if ($role->hasPermissionTo('create service') || $role->hasPermissionTo('read services')) {
                if ($resolved && ! $role->hasPermissionTo($resolved)) {
                    $role->givePermissionTo($resolved);
                }
                if ($closed && ! $role->hasPermissionTo($closed)) {
                    $role->givePermissionTo($closed);
                }
            }
        }
    }

    public function down(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        foreach (self::NEW_PERMISSIONS as $name) {
            Permission::where('name', $name)->where('guard_name', 'web')->delete();
        }
    }
};
