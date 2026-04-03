<?php

namespace App\Livewire\Inventory;

use App\Models\InvStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StatusManager extends Component
{
    // Variables
    public $statuses;
    public $name, $badge_class, $assignable, $status_id;
    public $isOpen = false;

    public $showConfirmModal = false;
    public $confirmTitle = '';
    public $confirmMessage = '';
    public $confirmButtonText = 'Confirmar';
    public $confirmButtonClass = 'btn-danger';
    public $confirmTargetId = null;

    // Reglas
    protected $rules = [
        'name' => 'required|min:3',
        'badge_class' => 'required',
        'assignable' => 'boolean'
    ];

    public function mount(): void
    {
        abort_unless(Auth::check() && Auth::user()->can('manage inventory config'), 403);
    }

    public function render()
    {
        $this->statuses = InvStatus::orderBy('name')->get();
        return view('livewire.inventory.status-manager')
            ->extends('admin.layout', ['title' => ' | Estatus'])->section('content');
    }

    // --- CRUD ---

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->badge_class = 'secondary';
        $this->assignable = 1; // Por defecto Sí es asignable
        $this->status_id = null;
    }

    public function store()
    {
        $this->validate();

        InvStatus::updateOrCreate(['id' => $this->status_id], [
            'name' => strtoupper($this->name), // Guardamos en mayúsculas por orden
            'badge_class' => $this->badge_class,
            'assignable' => $this->assignable
        ]);

        session()->flash('message', $this->status_id ? 'Estatus actualizado.' : 'Estatus creado exitosamente.');

        $this->closeModal();
    }

    public function edit($id)
    {
        $status = InvStatus::findOrFail($id);
        $this->status_id = $id;
        $this->name = $status->name;
        $this->badge_class = $status->badge_class;
        $this->assignable = $status->assignable;

        $this->openModal();
    }

    public function openConfirmDelete($id)
    {
        $this->confirmTargetId = $id;
        $this->confirmTitle = 'Eliminar estatus';
        $this->confirmMessage = '¿Está seguro de eliminar este estatus? Solo es posible si ningún activo lo está usando.';
        $this->confirmButtonText = 'Sí, eliminar';
        $this->confirmButtonClass = 'btn-danger';
        $this->showConfirmModal = true;
    }

    public function confirmModalConfirm()
    {
        if ($this->confirmTargetId) {
            $this->delete($this->confirmTargetId);
        }
        $this->confirmModalCancel();
    }

    public function confirmModalCancel()
    {
        $this->showConfirmModal = false;
        $this->confirmTargetId = null;
    }

    public function delete($id)
    {
        $status = InvStatus::find($id);
        if (!$status) return;

        if ($status->assets()->count() > 0) {
            session()->flash('error', 'No se puede eliminar: Hay equipos usando este estatus.');
            return;
        }

        $status->delete();
        session()->flash('message', 'Estatus eliminado.');
    }
}