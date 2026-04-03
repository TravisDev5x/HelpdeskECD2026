<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    private function guard(): string
    {
        return config('auth.defaults.guard', 'web');
    }

    private function perm(string $name): Permission
    {
        return Permission::firstOrCreate(
            ['name' => $name, 'guard_name' => $this->guard()]
        );
    }

    public function up(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $names = array_values(config('inventory_v2_filter_permissions.filters', []));
        $permissions = [];
        foreach ($names as $name) {
            $permissions[$name] = $this->perm($name);
        }

        $baseNames = [
            'use inventory filter search',
            'use inventory filter category',
            'use inventory filter status',
            'use inventory filter assignee',
            'use inventory filter sede',
            'use inventory filter assignee employment',
        ];

        $monitorNames = [
            'use inventory filter monitor range',
            'use inventory filter monitor dates',
            'use inventory filter monitor company',
            'use inventory filter monitor sede',
            'use inventory filter monitor search',
            'use inventory filter monitor event type',
        ];

        foreach (Role::query()->where('guard_name', $this->guard())->get() as $role) {
            if ($role->hasPermissionTo('read inventory')) {
                foreach ($baseNames as $n) {
                    if (isset($permissions[$n])) {
                        $role->givePermissionTo($permissions[$n]);
                    }
                }
            }

            if ($role->hasPermissionTo('read inventory monitor')) {
                foreach ($monitorNames as $n) {
                    if (isset($permissions[$n])) {
                        $role->givePermissionTo($permissions[$n]);
                    }
                }
            }

            if (
                $role->hasPermissionTo('read inventory own assignments')
                && ! $role->hasPermissionTo('read inventory')
                && isset($permissions['use inventory filter search'])
            ) {
                $role->givePermissionTo($permissions['use inventory filter search']);
            }
        }

        $admin = Role::findByName('Admin', $this->guard());
        if ($admin) {
            foreach ($permissions as $p) {
                if (! $admin->hasPermissionTo($p)) {
                    $admin->givePermissionTo($p);
                }
            }
        }
    }

    public function down(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        foreach (array_values(config('inventory_v2_filter_permissions.filters', [])) as $name) {
            Permission::where('name', $name)->delete();
        }
    }
};
