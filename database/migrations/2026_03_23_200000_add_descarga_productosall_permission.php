<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Alinea BD con la ruta `producto.downloadall` (middleware permission:descarga_productosall).
 */
return new class extends Migration
{
    public function up(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $permission = Permission::firstOrCreate(['name' => 'descarga_productosall']);

        $admin = Role::where('name', 'Admin')->first();
        if ($admin && ! $admin->hasPermissionTo($permission)) {
            $admin->givePermissionTo($permission);
        }
    }

    public function down(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        Permission::where('name', 'descarga_productosall')->delete();
    }
};
