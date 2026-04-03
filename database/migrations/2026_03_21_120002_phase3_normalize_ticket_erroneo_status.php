<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 3 — Unificar "Ticket erróneo": sustituir NBSP (U+00A0, UTF-8 C2 A0) por espacio normal en `status`.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        foreach (['services', 'historical_services'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'status')) {
                continue;
            }

            DB::statement(
                'UPDATE `'.$table.'` SET `status` = REPLACE(`status`, UNHEX(?), ?) WHERE `status` LIKE ? AND `status` LIKE ?',
                ['C2A0', ' ', '%Ticket%', '%erróneo%']
            );
        }
    }

    public function down(): void
    {
        // No reversible sin snapshot.
    }
};
