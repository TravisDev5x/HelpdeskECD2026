<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $guard = config('auth.defaults.guard', 'web');

        $exists = DB::table('permissions')
            ->where('name', 'manage inventory labels')
            ->where('guard_name', $guard)
            ->exists();

        if (! $exists) {
            DB::table('permissions')->insert([
                'name' => 'manage inventory labels',
                'guard_name' => $guard,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $guard = config('auth.defaults.guard', 'web');
        DB::table('permissions')
            ->where('name', 'manage inventory labels')
            ->where('guard_name', $guard)
            ->delete();
    }
};
