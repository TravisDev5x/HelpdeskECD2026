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

    public function up(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $perm = Permission::firstOrCreate(
            ['name' => 'escalate service', 'guard_name' => $this->guard()]
        );

        foreach (Role::query()->where('guard_name', $this->guard())->get() as $role) {
            if ($role->hasPermissionTo('update service') && ! $role->hasPermissionTo($perm)) {
                $role->givePermissionTo($perm);
            }
        }

        $admin = Role::findByName('Admin', $this->guard());
        if ($admin && ! $admin->hasPermissionTo($perm)) {
            $admin->givePermissionTo($perm);
        }

        app()['cache']->forget('spatie.permission.cache');
    }

    public function down(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        Permission::where('name', 'escalate service')
            ->where('guard_name', $this->guard())
            ->delete();

        app()['cache']->forget('spatie.permission.cache');
    }
};
