<?php

namespace App\Livewire\Inventory;

use App\Livewire\Inventory\Concerns\BuildsAssignmentSummary;
use App\Livewire\Inventory\Concerns\GuardsInventoryV2Filters;
use App\Models\InvAsset;
use App\Models\Sede;
use App\Support\InventoryV2FilterPermissions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Activos de inventario V2 asignados unicamente al usuario autenticado.
 */
class OwnAssignmentsIndex extends Component
{
    use BuildsAssignmentSummary;
    use GuardsInventoryV2Filters;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $sede_filter = '';

    #[Url(except: '')]
    public string $assignee_employment = '';

    #[Url(except: 25)]
    public int $perPage = 25;

    public function mount(): void
    {
        abort_unless(Auth::check() && Auth::user()->can('read inventory own assignments'), 403);
        $this->stripUnauthorizedInventoryFilters([
            'search' => 'search',
            'sede' => 'sede_filter',
            'assignee_employment' => 'assignee_employment',
        ]);
    }

    public function updatingSearch(): void
    {
        if (! $this->userCanInventoryFilter('search')) {
            $this->search = '';
            return;
        }
        $this->resetPage();
    }

    public function updatingSedeFilter(): void
    {
        if (! $this->userCanInventoryFilter('sede')) {
            $this->sede_filter = '';
            return;
        }
        $this->resetPage();
    }

    public function updatingAssigneeEmployment(): void
    {
        if (! $this->userCanInventoryFilter('assignee_employment')) {
            $this->assignee_employment = '';
            return;
        }
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    protected function assignmentsBaseQuery(): Builder
    {
        $q = InvAsset::query()
            ->whereNotNull('current_user_id')
            ->where('current_user_id', Auth::id());

        $search = (string) InventoryV2FilterPermissions::effectiveScalar(Auth::user(), 'search', $this->search, '');
        if ($search !== '') {
            $term = '%'.$search.'%';
            $q->where(function ($sub) use ($term) {
                $sub->where('name', 'like', $term)
                    ->orWhere('internal_tag', 'like', $term)
                    ->orWhere('serial', 'like', $term);
            });
        }

        if (
            $this->sede_filter !== ''
            && InventoryV2FilterPermissions::userMayUse(Auth::user(), 'sede')
        ) {
            $q->where('sede_id', (int) $this->sede_filter);
        }

        $this->applyAssigneeEmploymentFilter($q);

        return $q;
    }

    public function render()
    {
        $query = $this->assignmentsBaseQuery()
            ->with([
                'category',
                'status',
                'sede',
                'company',
                'ubicacion',
                'currentUser' => fn ($q) => $q->withTrashed(),
            ])
            ->leftJoin('users', 'users.id', '=', 'inv_assets.current_user_id')
            ->select('inv_assets.*')
            ->orderByDesc('inv_assets.id')
            ->orderByDesc('inv_assets.created_at');

        $assets = $query->paginate($this->perPage);

        $myAssignedCount = InvAsset::query()
            ->where('current_user_id', Auth::id())
            ->count();

        $sedes = Sede::orderBy('sede')->get();
        $canFullInventory = Auth::user()->can('read inventory');

        return view('livewire.inventory.own-assignments-index', [
            'assets' => $assets,
            'sedes' => $sedes,
            'myAssignedCount' => $myAssignedCount,
            'canFullInventory' => $canFullInventory,
        ])
            ->extends('admin.layout', ['title' => ' | Mis equipos asignados (Inventario V2)'])
            ->section('content');
    }
}
