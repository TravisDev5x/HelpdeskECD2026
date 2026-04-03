<?php

namespace App\Livewire\Inventory;

use App\Models\InvLabel;
use App\Models\Sede;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class LabelManager extends Component
{
    public $labels;

    public $sedes;

    public $label_id;

    public $sede_id = '';

    public $name = '';

    public $is_active = true;

    public $isOpen = false;

    public $showConfirmModal = false;

    public $confirmTitle = '';

    public $confirmMessage = '';

    public $confirmButtonText = 'Confirmar';

    public $confirmButtonClass = 'btn-danger';

    public $confirmTargetId = null;

    public function mount(): void
    {
        abort_unless(Auth::check() && $this->canManageLabels(), 403);
        $this->sedes = Sede::query()->orderBy('sede')->get();
    }

    public function render()
    {
        $this->labels = InvLabel::query()
            ->with(['sede'])
            ->orderBy('name')
            ->get();

        return view('livewire.inventory.label-manager')
            ->extends('admin.layout', ['title' => ' | Etiquetas'])
            ->section('content');
    }

    public function create(): void
    {
        abort_unless($this->canManageLabels(), 403);
        $this->resetInputFields();
        $this->isOpen = true;
    }

    public function edit(int $id): void
    {
        abort_unless($this->canManageLabels(), 403);

        $record = InvLabel::findOrFail($id);
        $this->label_id = $record->id;
        $this->sede_id = (string) $record->sede_id;
        $this->name = $record->name;
        $this->is_active = (bool) $record->is_active;
        $this->resetValidation();
        $this->isOpen = true;
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->resetInputFields();
    }

    public function store(): void
    {
        abort_unless($this->canManageLabels(), 403);

        $this->name = Str::upper(trim((string) $this->name));
        $this->validate([
            'sede_id' => [
                'required',
                'integer',
                Rule::exists('sedes', 'id'),
                Rule::unique('inv_labels', 'sede_id')->ignore($this->label_id),
            ],
            'name' => [
                'required',
                'string',
                'min:2',
                'max:120',
                Rule::unique('inv_labels', 'name')->ignore($this->label_id),
            ],
            'is_active' => 'boolean',
        ], [
            'sede_id.unique' => 'La sede ya tiene una etiqueta registrada. Edite la existente.',
            'name.unique' => 'La etiqueta ya existe en el catálogo.',
        ]);

        InvLabel::updateOrCreate(
            ['id' => $this->label_id],
            [
                'sede_id' => (int) $this->sede_id,
                'name' => $this->name,
                'is_active' => (bool) $this->is_active,
            ]
        );

        session()->flash('message', $this->label_id ? 'Etiqueta actualizada.' : 'Etiqueta creada.');
        $this->closeModal();
    }

    public function openConfirmDelete(int $id): void
    {
        abort_unless($this->canManageLabels(), 403);

        $record = InvLabel::find($id);
        if (! $record) {
            return;
        }

        $assetsCount = $record->assets()->count();
        if ($assetsCount > 0) {
            session()->flash('error', 'No se puede eliminar: hay '.$assetsCount.' activos usando esta etiqueta.');

            return;
        }

        $this->confirmTargetId = $id;
        $this->confirmTitle = 'Eliminar etiqueta';
        $this->confirmMessage = '¿Está seguro de eliminar esta etiqueta?';
        $this->confirmButtonText = 'Sí, eliminar';
        $this->confirmButtonClass = 'btn-danger';
        $this->showConfirmModal = true;
    }

    public function confirmModalConfirm(): void
    {
        abort_unless($this->canManageLabels(), 403);
        if ($this->confirmTargetId) {
            $this->delete($this->confirmTargetId);
        }
        $this->confirmModalCancel();
    }

    public function confirmModalCancel(): void
    {
        $this->showConfirmModal = false;
        $this->confirmTargetId = null;
    }

    public function delete(int $id): void
    {
        abort_unless($this->canManageLabels(), 403);
        $record = InvLabel::find($id);
        if (! $record) {
            return;
        }

        if ($record->assets()->count() > 0) {
            session()->flash('error', 'No se puede eliminar: hay activos usando esta etiqueta.');

            return;
        }

        $record->delete();
        session()->flash('message', 'Etiqueta eliminada.');
    }

    private function resetInputFields(): void
    {
        $this->label_id = null;
        $this->sede_id = '';
        $this->name = '';
        $this->is_active = true;
        $this->resetValidation();
    }

    private function canManageLabels(): bool
    {
        $user = Auth::user();

        return $user && ($user->can('manage inventory labels') || $user->can('manage inventory config'));
    }
}
