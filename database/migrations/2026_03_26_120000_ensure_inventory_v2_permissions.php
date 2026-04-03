<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Inventario V2: permisos usados en Livewire (read/edit/manage config) deben existir aunque
 * no se haya ejecutado InventoryV2PermissionsSeeder en este entorno.
 */
return new class extends Migration
{
    private const PERMISSIONS = [
        'read inventory',
        'edit inventory',
        'manage inventory config',
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

        $rolesReadEdit = ['Soporte', 'Infraestructura', 'Mantenimiento', 'Auditor'];
        foreach ($rolesReadEdit as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if (! $role) {
                continue;
            }
            foreach (['read inventory', 'edit inventory'] as $name) {
                $p = Permission::where('name', $name)->first();
                if ($p && ! $role->hasPermissionTo($p)) {
                    $role->givePermissionTo($p);
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
