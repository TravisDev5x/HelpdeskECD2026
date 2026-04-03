<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Alerta del solicitante en nota de ticket: solo la reciben quienes tengan el permiso (campana / listado).
 */
return new class extends Migration
{
    private const PERMISSION = 'receive internal notification ticket requester alert';

    /** Por defecto: equipos de mesa de ayuda (incluye variantes de nombre de rol si existen en BD). */
    private const ROLE_NAMES = [
        'Soporte',
        'Infraestructura',
        'Telecomunicaciones',
        'Telecominicaciones',
        'Mantenimiento',
    ];

    public function up(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $guard = 'web';

        $perm = Permission::firstOrCreate(['name' => self::PERMISSION, 'guard_name' => $guard]);

        $admin = Role::where('name', 'Admin')->where('guard_name', $guard)->first();
        if ($admin && ! $admin->hasPermissionTo($perm)) {
            $admin->givePermissionTo($perm);
        }

        foreach (self::ROLE_NAMES as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();
            if ($role && ! $role->hasPermissionTo($perm)) {
                $role->givePermissionTo($perm);
            }
        }
    }

    public function down(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        Permission::where('name', self::PERMISSION)->where('guard_name', 'web')->delete();
    }
};
