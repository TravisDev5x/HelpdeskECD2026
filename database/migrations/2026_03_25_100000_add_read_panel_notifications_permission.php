<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        $panel = Permission::query()->firstOrCreate(
            ['name' => 'read panel notifications', 'guard_name' => 'web']
        );

        $readServices = Permission::query()
            ->where('name', 'read services')
            ->where('guard_name', 'web')
            ->first();

        if (! $readServices) {
            return;
        }

        $roleIds = DB::table('role_has_permissions')
            ->where('permission_id', $readServices->id)
            ->pluck('role_id');

        foreach ($roleIds as $roleId) {
            DB::table('role_has_permissions')->updateOrInsert([
                'role_id' => $roleId,
                'permission_id' => $panel->id,
            ]);
        }
    }

    public function down(): void
    {
        $panel = Permission::query()
            ->where('name', 'read panel notifications')
            ->where('guard_name', 'web')
            ->first();

        if (! $panel) {
            return;
        }

        DB::table('role_has_permissions')->where('permission_id', $panel->id)->delete();
        $panel->delete();
    }
};
