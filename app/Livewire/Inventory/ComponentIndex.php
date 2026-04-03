<?php

namespace App\Livewire\Inventory;

use App\Livewire\Inventory\Concerns\GuardsInventoryV2Filters;
use App\Support\InventoryV2FilterPermissions;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Componente;      // Tu modelo de Componentes
use App\Models\InvAsset; // Tu modelo de Activos V2
use Illuminate\Validation\Rule;
use App\Models\InvComponentMovement;
use Illuminate\Support\Facades\Auth;

class ComponentIndex extends Component
{
    use GuardsInventoryV2Filters;
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    // --- VARIABLES DE FILTROS Y PAGINACIÓN ---
    public $search = '';
    public $status_filter = ''; 
    public $perPage = 10; 

    // --- VARIABLES PARA EL MODAL DE ASIGNACIÓN ---
    public $showAssignModal = false;
    public $selected_component;
    public $selected_asset_id;
    public $asset_search = '';
    public $asset_results = [];

    // --- VARIABLES PARA EL MODAL CRUD (CREAR/EDITAR) ---
    public $showFormModal = false;
    public $isEditMode = false;
    public $component_id; 
    
    // Campos del formulario
    public $name, $marca, $modelo, $serie, $capacidad, $observacion, $costo;

    // --- MODAL DE CONFIRMACIÓN ---
    public $showConfirmModal = false;
    public $confirmTitle = '';
    public $confirmMessage = '';
    public $confirmButtonText = 'Confirmar';
    public $confirmButtonClass = 'btn-warning';
    public $confirmAction = '';
    public $confirmTargetId = null;

    // --- MODAL DE DETALLE / HISTORIAL ---
    public $showDetailModal = false;
    public $detailComponent = null;
    public $componentHistory = [];

    // --- RESETEO DE PÁGINA AL FILTRAR ---
    public function updatingSearch()
    {
        if (! $this->userCanInventoryFilter('search')) {
            $this->search = '';
            return;
        }
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        if (! $this->userCanInventoryFilter('status')) {
            $this->status_filter = '';
            return;
        }
        $this->resetPage();
    }

    public function updatingPerPage() { $this->resetPage(); }

    public function mount(): void
    {
        abort_unless(Auth::check() && Auth::user()->can('read inventory'), 403);
        $this->stripUnauthorizedInventoryFilters([
            'search' => 'search',
            'status' => 'status_filter',
        ]);
    }

    // =================================================================
    //  RENDER: AQUÍ ESTÁ LA LÓGICA "INTELIGENTE"
    // =================================================================
    public function render()
    {
        $search = (string) InventoryV2FilterPermissions::effectiveScalar(Auth::user(), 'search', $this->search, '');
        $statusFilter = InventoryV2FilterPermissions::userMayUse(Auth::user(), 'status') ? $this->status_filter : '';

        $components = Componente::query()
            ->with(['asset', 'equipo']) // Carga relaciones para evitar consultas N+1
            ->when($search !== '', function ($q) use ($search) {
                $term = '%' . $search . '%';
                
                // Agrupamos los OR para que no rompan los filtros de estado
                $q->where(function($sub) use ($term){
                    
                    // 1. Buscar por datos del propio componente
                    $sub->where('name', 'like', $term)
                        ->orWhere('serie', 'like', $term)
                        ->orWhere('marca', 'like', $term)
                        ->orWhere('modelo', 'like', $term)
                        
                        // 2. BUSCADOR INTELIGENTE: Buscar por datos de la PC donde está instalado
                        ->orWhereHas('asset', function($query) use ($term) {
                            $query->where('internal_tag', 'like', $term) // Busca por Tag (ej: 0492)
                                  ->orWhere('name', 'like', $term)       // Busca por Nombre PC
                                  ->orWhere('serial', 'like', $term);    // Busca por Serie PC
                        });
                });
            })
            // Filtros de Estado (Stock, Asignado, Suspendido)
            ->when($statusFilter, function ($q) use ($statusFilter) {
                switch ($statusFilter) {
                    case 'stock':
                        $q->whereNull('asset_id')->where('status', '!=', 'SUSPENDIDO');
                        break;
                    case 'assigned':
                        $q->whereNotNull('asset_id')->where('status', '!=', 'SUSPENDIDO');
                        break;
                    case 'suspended':
                        $q->where('status', 'SUSPENDIDO');
                        break;
                }
            })
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        return view('livewire.inventory.component-index', [
            'components' => $components
        ])->extends('admin.layout', ['title' => ' | Componentes'])->section('content');
    }

    // =================================================================
    //  CRUD: CREAR Y EDITAR
    // =================================================================
    public function create()
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->showFormModal = true;
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->isEditMode = true;
        $this->component_id = $id;

        $comp = Componente::find($id);
        $this->name = $comp->name;
        $this->marca = $comp->marca;
        $this->modelo = $comp->modelo;
        $this->serie = $comp->serie;
        $this->capacidad = $comp->capacidad;
        $this->costo = $comp->costo;
        $this->observacion = $comp->observacion;

        $this->showFormModal = true;
    }

    public function store()
    {
        // Validaciones
        $rules = [
            'name' => 'required|min:2',
            'marca' => 'required',
            // Valida que la serie sea única, pero ignora el actual si estamos editando
            'serie' => ['nullable', Rule::unique('components', 'serie')->ignore($this->component_id)],
            'costo' => 'nullable|numeric'
        ];
        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'serie' => $this->serie,
            'capacidad' => $this->capacidad,
            'costo' => $this->costo,
            'observacion' => $this->observacion,
        ];

        if ($this->isEditMode) {
            Componente::find($this->component_id)->update($data);
            session()->flash('message', 'Componente actualizado correctamente.');
        } else {
            $data['status'] = 'OPERABLE';
            $data['fecha_ingreso'] = now();
            Componente::create($data);
            session()->flash('message', 'Componente registrado en Stock.');
        }

        $this->showFormModal = false;
    }

    public function closeFormModal() { $this->showFormModal = false; }

    private function resetForm()
    {
        $this->reset(['name', 'marca', 'modelo', 'serie', 'capacidad', 'costo', 'observacion', 'component_id']);
        $this->resetErrorBag();
    }

    public function openDetailModal($id)
    {
        $this->detailComponent = \App\Models\InvComponent::with([
            'movements' => function ($q) {
                $q->with(['asset', 'admin', 'originAsset'])
                  ->latest('date')
                  ->take(30);
            },
        ])->findOrFail($id);

        $this->componentHistory = $this->detailComponent->movements;
        $this->showDetailModal = true;
    }

    // =================================================================
    //  LÓGICA DE ASIGNACIÓN (VINCULAR A PC)
    // =================================================================
    public function openAssignModal($id)
    {
        $this->selected_component = Componente::with('asset')->find($id);
        $this->selected_asset_id = $this->selected_component->asset_id;
        
        $this->asset_search = '';
        $this->asset_results = [];
        
        // Si ya tiene PC, la mostramos primero
        if($this->selected_asset_id && $this->selected_component->asset){
             $this->asset_results = [$this->selected_component->asset];
        } else {
             // Sugerencias (los últimos agregados)
             $this->asset_results = InvAsset::latest()->take(3)->get();
        }

        $this->showAssignModal = true;
    }

    // Buscador interno del Modal (Para encontrar la PC destino)
    public function updatedAssetSearch()
    {
        if(strlen($this->asset_search) > 1){
            $term = '%' . $this->asset_search . '%';
            $this->asset_results = InvAsset::query()
                ->where(function ($q) use ($term) {
                    $q->where('internal_tag', 'like', $term)
                        ->orWhere('serial', 'like', $term)
                        ->orWhere('name', 'like', $term)
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(specs, '$.ip')) LIKE ?", [$term]);
                })
                ->take(10)->get();
        }
    }

    public function selectAsset($id)
    {
        // Toggle selección
        $this->selected_asset_id = ($this->selected_asset_id == $id) ? null : $id;
    }

    public function saveAssignment()
    {
        $previousAssetId = $this->selected_component->asset_id;
        $newAssetId = $this->selected_asset_id ?: null;

        $this->selected_component->update([
            'asset_id' => $newAssetId
        ]);

        if ($newAssetId && $newAssetId != $previousAssetId) {
            InvComponentMovement::create([
                'component_id' => $this->selected_component->id,
                'asset_id'     => $newAssetId,
                'admin_id'     => auth()->id() ?? 1,
                'type'         => 'ASIGNAR',
                'date'         => now(),
                'notes'        => null,
            ]);
        }

        if (!$newAssetId && $previousAssetId) {
            InvComponentMovement::create([
                'component_id' => $this->selected_component->id,
                'asset_id'     => $previousAssetId,
                'admin_id'     => auth()->id() ?? 1,
                'type'         => 'RETIRAR',
                'date'         => now(),
                'notes'        => null,
            ]);
        }
        
        session()->flash('message', $this->selected_asset_id ? 'Asignado correctamente.' : 'Enviado a STOCK.');
        $this->showAssignModal = false;
    }

    public function closeAssignModal() { $this->showAssignModal = false; }

    // =================================================================
    //  SUSPENDER / BAJA (con modal de confirmación)
    // =================================================================
    public function openConfirmToggleSuspend($id)
    {
        $component = Componente::find($id);
        if (!$component) return;
        $this->confirmTargetId = $id;
        $this->confirmAction = 'toggleSuspend';
        if ($component->status === 'SUSPENDIDO') {
            $this->confirmTitle = 'Reactivar componente';
            $this->confirmMessage = '¿Desea reactivar este componente? Volverá a estar disponible para asignación.';
            $this->confirmButtonText = 'Sí, reactivar';
            $this->confirmButtonClass = 'btn-success';
        } else {
            $this->confirmTitle = 'Marcar como baja / suspendido';
            $this->confirmMessage = '¿Desea marcar este componente como SUSPENDIDO/BAJA? No podrá asignarse hasta que lo reactive.';
            $this->confirmButtonText = 'Sí, suspender';
            $this->confirmButtonClass = 'btn-warning';
        }
        $this->showConfirmModal = true;
    }

    public function toggleSuspend($id)
    {
        $component = Componente::find($id);
        if (!$component) return;
        if ($component->status === 'SUSPENDIDO') {
            $component->update(['status' => 'OPERABLE']);
            session()->flash('message', 'Componente reactivado exitosamente.');
        } else {
            $component->update(['status' => 'SUSPENDIDO']);

            InvComponentMovement::create([
                'component_id' => $component->id,
                'asset_id'     => null,
                'admin_id'     => auth()->id() ?? 1,
                'type'         => 'BAJA',
                'date'         => now(),
                'notes'        => null,
            ]);

            session()->flash('message', 'Componente marcado como SUSPENDIDO/BAJA.');
        }
    }

    public function confirmModalConfirm()
    {
        if ($this->confirmAction === 'toggleSuspend' && $this->confirmTargetId) {
            $this->toggleSuspend($this->confirmTargetId);
        }
        $this->confirmModalCancel();
    }

    public function confirmModalCancel()
    {
        $this->showConfirmModal = false;
        $this->confirmAction = '';
        $this->confirmTargetId = null;
    }
}