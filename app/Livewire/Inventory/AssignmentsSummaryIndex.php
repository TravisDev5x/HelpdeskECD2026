<?php

namespace App\Livewire\Inventory;

use App\Livewire\Inventory\Concerns\BuildsAssignmentSummary;
use App\Livewire\Inventory\Concerns\GuardsInventoryV2Filters;
use App\Models\InvAsset;
use App\Models\Sede;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Vista dedicada: resumen de activos asignados agrupado por responsable (sin tabla detallada).
 */
class AssignmentsSummaryIndex extends Component
{
    use BuildsAssignmentSummary;
    use GuardsInventoryV2Filters;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $user_filter = '';

    #[Url(except: '')]
    public string $sede_filter = '';

    /** '' | active | baja — ver trait BuildsAssignmentSummary */
    #[Url(except: '')]
    public string $assignee_employment = '';

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
    }

    public function updatingUserFilter(): void
    {
        if (! $this->userCanInventoryFilter('assignee')) {
            $this->user_filter = '';
            return;
        }
    }

    public function updatingSedeFilter(): void
    {
        if (! $this->userCanInventoryFilter('sede')) {
            $this->sede_filter = '';
            return;
        }
    }

    public function updatingAssigneeEmployment(): void
    {
        if (! $this->userCanInventoryFilter('assignee_employment')) {
            $this->assignee_employment = '';
            return;
        }
    }

    public function render()
    {
        $assigneeIdsGlobal = InvAsset::query()
            ->whereNotNull('current_user_id')
            ->distinct()
            ->pluck('current_user_id');

        $assignees = $this->assigneesForDropdown();

        $sedes = Sede::orderBy('sede')->get();

        $summaryByPerson = $this->buildSummaryByPerson();

        $filteredTotal = $this->assignmentsBaseQuery()->count();

        return view('livewire.inventory.assignments-summary-index', [
            'assignees' => $assignees,
            'sedes' => $sedes,
            'summaryByPerson' => $summaryByPerson,
            'totalAssignedAssets' => InvAsset::whereNotNull('current_user_id')->count(),
            'totalAssignees' => $assigneeIdsGlobal->unique()->count(),
            'filteredTotal' => $filteredTotal,
        ])
            ->extends('admin.layout', ['title' => ' | Resumen de asignaciones'])
            ->section('content');
    }
}
