<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use App\Models\InvCategory;
use App\Models\InvStatus;
use App\Models\InvAsset;
use App\Models\InvMovement;
use App\Models\Product;

/**
 * Seeder destructivo: trunca tablas de inventario v2 y migra desde Product.
 * Ejecutar solo manualmente: php artisan db:seed --class=Database\\Seeders\\InvMigrationSeeder
 */
class InvMigrationSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        InvMovement::truncate();
        InvAsset::truncate();
        InvStatus::truncate();
        InvCategory::truncate();
        Schema::enableForeignKeyConstraints();

        $this->command->info('--- Creando Catálogos ---');

        $catLaptop = InvCategory::create(['name' => 'Laptop', 'prefix' => 'NB', 'type' => 'HARDWARE']);
        $catMonitor = InvCategory::create(['name' => 'Monitor', 'prefix' => 'MON', 'type' => 'HARDWARE']);
        $catGeneral = InvCategory::create(['name' => 'General', 'prefix' => 'GEN', 'type' => 'HARDWARE']);

        $stDisponible = InvStatus::create(['name' => 'DISPONIBLE', 'badge_class' => 'success', 'assignable' => true]);
        $stAsignado = InvStatus::create(['name' => 'ASIGNADO', 'badge_class' => 'primary', 'assignable' => false]);
        $stTaller = InvStatus::create(['name' => 'MANTENIMIENTO', 'badge_class' => 'warning', 'assignable' => false]);
        $stBaja = InvStatus::create(['name' => 'BAJA', 'badge_class' => 'danger', 'assignable' => false]);
        $stDesmantelado = InvStatus::create(['name' => 'DESMANTELADO', 'badge_class' => 'dark', 'assignable' => false]);

        $oldProducts = Product::withTrashed()->get();

        $this->command->info("--- Migrando {$oldProducts->count()} activos ---");

        foreach ($oldProducts as $old) {
            $etiqueta = trim($old->etiqueta);

            if (empty($etiqueta) || in_array(strtoupper($etiqueta), ['N/A', 'NA', 'S/N', 'SIN ETIQUETA', 'NO APLICA'])) {
                $etiqueta = null;
            }

            if ($etiqueta !== null) {
                if (InvAsset::where('internal_tag', $etiqueta)->exists()) {
                    $etiqueta = $etiqueta . '-DUP-' . $old->id;
                }
            }

            $catId = $catGeneral->id;
            $busqueda = strtolower($old->name . ' ' . $old->medio . ' ' . $old->modelo);

            if (Str::contains($busqueda, ['laptop', 'portatil', 'notebook', 'cpu', 'desktop'])) {
                $catId = $catLaptop->id;
            } elseif (Str::contains($busqueda, ['monitor', 'pantalla', 'display'])) {
                $catId = $catMonitor->id;
            }

            $statusId = $stDisponible->id;
            $condition = 'BUENO';
            $oldStatus = strtoupper($old->status);

            if ($oldStatus == 'ASIGNADO' || $oldStatus == 'OPERATIVO') {
                $statusId = $stDisponible->id;
            }
            if ($oldStatus == 'MANTENIMIENTO' || $old->maintenance == 1) {
                $statusId = $stTaller->id;
            }

            if (in_array($oldStatus, ['BAJA', 'ROBADO', 'PERDIDO', 'SCRAP'])) {
                $statusId = $stBaja->id;
                $condition = 'MALO';
            }
            if ($oldStatus == 'RECICLADO') {
                $statusId = $stDesmantelado->id;
                $condition = 'PARA_PIEZAS';
            }

            if ($old->employee_id && ! $old->deleted_at && $statusId != $stBaja->id) {
                $statusId = $stAsignado->id;
            }

            $asset = InvAsset::create([
                'uuid' => (string) Str::uuid(),
                'internal_tag' => $etiqueta,
                'serial' => $old->serie,
                'name' => $old->name ?: 'Equipo sin nombre',
                'category_id' => $catId,
                'status_id' => $statusId,
                'condition' => $condition,
                'sede_id' => $old->sede_id,
                'ubicacion_id' => $old->ubicacion_id,
                'cost' => $old->costo,
                'purchase_date' => $old->fecha_ingreso,
                'warranty_expiry' => null,
                'specs' => [
                    'marca' => $old->marca,
                    'modelo' => $old->modelo,
                    'medio' => $old->medio,
                    'ip' => $old->ip,
                    'mac' => $old->mac,
                    'observaciones' => $old->observacion,
                ],
                'current_user_id' => $old->employee_id,
                'notes' => $old->observacion,
                'created_at' => $old->created_at,
                'updated_at' => $old->updated_at,
                'deleted_at' => $old->deleted_at,
            ]);

            if ($old->employee_id && ! $old->deleted_at) {
                InvMovement::create([
                    'asset_id' => $asset->id,
                    'type' => 'CHECKOUT',
                    'user_id' => $old->employee_id,
                    'admin_id' => 1,
                    'date' => $old->date_assignment ?? $old->updated_at ?? now(),
                    'notes' => 'Migración: Asignación histórica.',
                    'responsiva_path' => $old->responsiva,
                ]);
            } else {
                InvMovement::create([
                    'asset_id' => $asset->id,
                    'type' => 'AUDIT',
                    'admin_id' => 1,
                    'date' => $old->created_at ?? now(),
                    'notes' => 'Alta inicial por Migración.',
                ]);
            }
        }

        $this->command->info('¡Migración Finalizada Correctamente!');
    }
}
