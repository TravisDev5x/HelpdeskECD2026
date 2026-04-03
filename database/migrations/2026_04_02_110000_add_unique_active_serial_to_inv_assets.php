<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inv_assets') || ! Schema::hasColumn('inv_assets', 'serial')) {
            return;
        }

        $hasDeletedAt = Schema::hasColumn('inv_assets', 'deleted_at');
        $driver = Schema::getConnection()->getDriverName();

        // Limpieza segura: valores no informativos se consideran "sin serie" y se guardan como NULL.
        $this->nullifyPlaceholderSerials($hasDeletedAt);

        // Detección + resolución automática de duplicados normalizados (trim + mayúsculas).
        $this->resolveNormalizedDuplicates($hasDeletedAt);
        $duplicates = $this->normalizedDuplicates($hasDeletedAt);
        if ($duplicates->isNotEmpty()) {
            $examples = $duplicates->map(function ($row) {
                return "{$row->serial_norm} ({$row->total}) [ids: {$row->ids}]";
            })->implode(', ');
            throw new RuntimeException(
                "No se puede crear índice único de serial: persisten duplicados en inv_assets. ".
                "Revise manualmente estos casos y vuelva a ejecutar la migración. Ejemplos: {$examples}"
            );
        }

        // En MySQL, UNIQUE(serial, deleted_at) NO bloquea duplicados con deleted_at=NULL.
        // Para unicidad estricta de serial usamos índice único directo sobre serial.
        // (Múltiples NULL siguen permitidos por MySQL, que es lo deseado para "sin serie".)
        if ($this->hasIndex('inv_assets', 'inv_assets_serial_unique')) {
            return;
        }

        if ($driver !== 'mysql') {
            // Mantiene comportamiento explícito en otros drivers.
            Schema::table('inv_assets', function (Blueprint $table) {
                $table->unique('serial', 'inv_assets_serial_unique');
            });
            return;
        }

        Schema::table('inv_assets', function (Blueprint $table) {
            $table->unique('serial', 'inv_assets_serial_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('inv_assets')) {
            return;
        }

        if (! $this->hasIndex('inv_assets', 'inv_assets_serial_unique')) {
            return;
        }

        Schema::table('inv_assets', function (Blueprint $table) {
            $table->dropUnique('inv_assets_serial_unique');
        });
    }

    private function nullifyPlaceholderSerials(bool $hasDeletedAt): void
    {
        $normalizeExpr = "UPPER(TRIM(REPLACE(REPLACE(serial, '\\\\', '/'), ' ', '')))";
        $placeholders = [
            'N/A', 'NA', 'S/N', 'SN', 'N.D.', 'ND',
            'SIN SERIE', 'SINSERIE', 'NO APLICA', 'NOAPLICA',
            'NULL', 'NULO', '-', '--',
        ];
        $bindings = implode(',', array_fill(0, count($placeholders), '?'));

        $query = DB::table('inv_assets')
            ->whereNotNull('serial')
            ->whereRaw('TRIM(serial) <> ""')
            ->whereRaw("{$normalizeExpr} IN ({$bindings})", $placeholders);

        if ($hasDeletedAt) {
            // Limpiamos activos vigentes; no tocamos históricos borrados.
            $query->whereNull('deleted_at');
        }

        $query->update(['serial' => null]);
    }

    private function normalizedDuplicates(bool $hasDeletedAt)
    {
        $normalizeExpr = 'UPPER(TRIM(serial))';

        $query = DB::table('inv_assets')
            ->selectRaw("{$normalizeExpr} as serial_norm, COUNT(*) as total, GROUP_CONCAT(id ORDER BY id SEPARATOR ',') as ids")
            ->whereNotNull('serial')
            ->whereRaw('TRIM(serial) <> ""')
            ->groupBy(DB::raw($normalizeExpr))
            ->having('total', '>', 1)
            ->limit(5);

        if ($hasDeletedAt) {
            $query->whereNull('deleted_at');
        }

        return $query->get();
    }

    private function resolveNormalizedDuplicates(bool $hasDeletedAt): void
    {
        $normalizeExpr = 'UPPER(TRIM(serial))';

        $duplicateGroups = DB::table('inv_assets')
            ->selectRaw("{$normalizeExpr} as serial_norm")
            ->whereNotNull('serial')
            ->whereRaw('TRIM(serial) <> ""')
            ->when($hasDeletedAt, fn ($q) => $q->whereNull('deleted_at'))
            ->groupBy(DB::raw($normalizeExpr))
            ->havingRaw('COUNT(*) > 1')
            ->pluck('serial_norm');

        foreach ($duplicateGroups as $serialNorm) {
            $rows = DB::table('inv_assets')
                ->select('id')
                ->whereNotNull('serial')
                ->whereRaw('TRIM(serial) <> ""')
                ->whereRaw("{$normalizeExpr} = ?", [$serialNorm])
                ->when($hasDeletedAt, fn ($q) => $q->whereNull('deleted_at'))
                ->orderBy('id')
                ->pluck('id')
                ->values();

            if ($rows->count() <= 1) {
                continue;
            }

            // Conservamos el serial en el primer registro (más antiguo por id)
            // y limpiamos los demás para permitir unicidad estricta en la BD.
            $idsToNullify = $rows->slice(1)->all();
            DB::table('inv_assets')->whereIn('id', $idsToNullify)->update(['serial' => null]);
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $rows = DB::select('SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?', [$indexName]);

            return ! empty($rows);
        }

        return false;
    }
};
