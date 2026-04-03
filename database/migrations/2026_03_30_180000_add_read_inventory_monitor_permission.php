<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Inventario V2: monitoreo (KPIs, alertas, exportes) separado de read inventory.
 */
return new class extends Migration
{
    public function up(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        Permission::firstOrCreate(['name' => 'read inventory monitor']);

        $admin = Role::where('name', 'Admin')->first();
        if ($admin && ! $admin->hasPermissionTo('read inventory monitor')) {
            $admin->givePermissionTo('read inventory monitor');
        }
    }

    public function down(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        Permission::where('name', 'read inventory monitor')->delete();
    }
};
