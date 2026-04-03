<?php

namespace App\Livewire\Inventory;

use App\Models\InvAsset;
use App\Models\InvImportBatch;
use App\Models\InvImportRow;
use App\Support\Inventory\InventoryImportMatcher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Vista "Pendientes": incumplimientos e inconsistencias del inventario V2.
 * Robados / perdidos: por nombre de estatus en inv_statuses (p. ej. ROBADO, PERDIDO).
 * Mantenimiento: estatus con "mantenim" en el nombre o ticket en inv_maintenances sin fecha de fin.
 */
class InventoryPendingIndex extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    /** Filas por sección (cada tabla tiene su propio paginador). */
    public int $perPage = 10;

    public function mount(): void
    {
        abort_unless(Auth::check() && Auth::user()->can('read inventory'), 403);
    }

    public function updatingPerPage(): void
    {
        $this->resetPage('bajaPage');
        $this->resetPage('stolenPage');
        $this->resetPage('lostPage');
        $this->resetPage('maintenancePage');
        $this->resetPage('companyPage');
        $this->resetPage('categoryPage');
        $this->resetPage('statusPage');
        $this->resetPage('labelPage');
        $this->resetPage('tagSameAsLabelPage');
        $this->resetPage('serialDupPage');
        $this->resetPage('tagDupPage');
        $this->resetPage('importRowsPage');
        $this->resetPage('importBatchesPage');
    }

    public function retryImportRow(int $rowId): void
    {
        abort_unless(Auth::check() && Auth::user()->can('read inventory'), 403);

        $row = InvImportRow::query()->with('batch')->find($rowId);
        if (! $row || ! $row->batch) {
            session()->flash('error', 'La fila de importación ya no existe.');
            return;
        }

        /** @var InventoryImportMatcher $matcher */
        $matcher = app(InventoryImportMatcher::class);
        $defaults = (array) ($row->batch->defaults ?? []);
        $result = $matcher->parseRow((array) ($row->payload ?? []), $defaults);

        $row->update([
            'parsed' => $result['parsed'],
            'errors' => $result['errors'],
            'warnings' => $result['warnings'],
            'action' => $result['action'],
            'status' => empty($result['errors']) ? 'ready' : 'pending',
        ]);

        if (! empty($result['errors'])) {
            session()->flash('message', 'Fila revalidada: aún requiere corrección.');
            return;
        }

        DB::transaction(function () use ($matcher, $row, $result) {
            $apply = $matcher->upsertParsedRow($result['parsed'], Auth::id());
            $row->update([
                'status' => 'resolved',
                'processed_asset_id' => $apply['asset_id'] ?? null,
                'resolved_at' => now(),
            ]);
        });

        session()->flash('message', 'Fila importada correctamente desde Pendientes.');
    }

    public function retryImportBatch(int $batchId): void
    {
        abort_unless(Auth::check() && Auth::user()->can('read inventory'), 403);

        $batch = InvImportBatch::query()->with(['rows' => fn ($q) => $q->whereIn('status', ['pending', 'ready'])])->find($batchId);
        if (! $batch) {
            session()->flash('error', 'El lote seleccionado no existe.');
            return;
        }

        $total = 0;
        $ok = 0;
        foreach ($batch->rows as $row) {
            $total++;
            $this->retryImportRow((int) $row->id);
            $row->refresh();
            if ($row->status === 'resolved') {
                $ok++;
            }
        }

        $remaining = InvImportRow::query()
            ->where('batch_id', $batch->id)
            ->whereIn('status', ['pending', 'ready'])
            ->count();

        $batch->update(['status' => $remaining > 0 ? 'needs_review' : 'executed']);
        session()->flash('message', "Reintento de lote completado: {$ok}/{$total} filas resueltas.");
    }

    private static function assetsAssignedToBajaQuery(): Builder
    {
        return InvAsset::query()
            ->whereNotNull('current_user_id')
            ->whereExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('users')
                    ->whereColumn('users.id', 'inv_assets.current_user_id')
                    ->whereNotNull('users.deleted_at');
            })
            ->with([
                'category',
                'status',
                'sede',
                'currentUser' => fn ($q) => $q->withTrashed(),
            ])
            ->orderByDesc('inv_assets.id');
    }

    private static function assetsStatusPatternQuery(string $lowerLike): Builder
    {
        return InvAsset::query()
            ->whereHas('status', function ($s) use ($lowerLike) {
                $s->whereRaw('LOWER(name) LIKE ?', [$lowerLike]);
            })
            ->with(['category', 'status', 'sede'])
            ->orderByDesc('inv_assets.id');
    }

    private static function assetsInMaintenanceQuery(): Builder
    {
        return InvAsset::query()
            ->where(function ($q) {
                $q->whereHas('status', function ($s) {
                    $s->whereRaw('LOWER(name) LIKE ?', ['%mantenim%']);
                })->orWhereHas('maintenances', function ($m) {
                    $m->whereNull('end_date');
                });
            })
            ->with([
                'category',
                'status',
                'sede',
                'maintenances' => fn ($m) => $m->whereNull('end_date')->orderByDesc('start_date'),
            ])
            ->orderByDesc('inv_assets.id');
    }

    public function render()
    {
        $pp = max(1, min(100, $this->perPage));

        return view('livewire.inventory.inventory-pending-index', [
            'assignedToBaja' => static::assetsAssignedToBajaQuery()->paginate($pp, ['*'], 'bajaPage'),
            'stolenAssets' => static::assetsStatusPatternQuery('%robad%')->paginate($pp, ['*'], 'stolenPage'),
            'lostAssets' => static::assetsStatusPatternQuery('%perdid%')->paginate($pp, ['*'], 'lostPage'),
            'maintenanceAssets' => static::assetsInMaintenanceQuery()->paginate($pp, ['*'], 'maintenancePage'),
            'withoutCompany' => InvAsset::query()
                ->whereNull('company_id')
                ->with(['category', 'status', 'sede'])
                ->orderByDesc('id')
                ->paginate($pp, ['*'], 'companyPage'),
            'withoutCategory' => InvAsset::query()
                ->whereNull('category_id')
                ->with(['status', 'sede'])
                ->orderByDesc('id')
                ->paginate($pp, ['*'], 'categoryPage'),
            'withoutStatus' => InvAsset::query()
                ->whereNull('status_id')
                ->with(['category', 'sede'])
                ->orderByDesc('id')
                ->paginate($pp, ['*'], 'statusPage'),
            'withoutLabel' => InvAsset::query()
                ->whereNull('label_id')
                ->with(['category', 'status', 'sede'])
                ->orderByDesc('id')
                ->paginate($pp, ['*'], 'labelPage'),
            'tagSameAsLabel' => InvAsset::query()
                ->whereNotNull('internal_tag')
                ->whereRaw("TRIM(internal_tag) <> ''")
                ->whereHas('label', function ($q) {
                    $q->whereRaw('UPPER(TRIM(inv_labels.name)) = UPPER(TRIM(inv_assets.internal_tag))');
                })
                ->with(['label', 'sede', 'category', 'company'])
                ->orderByDesc('id')
                ->paginate($pp, ['*'], 'tagSameAsLabelPage'),
            'serialDuplicates' => InvAsset::query()
                ->selectRaw('UPPER(TRIM(serial)) as duplicate_value, COUNT(*) as total')
                ->whereNotNull('serial')
                ->whereRaw("TRIM(serial) <> ''")
                ->groupBy('duplicate_value')
                ->havingRaw('COUNT(*) > 1')
                ->orderByDesc('total')
                ->paginate($pp, ['*'], 'serialDupPage'),
            'tagDuplicates' => InvAsset::query()
                ->selectRaw('UPPER(TRIM(internal_tag)) as duplicate_value, COUNT(*) as total')
                ->whereNotNull('internal_tag')
                ->whereRaw("TRIM(internal_tag) <> ''")
                ->groupBy('duplicate_value')
                ->havingRaw('COUNT(*) > 1')
                ->orderByDesc('total')
                ->paginate($pp, ['*'], 'tagDupPage'),
            'pendingImportRows' => InvImportRow::query()
                ->with('batch')
                ->where('status', 'pending')
                ->orderByDesc('id')
                ->paginate($pp, ['*'], 'importRowsPage'),
            'pendingImportBatches' => InvImportBatch::query()
                ->whereIn('status', ['needs_review', 'previewed', 'ready'])
                ->orderByDesc('id')
                ->paginate($pp, ['*'], 'importBatchesPage'),
            'countBaja' => static::assetsAssignedToBajaQuery()->count(),
            'countStolen' => static::assetsStatusPatternQuery('%robad%')->count(),
            'countLost' => static::assetsStatusPatternQuery('%perdid%')->count(),
            'countMaintenance' => static::assetsInMaintenanceQuery()->count(),
            'countNoCompany' => InvAsset::whereNull('company_id')->count(),
            'countNoCategory' => InvAsset::whereNull('category_id')->count(),
            'countNoStatus' => InvAsset::whereNull('status_id')->count(),
            'countNoLabel' => InvAsset::whereNull('label_id')->count(),
            'countTagSameAsLabel' => InvAsset::query()
                ->whereNotNull('internal_tag')
                ->whereRaw("TRIM(internal_tag) <> ''")
                ->whereHas('label', function ($q) {
                    $q->whereRaw('UPPER(TRIM(inv_labels.name)) = UPPER(TRIM(inv_assets.internal_tag))');
                })
                ->count(),
            'countSerialDup' => DB::query()
                ->fromSub(
                    DB::table('inv_assets')
                        ->selectRaw('UPPER(TRIM(serial)) as duplicate_value')
                        ->whereNotNull('serial')
                        ->whereRaw("TRIM(serial) <> ''")
                        ->groupBy('duplicate_value')
                        ->havingRaw('COUNT(*) > 1'),
                    'dups'
                )
                ->count(),
            'countTagDup' => DB::query()
                ->fromSub(
                    DB::table('inv_assets')
                        ->selectRaw('UPPER(TRIM(internal_tag)) as duplicate_value')
                        ->whereNotNull('internal_tag')
                        ->whereRaw("TRIM(internal_tag) <> ''")
                        ->groupBy('duplicate_value')
                        ->havingRaw('COUNT(*) > 1'),
                    'dups'
                )
                ->count(),
            'countPendingImportRows' => InvImportRow::query()->where('status', 'pending')->count(),
        ])
            ->extends('admin.layout', ['title' => ' | Pendientes inventario'])
            ->section('content');
    }
}
