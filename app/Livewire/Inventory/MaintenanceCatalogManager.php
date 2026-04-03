<?php

namespace App\Livewire\Inventory;

use App\Models\InvMaintenance;
use App\Models\InvMaintenanceModality;
use App\Models\InvMaintenanceOrigin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class MaintenanceCatalogManager extends Component
{
    /** Origen del equipo (interno/externo) o modalidad (preventivo/correctivo). */
    public string $editCatalog = '';

    public ?int $itemId = null;

    public string $code = '';

    public string $name = '';

    public int $sort_order = 0;

    public bool $is_active = true;

    public bool $isOpen = false;

    public bool $showConfirmModal = false;

    public string $confirmTitle = '';

    public string $confirmMessage = '';

    public ?int $confirmTargetId = null;

    public string $confirmButtonText = 'Eliminar';

    public string $confirmButtonClass = 'btn-danger';

    public function mount(): void
    {
        abort_unless(Auth::check() && Auth::user()->can('manage inventory maintenance catalogs'), 403);
    }

    public function render()
    {
        $origins = InvMaintenanceOrigin::query()->orderBy('sort_order')->orderBy('name')->get();
        $modalities = InvMaintenanceModality::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('livewire.inventory.maintenance-catalog-manager', [
            'origins' => $origins,
            'modalities' => $modalities,
        ]);
    }

    public function create(string $catalog): void
    {
        abort_unless(Auth::user()->can('manage inventory maintenance catalogs'), 403);
        $this->editCatalog = $catalog === 'modality' ? 'modality' : 'origin';
        $this->itemId = null;
        $this->reset(['code', 'name']);
        $maxSort = $this->editCatalog === 'origin'
            ? InvMaintenanceOrigin::max('sort_order')
            : InvMaintenanceModality::max('sort_order');
        $this->sort_order = (int) ($maxSort ?? 0) + 10;
        $this->is_active = true;
        $this->resetValidation();
        $this->isOpen = true;
    }

    public function edit(string $catalog, int $id): void
    {
        abort_unless(Auth::user()->can('manage inventory maintenance catalogs'), 403);
        $this->editCatalog = $catalog === 'modality' ? 'modality' : 'origin';
        $model = $this->editCatalog === 'origin'
            ? InvMaintenanceOrigin::findOrFail($id)
            : InvMaintenanceModality::findOrFail($id);
        $this->itemId = $model->id;
        $this->code = $model->code;
        $this->name = $model->name;
        $this->sort_order = (int) $model->sort_order;
        $this->is_active = (bool) $model->is_active;
        $this->resetValidation();
        $this->isOpen = true;
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->itemId = null;
        $this->editCatalog = '';
        $this->reset(['code', 'name', 'sort_order', 'is_active']);
        $this->resetValidation();
    }

    public function store(): void
    {
        abort_unless(Auth::user()->can('manage inventory maintenance catalogs'), 403);

        $table = $this->editCatalog === 'modality' ? 'inv_maintenance_modalities' : 'inv_maintenance_origins';
        $modelClass = $this->editCatalog === 'modality' ? InvMaintenanceModality::class : InvMaintenanceOrigin::class;

        $rawCode = trim($this->code);
        $codeNormalized = Str::upper(Str::slug($rawCode, '_'));
        if ($codeNormalized === '' || strlen($codeNormalized) < 2) {
            $codeNormalized = Str::upper(preg_replace('/[^A-Za-z0-9_]/', '_', $rawCode));
        }
        $this->code = $codeNormalized;

        $this->validate([
            'code' => [
                'required',
                'string',
                'max:64',
                'regex:/^[A-Z0-9_]+$/',
                Rule::unique($table, 'code')->ignore($this->itemId),
            ],
            'name' => 'required|string|min:2|max:255',
            'sort_order' => 'required|integer|min:0|max:65535',
            'is_active' => 'boolean',
        ], [
            'code.regex' => 'El código solo puede usar letras mayúsculas, números y guiones bajos.',
        ]);

        $modelClass::updateOrCreate(
            ['id' => $this->itemId],
            [
                'code' => $codeNormalized,
                'name' => Str::of($this->name)->trim()->toString(),
                'sort_order' => $this->sort_order,
                'is_active' => $this->is_active,
            ]
        );

        session()->flash('message', $this->itemId ? 'Registro actualizado.' : 'Registro creado.');
        $this->closeModal();
    }

    public function openConfirmDelete(string $catalog, int $id): void
    {
        abort_unless(Auth::user()->can('manage inventory maintenance catalogs'), 403);
        $isModality = $catalog === 'modality';
        $model = $isModality ? InvMaintenanceModality::find($id) : InvMaintenanceOrigin::find($id);
        if (! $model) {
            return;
        }

        $fk = $isModality ? 'modality_id' : 'origin_id';
        $usage = InvMaintenance::where($fk, $id)->count();
        if ($usage > 0) {
            session()->flash('error', 'No se puede eliminar: hay '.$usage.' mantenimiento(s) que lo usan. Desactive el registro o reasigne los mantenimientos.');

            return;
        }

        $this->editCatalog = $isModality ? 'modality' : 'origin';
        $this->confirmTargetId = $id;
        $this->confirmTitle = 'Eliminar '.($isModality ? 'modalidad' : 'origen');
        $this->confirmMessage = '¿Eliminar «'.$model->name.'»? Esta acción no se puede deshacer.';
        $this->showConfirmModal = true;
    }

    public function confirmModalConfirm(): void
    {
        abort_unless(Auth::user()->can('manage inventory maintenance catalogs'), 403);
        if ($this->confirmTargetId && $this->editCatalog) {
            $model = $this->editCatalog === 'modality'
                ? InvMaintenanceModality::find($this->confirmTargetId)
                : InvMaintenanceOrigin::find($this->confirmTargetId);
            if ($model) {
                $fk = $this->editCatalog === 'modality' ? 'modality_id' : 'origin_id';
                if (InvMaintenance::where($fk, $model->id)->exists()) {
                    session()->flash('error', 'No se puede eliminar: aún hay mantenimientos asociados.');
                } else {
                    $model->delete();
                    session()->flash('message', 'Registro eliminado.');
                }
            }
        }
        $this->confirmModalCancel();
    }

    public function confirmModalCancel(): void
    {
        $this->showConfirmModal = false;
        $this->confirmTargetId = null;
        $this->confirmMessage = '';
        $this->editCatalog = '';
    }
}
