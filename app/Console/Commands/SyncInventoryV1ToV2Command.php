<?php

namespace App\Console\Commands;

use App\Models\InvAsset;
use App\Models\InvCategory;
use App\Models\InvStatus;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncInventoryV1ToV2Command extends Command
{
    protected $signature = 'helpdesk:inventory-v1-to-v2-sync
                            {--dry-run : Simula sin escribir en base}
                            {--chunk=500 : Cantidad de registros por lote}';

    protected $description = 'Sincroniza activos V1 (products activos) hacia inventario V2 (inv_assets) sin truncar.';

    /** @var array<int, int> */
    private array $assetByLegacyProductId = [];
    /** @var array<string, int> */
    private array $assetBySerial = [];
    /** @var array<string, int> */
    private array $assetByInternalTag = [];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunk = max(50, (int) $this->option('chunk'));

        $catalog = $this->ensureCatalogs();
        $created = 0;
        $updated = 0;
        $scanned = 0;

        $this->buildExistingAssetIndexes();

        $query = Product::query()->orderBy('id');
        $total = (clone $query)->count();
        $this->info("Productos V1 activos a procesar: {$total}");
        if ($dryRun) {
            $this->warn('Modo simulación activo (dry-run): no se guardarán cambios.');
        }

        $query->chunkById($chunk, function ($products) use (&$created, &$updated, &$scanned, $catalog, $dryRun) {
            foreach ($products as $product) {
                $scanned++;

                $payload = $this->buildAssetPayload($product, $catalog);
                $existing = $this->findExistingAsset($product, $payload['serial'] ?? null, $payload['internal_tag'] ?? null);

                if ($dryRun) {
                    if ($existing) {
                        $updated++;
                    } else {
                        $created++;
                    }
                    continue;
                }

                if ($existing) {
                    $existing->fill($payload);
                    $existing->save();
                    $this->registerAssetInIndexes($existing);
                    $updated++;
                    continue;
                }

                $payload['uuid'] = (string) Str::uuid();
                $createdAsset = InvAsset::create($payload);
                $this->registerAssetInIndexes($createdAsset);
                $created++;
            }
        });

        $this->newLine();
        $this->info('Sincronización V1 -> V2 completada.');
        $this->line("Escaneados: {$scanned}");
        $this->line("Creados: {$created}");
        $this->line("Actualizados: {$updated}");

        return self::SUCCESS;
    }

    private function ensureCatalogs(): array
    {
        $catLaptop = InvCategory::query()->firstOrCreate(
            ['name' => 'Laptop'],
            ['prefix' => 'NB', 'type' => 'HARDWARE', 'require_specs' => true]
        );
        $catMonitor = InvCategory::query()->firstOrCreate(
            ['name' => 'Monitor'],
            ['prefix' => 'MON', 'type' => 'HARDWARE', 'require_specs' => true]
        );
        $catGeneral = InvCategory::query()->firstOrCreate(
            ['name' => 'General'],
            ['prefix' => 'GEN', 'type' => 'HARDWARE', 'require_specs' => true]
        );

        $stDisponible = InvStatus::query()->firstOrCreate(
            ['name' => 'DISPONIBLE'],
            ['badge_class' => 'success', 'assignable' => true]
        );
        $stAsignado = InvStatus::query()->firstOrCreate(
            ['name' => 'ASIGNADO'],
            ['badge_class' => 'primary', 'assignable' => false]
        );
        $stTaller = InvStatus::query()->firstOrCreate(
            ['name' => 'MANTENIMIENTO'],
            ['badge_class' => 'warning', 'assignable' => false]
        );
        $stBaja = InvStatus::query()->firstOrCreate(
            ['name' => 'BAJA'],
            ['badge_class' => 'danger', 'assignable' => false]
        );
        $stDesmantelado = InvStatus::query()->firstOrCreate(
            ['name' => 'DESMANTELADO'],
            ['badge_class' => 'dark', 'assignable' => false]
        );

        return [
            'categories' => [
                'laptop' => $catLaptop->id,
                'monitor' => $catMonitor->id,
                'general' => $catGeneral->id,
            ],
            'statuses' => [
                'disponible' => $stDisponible->id,
                'asignado' => $stAsignado->id,
                'mantenimiento' => $stTaller->id,
                'baja' => $stBaja->id,
                'desmantelado' => $stDesmantelado->id,
            ],
        ];
    }

    private function buildExistingAssetIndexes(): void
    {
        InvAsset::query()
            ->select(['id', 'serial', 'internal_tag', 'specs'])
            ->chunkById(1000, function ($assets) {
                foreach ($assets as $asset) {
                    $this->registerAssetInIndexes($asset);
                }
            });
    }

    private function registerAssetInIndexes(InvAsset $asset): void
    {
        $legacyProductId = (int) data_get($asset->specs, 'legacy_product_id', 0);
        if ($legacyProductId > 0) {
            $this->assetByLegacyProductId[$legacyProductId] = $asset->id;
        }

        $serial = $this->normalizeIdentity($asset->serial);
        if ($serial) {
            $this->assetBySerial[$serial] = $asset->id;
        }

        $internalTag = $this->normalizeTag($asset->internal_tag);
        if ($internalTag) {
            $this->assetByInternalTag[$internalTag] = $asset->id;
        }
    }

    private function findExistingAsset(Product $product, ?string $serial, ?string $internalTag): ?InvAsset
    {
        if (isset($this->assetByLegacyProductId[$product->id])) {
            return InvAsset::query()->find($this->assetByLegacyProductId[$product->id]);
        }

        if ($serial && isset($this->assetBySerial[$serial])) {
            return InvAsset::query()->find($this->assetBySerial[$serial]);
        }

        if ($internalTag && isset($this->assetByInternalTag[$internalTag])) {
            return InvAsset::query()->find($this->assetByInternalTag[$internalTag]);
        }

        return null;
    }

    private function buildAssetPayload(Product $product, array $catalog): array
    {
        $categoryId = $this->resolveCategoryId($product, $catalog);
        [$statusId, $condition] = $this->resolveStatusAndCondition($product, $catalog);

        $serial = $this->normalizeIdentity($product->serie);
        $internalTag = $this->normalizeTag($product->etiqueta);

        if ($internalTag) {
            $existingTagOwnerId = $this->assetByInternalTag[$internalTag] ?? null;
            $legacyAssetId = $this->assetByLegacyProductId[$product->id] ?? null;
            if ($existingTagOwnerId !== null && $existingTagOwnerId !== $legacyAssetId) {
                $internalTag = "{$internalTag}-V1-{$product->id}";
            }
        }

        return [
            'internal_tag' => $internalTag,
            'serial' => $serial,
            'name' => trim((string) ($product->name ?: 'Equipo sin nombre')),
            'category_id' => $categoryId,
            'status_id' => $statusId,
            'condition' => $condition,
            'company_id' => $this->nullableInt($product->company_id),
            'sede_id' => $this->nullableInt($product->sede_id),
            'ubicacion_id' => $this->nullableInt($product->ubicacion_id),
            'cost' => $product->costo,
            'purchase_date' => $product->fecha_ingreso,
            'current_user_id' => $this->nullableInt($product->employee_id),
            'notes' => $product->observacion,
            'specs' => [
                'legacy_product_id' => $product->id,
                'legacy_owner' => $product->owner,
                'legacy_status' => $product->status,
                'marca' => $product->marca,
                'modelo' => $product->modelo,
                'medio' => $product->medio,
                'ip' => $product->ip,
                'mac' => $product->mac,
                'observaciones' => $product->observacion,
            ],
        ];
    }

    private function resolveCategoryId(Product $product, array $catalog): int
    {
        $search = Str::lower(trim("{$product->name} {$product->medio} {$product->modelo}"));
        if (Str::contains($search, ['laptop', 'portatil', 'notebook', 'cpu', 'desktop'])) {
            return $catalog['categories']['laptop'];
        }
        if (Str::contains($search, ['monitor', 'pantalla', 'display'])) {
            return $catalog['categories']['monitor'];
        }
        return $catalog['categories']['general'];
    }

    private function resolveStatusAndCondition(Product $product, array $catalog): array
    {
        $oldStatus = Str::upper(trim((string) $product->status));
        $statusId = $catalog['statuses']['disponible'];
        $condition = 'BUENO';

        if ($product->employee_id) {
            $statusId = $catalog['statuses']['asignado'];
        } elseif ($oldStatus === 'EN_REPARACION' || $oldStatus === 'MANTENIMIENTO' || $oldStatus === 'INOPERABLE' || (int) $product->maintenance === 1) {
            $statusId = $catalog['statuses']['mantenimiento'];
        } elseif (in_array($oldStatus, ['RECICLADO'], true)) {
            $statusId = $catalog['statuses']['desmantelado'];
            $condition = 'PARA_PIEZAS';
        } elseif (in_array($oldStatus, ['BAJA', 'ROBADO', 'PERDIDO', 'SCRAP', 'ABSOLETO', 'OBSOLETO', 'NO_ENTREGADO'], true)) {
            $statusId = $catalog['statuses']['baja'];
            $condition = 'MALO';
        }

        return [$statusId, $condition];
    }

    private function normalizeIdentity(?string $value): ?string
    {
        $normalized = Str::upper(trim((string) $value));
        if ($normalized === '' || in_array($normalized, ['0', 'N/A', 'NA', 'S/N', 'SIN SERIE', 'NO APLICA'], true)) {
            return null;
        }
        return $normalized;
    }

    private function normalizeTag(?string $value): ?string
    {
        $tag = $this->normalizeIdentity($value);
        if ($tag === null) {
            return null;
        }

        if (in_array($tag, ['N/A', 'NA', 'S/N', 'SIN ETIQUETA', 'NO APLICA'], true)) {
            return null;
        }

        return $tag;
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        $raw = trim((string) $value);
        if ($raw === '' || ! ctype_digit($raw)) {
            return null;
        }

        return (int) $raw;
    }
}

