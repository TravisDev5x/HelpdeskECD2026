<?php

namespace App\Livewire\Inventory;

use App\Livewire\Inventory\Concerns\BuildsAssignmentSummary;
use App\Livewire\Inventory\Concerns\GuardsInventoryV2Filters;
use App\Models\InvAsset;
use App\Models\Sede;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Listado paginado: activos con responsable (detalle por fila).
 */
class AssignmentsIndex extends Component
{
    use BuildsAssignmentSummary;
    use GuardsInventoryV2Filters;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $user_filter = '';

    #[Url(except: '')]
    public string $sede_filter = '';

    /** '' = todos, active = responsable en nómina, baja = responsable dado de baja (soft delete) */
    #[Url(except: '')]
    public string $assignee_employment = '';

    #[Url(except: 25)]
    public int $perPage = 25;

    public function mount(): void
    {
        abort_unless(Auth::check() && Auth::user()->can('read inventory'), 403);
        $this->stripUnauthorizedInventoryFilters([
            'search' => 'search',
            'assignee' => 'user_filter',
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

    public function updatingUserFilter(): void
    {
        if (! $this->userCanInventoryFilter('assignee')) {
            $this->user_filter = '';
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

        $assigneeIdsGlobal = InvAsset::query()
            ->whereNotNull('current_user_id')
            ->distinct()
            ->pluck('current_user_id');

        $assignees = $this->assigneesForDropdown();

        $sedes = Sede::orderBy('sede')->get();

        return view('livewire.inventory.assignments-index', [
            'assets' => $assets,
            'assignees' => $assignees,
            'sedes' => $sedes,
            'totalAssignedAssets' => InvAsset::whereNotNull('current_user_id')->count(),
            'totalAssignees' => $assigneeIdsGlobal->unique()->count(),
        ])
            ->extends('admin.layout', ['title' => ' | Asignaciones por activo'])
            ->section('content');
    }
}
