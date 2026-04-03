<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Permisos modulares para recibir notificaciones internas (canal database / campana).
 * Sustituye destinatarios fijos por rol (Admin / Soporte) en el código de aplicación.
 */
return new class extends Migration
{
    private const PERMISSIONS = [
        'receive internal notification ticket created',
        'receive internal notification user login',
        'receive internal notification password support',
        'receive internal notification user missing email',
    ];

    /** Permisos que antes recibían rol Soporte (además de Admin). */
    private const SOPORTE_SUBSET = [
        'receive internal notification password support',
        'receive internal notification user missing email',
    ];

    public function up(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        foreach (self::PERMISSIONS as $name) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web']
            );
        }

        $admin = Role::where('name', 'Admin')->first();
        if ($admin) {
            foreach (self::PERMISSIONS as $name) {
                $p = Permission::where('name', $name)->first();
                if ($p && ! $admin->hasPermissionTo($p)) {
                    $admin->givePermissionTo($p);
                }
            }
        }

        $soporte = Role::where('name', 'Soporte')->first();
        if ($soporte) {
            foreach (self::SOPORTE_SUBSET as $name) {
                $p = Permission::where('name', $name)->first();
                if ($p && ! $soporte->hasPermissionTo($p)) {
                    $soporte->givePermissionTo($p);
                }
            }
        }
    }

    public function down(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        foreach (self::PERMISSIONS as $name) {
            Permission::where('name', $name)->delete();
        }
    }
};
