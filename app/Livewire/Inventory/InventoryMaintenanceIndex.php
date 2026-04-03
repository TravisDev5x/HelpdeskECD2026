<?php

namespace App\Livewire\Inventory;

use App\Livewire\Inventory\Concerns\GuardsInventoryV2Filters;
use App\Support\InventoryV2FilterPermissions;
use App\Models\InvAsset;
use App\Models\InvCategory;
use App\Models\InvMaintenance;
use App\Models\InvMaintenanceModality;
use App\Models\InvMaintenanceOrigin;
use App\Models\InvStatus;
use App\Models\Sede;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryMaintenanceIndex extends Component
{
    use GuardsInventoryV2Filters;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';

    public $category_filter = '';

    public $status_filter = '';

    public $sede_filter = '';

    public int $perPage = 15;

    /** @var array<int, string> */
    public array $selected = [];

    public bool $selectAll = false;

    public bool $showMaintModal = false;

    /** Modal de trazabilidad de mantenimientos por activo. */
    public bool $showTraceModal = false;

    public ?int $trace_asset_id = null;

    /** IDs de catálogo (tablas inv_maintenance_origins / inv_maintenance_modalities). */
    public string $mt_origin_id = '';

    public string $mt_modality_id = '';

    public string $mt_title = '';

    public string $mt_diagnosis = '';

    public string $mt_solution = '';

    public string $mt_start_date = '';

    public string $mt_end_date = '';

    public $mt_cost = '';

    /**
     * Si es false y el nuevo registro queda abierto (sin fecha de cierre), no se permite si el activo ya tiene uno abierto.
     */
    public bool $mt_allow_multiple_open = false;

    public function mount(): void
    {
        abort_unless(Auth::check() && Auth::user()->can('read inventory'), 403);
        $this->mt_start_date = now()->format('Y-m-d');
        $this->applyDefaultCatalogIds();

        $preId = (int) request()->query('asset', 0);
        if ($preId > 0 && InvAsset::whereKey($preId)->exists()) {
            $this->selected = [(string) $preId];
        }

        $this->stripUnauthorizedInventoryFilters([
            'search' => 'search',
            'category' => 'category_filter',
            'status' => 'status_filter',
            'sede' => 'sede_filter',
        ]);
    }

    public function updatingSearch(): void
    {
        if (! $this->userCanInventoryFilter('search')) {
            $this->search = '';
            return;
        }
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatingCategoryFilter(): void
    {
        if (! $this->userCanInventoryFilter('category')) {
            $this->category_filter = '';
            return;
        }
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatingStatusFilter(): void
    {
        if (! $this->userCanInventoryFilter('status')) {
            $this->status_filter = '';
            return;
        }
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatingSedeFilter(): void
    {
        if (! $this->userCanInventoryFilter('sede')) {
            $this->sede_filter = '';
            return;
        }
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedSelectAll($value): void
    {
        $this->selected = $value
            ? $this->baseQuery()->limit(500)->pluck('id')->map(fn ($id) => (string) $id)->toArray()
            : [];
    }

    private function maintenanceFiltersEffective(): array
    {
        $u = Auth::user();

        return [
            'search' => (string) InventoryV2FilterPermissions::effectiveScalar($u, 'search', $this->search, ''),
            'category' => InventoryV2FilterPermissions::userMayUse($u, 'category') ? $this->category_filter : '',
            'status' => InventoryV2FilterPermissions::userMayUse($u, 'status') ? $this->status_filter : '',
            'sede' => InventoryV2FilterPermissions::userMayUse($u, 'sede') ? $this->sede_filter : '',
        ];
    }

    private function baseQuery()
    {
        $f = $this->maintenanceFiltersEffective();

        return InvAsset::query()
            ->when($f['search'] !== '', function ($q) use ($f) {
                $term = '%'.$f['search'].'%';
                $q->where(function ($query) use ($term) {
                    $query->where('name', 'like', $term)
                        ->orWhere('internal_tag', 'like', $term)
                        ->orWhere('serial', 'like', $term);
                });
            })
            ->when($f['category'], fn ($q) => $q->where('category_id', $f['category']))
            ->when($f['status'], fn ($q) => $q->where('status_id', $f['status']))
            ->when($f['sede'], fn ($q) => $q->where('sede_id', $f['sede']));
    }

    private function resetSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function openMaintModal(): void
    {
        abort_unless(Auth::user()->can('edit inventory'), 403);
        if (count($this->selected) < 1) {
            return;
        }
        $this->prepareMaintForm();
        $this->showMaintModal = true;
    }

    /** Abre el mismo modal de alta dejando solo este activo seleccionado (desde columna Acciones). */
    public function openMaintModalForAsset(int $assetId): void
    {
        abort_unless(Auth::user()->can('edit inventory'), 403);
        if (! InvAsset::whereKey($assetId)->exists()) {
            session()->flash('error', 'Activo no encontrado.');

            return;
        }
        $this->selectAll = false;
        $this->selected = [(string) $assetId];
        $this->prepareMaintForm();
        $this->showMaintModal = true;
    }

    private function prepareMaintForm(): void
    {
        $this->resetErrorBag();
        $this->reset(['mt_title', 'mt_diagnosis', 'mt_solution', 'mt_end_date', 'mt_cost', 'mt_allow_multiple_open', 'mt_origin_id', 'mt_modality_id']);
        $this->mt_start_date = now()->format('Y-m-d');
        $this->applyDefaultCatalogIds();
    }

    private function applyDefaultCatalogIds(): void
    {
        $firstOrigin = InvMaintenanceOrigin::query()->where('is_active', true)->orderBy('sort_order')->value('id');
        $firstModality = InvMaintenanceModality::query()->where('is_active', true)->orderBy('sort_order')->value('id');
        $this->mt_origin_id = $firstOrigin ? (string) $firstOrigin : '';
        $this->mt_modality_id = $firstModality ? (string) $firstModality : '';
    }

    public function closeMaintModal(): void
    {
        $this->showMaintModal = false;
    }

    public function openTraceModal(int $assetId): void
    {
        abort_unless(Auth::user()->can('read inventory'), 403);
        if (! InvAsset::whereKey($assetId)->exists()) {
            session()->flash('error', 'Activo no encontrado.');

            return;
        }
        $this->trace_asset_id = $assetId;
        $this->showTraceModal = true;
    }

    public function closeTraceModal(): void
    {
        $this->showTraceModal = false;
        $this->trace_asset_id = null;
    }

    public function closeMaintenance(int $maintenanceId): void
    {
        abort_unless(Auth::user()->can('edit inventory'), 403);
        if ($this->trace_asset_id === null) {
            return;
        }

        $maint = InvMaintenance::query()
            ->where('id', $maintenanceId)
            ->where('asset_id', $this->trace_asset_id)
            ->whereNull('end_date')
            ->first();

        if (! $maint) {
            session()->flash('error', 'Mantenimiento no encontrado o ya estaba cerrado.');

            return;
        }

        $maint->update(['end_date' => now()->format('Y-m-d')]);
        session()->flash('message', 'Mantenimiento marcado como cerrado.');
    }

    /** Registra el mismo mantenimiento para cada activo seleccionado (misma lógica que InvMaintenance en ficha). */
    public function storeBulkMaintenance(): void
    {
        abort_unless(Auth::user()->can('edit inventory'), 403);

        $this->validate([
            'mt_origin_id' => [
                'required',
                Rule::exists('inv_maintenance_origins', 'id')->where(fn ($q) => $q->where('is_active', true)),
            ],
            'mt_modality_id' => [
                'required',
                Rule::exists('inv_maintenance_modalities', 'id')->where(fn ($q) => $q->where('is_active', true)),
            ],
            'mt_title' => 'required|string|min:3|max:255',
            'mt_diagnosis' => 'required|string|min:3|max:10000',
            'mt_solution' => 'nullable|string|max:10000',
            'mt_start_date' => 'required|date',
            'mt_end_date' => 'nullable|date|after_or_equal:mt_start_date',
            'mt_cost' => 'nullable|numeric|min:0|max:99999999.99',
            'mt_allow_multiple_open' => 'boolean',
        ], [
            'mt_end_date.after_or_equal' => 'La fecha de cierre no puede ser anterior al inicio.',
            'mt_origin_id.required' => 'Seleccione el origen del mantenimiento.',
            'mt_modality_id.required' => 'Seleccione la modalidad del mantenimiento.',
        ]);

        $ids = collect($this->selected)->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();
        if ($ids === []) {
            session()->flash('error', 'No hay activos seleccionados.');
            $this->closeMaintModal();

            return;
        }

        if (count($ids) > 500) {
            $this->addError('mt_title', 'Seleccione como máximo 500 activos por operación.');

            return;
        }

        $assets = InvAsset::whereIn('id', $ids)->pluck('id')->all();
        if (count($assets) !== count($ids)) {
            session()->flash('error', 'Algunos activos ya no existen. Actualice la página.');
            $this->closeMaintModal();

            return;
        }

        $endDate = $this->mt_end_date !== '' && $this->mt_end_date !== null ? $this->mt_end_date : null;

        if ($endDate === null && ! $this->mt_allow_multiple_open) {
            $conflicting = InvAsset::query()
                ->whereIn('id', $ids)
                ->whereHas('maintenances', fn ($q) => $q->whereNull('end_date'))
                ->get(['id', 'internal_tag', 'name']);
            if ($conflicting->isNotEmpty()) {
                $tags = $conflicting->map(fn ($a) => $a->internal_tag ?: '#'.$a->id)->implode(', ');
                $this->addError(
                    'mt_title',
                    "Hay activos con mantenimiento abierto: {$tags}. Cierre el anterior, use fecha de cierre en este registro o marque «Permitir otro mantenimiento abierto»."
                );

                return;
            }
        }

        $title = Str::of($this->mt_title)->squish()->toString();
        $diagnosis = Str::of($this->mt_diagnosis)->trim()->toString();
        $solution = $this->mt_solution !== null && $this->mt_solution !== ''
            ? Str::of($this->mt_solution)->trim()->toString()
            : null;

        $adminId = auth()->id() ?? 1;
        $cost = $this->mt_cost !== '' && $this->mt_cost !== null ? round((float) $this->mt_cost, 2) : 0.0;
        $originId = (int) $this->mt_origin_id;
        $modalityId = (int) $this->mt_modality_id;

        try {
            DB::transaction(function () use ($assets, $title, $diagnosis, $solution, $cost, $endDate, $adminId, $originId, $modalityId) {
                foreach ($assets as $assetId) {
                    InvMaintenance::create([
                        'asset_id' => $assetId,
                        'origin_id' => $originId,
                        'modality_id' => $modalityId,
                        'title' => $title,
                        'diagnosis' => $diagnosis,
                        'solution' => $solution,
                        'cost' => $cost,
                        'start_date' => $this->mt_start_date,
                        'end_date' => $endDate,
                        'logged_by' => $adminId,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'No se pudo guardar el mantenimiento. Intente de nuevo o contacte a sistemas.');
            $this->closeMaintModal();
            $this->resetSelection();

            return;
        }

        $n = count($assets);
        session()->flash('message', "Se registró el mantenimiento en {$n} activo(s).");
        $this->closeMaintModal();
        $this->resetSelection();
    }

    public function render()
    {
        $assets = $this->baseQuery()
            ->with(['category', 'status', 'sede'])
            ->withCount(['maintenances as open_maintenances_count' => fn ($q) => $q->whereNull('end_date')])
            ->orderByDesc('id')
            ->paginate($this->perPage);

        $traceAsset = null;
        if ($this->showTraceModal && $this->trace_asset_id) {
            $traceAsset = InvAsset::query()
                ->with([
                    'maintenances' => fn ($q) => $q->with(['logger', 'origin', 'modality'])->orderByDesc('start_date'),
                ])
                ->find($this->trace_asset_id);
        }

        return view('livewire.inventory.inventory-maintenance-index', [
            'assets' => $assets,
            'traceAsset' => $traceAsset,
            'maintenanceOrigins' => InvMaintenanceOrigin::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'maintenanceModalities' => InvMaintenanceModality::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'categories' => InvCategory::select('id', 'name')->orderBy('name')->get(),
            'statuses' => InvStatus::select('id', 'name')->orderBy('name')->get(),
            'sedes' => Sede::orderBy('sede')->get(),
        ]);
    }
}
