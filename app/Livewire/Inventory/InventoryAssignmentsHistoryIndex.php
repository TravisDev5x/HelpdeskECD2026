<?php

namespace App\Livewire\Inventory;

use App\Models\User;
use App\Support\InvAssignmentHistoryQuery;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryAssignmentsHistoryIndex extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'assignments')]
    public string $typeScope = 'assignments';

    #[Url(except: '')]
    public string $filterUserId = '';

    #[Url(except: '')]
    public string $filterAdminId = '';

    #[Url(except: '')]
    public string $dateFrom = '';

    #[Url(except: '')]
    public string $dateTo = '';

    #[Url(except: '')]
    public string $filterBatchUuid = '';

    public function mount(): void
    {
        $u = Auth::user();
        abort_unless(
            $u && ($u->can('read inventory') || $u->can('read inventory assignment history')),
            403
        );
        if ($this->dateFrom === '' && $this->dateTo === '') {
            $this->dateTo = now()->format('Y-m-d');
            $this->dateFrom = now()->subMonths(3)->format('Y-m-d');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeScope(): void
    {
        $this->resetPage();
    }

    public function updatingFilterUserId(): void
    {
        $this->resetPage();
    }

    public function updatingFilterAdminId(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function updatingFilterBatchUuid(): void
    {
        $this->resetPage();
    }

    public function resetFiltros(): void
    {
        $this->search = '';
        $this->typeScope = 'assignments';
        $this->filterUserId = '';
        $this->filterAdminId = '';
        $this->filterBatchUuid = '';
        $this->dateTo = now()->format('Y-m-d');
        $this->dateFrom = now()->subMonths(3)->format('Y-m-d');
        $this->resetPage();
    }

    /**
     * @return array<string, mixed>
     */
    protected function filtrosArray(): array
    {
        return [
            'search' => $this->search,
            'type_scope' => $this->typeScope,
            'user_id' => $this->filterUserId,
            'admin_id' => $this->filterAdminId,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'batch_uuid' => $this->filterBatchUuid,
        ];
    }

    public function render()
    {
        $query = InvAssignmentHistoryQuery::base();
        InvAssignmentHistoryQuery::applyFilters($query, $this->filtrosArray());
        $movements = $query->orderByDesc('date')->orderByDesc('id')->paginate(25);

        $usersForFilter = User::query()
            ->select('id', 'name', 'ap_paterno', 'ap_materno')
            ->orderBy('name')
            ->limit(800)
            ->get();

        $exportQuery = array_filter($this->filtrosArray(), fn ($v) => $v !== '' && $v !== null);

        return view('livewire.inventory.inventory-assignments-history-index', [
            'movements' => $movements,
            'usersForFilter' => $usersForFilter,
            'exportQuery' => $exportQuery,
        ])->extends('admin.layout', ['title' => ' | Historial asignaciones V2'])
            ->section('content');
    }
}
