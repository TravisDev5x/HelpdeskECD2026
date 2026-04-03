<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Fase 3 — Permisos usados en menú / rutas pero no siempre presentes en seeds antiguos.
 */
return new class extends Migration
{
    private const PERMISSIONS = [
        'modulo.did',
        'modulo.ubicaciones',
        'read calendar',
    ];

    public function up(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        foreach (self::PERMISSIONS as $name) {
            Permission::firstOrCreate(['name' => $name]);
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
    }

    public function down(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        foreach (self::PERMISSIONS as $name) {
            Permission::where('name', $name)->delete();
        }
    }
};
