<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class InventoryV2PermissionsSeeder extends Seeder
{
    public function run()
    {
        app()['cache']->forget('spatie.permission.cache');
        $guard = config('auth.defaults.guard', 'web');

        // Convención modular inventario V2: read/edit operativos; manage * = catálogos;
        // read inventory monitor = KPIs, alertas y exportes del monitor (independiente de read inventory).
        $basePermissions = [
            'read inventory',
            'read inventory assignment history',
            'edit inventory',
            'read inventory monitor',
            'read inventory own assignments',
            'manage inventory config',
            'manage inventory labels',
            'manage inventory maintenance catalogs',
        ];
        $filterPermissions = array_values(config('inventory_v2_filter_permissions.filters', []));
        $permissions = array_values(array_unique(array_merge($basePermissions, $filterPermissions)));

        foreach ($permissions as $name) {
            if (is_string($name) && $name !== '') {
                Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
            }
        }

        $admin = Role::where('name', 'Admin')->first();
        if ($admin) {
            $admin->givePermissionTo($permissions);
        }

        $rolesWithInventory = ['Soporte', 'Infraestructura', 'Mantenimiento', 'Auditor'];
        foreach ($rolesWithInventory as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo('read inventory');
                $role->givePermissionTo('edit inventory');
            }
        }

        $own = Permission::findByName('read inventory own assignments', config('auth.defaults.guard', 'web'));
        foreach (Role::query()->where('guard_name', config('auth.defaults.guard', 'web'))->get() as $role) {
            if (! $role->hasPermissionTo($own)) {
                $role->givePermissionTo($own);
            }
        }
    }
}
