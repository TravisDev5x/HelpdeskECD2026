<?php

namespace App\Livewire\Inventory;

use App\Models\InvCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CategoryManager extends Component
{
    public $categories;
    public $name, $category_id;
    public $isOpen = false;

    public $showConfirmModal = false;
    public $confirmTitle = '';
    public $confirmMessage = '';
    public $confirmButtonText = 'Confirmar';
    public $confirmButtonClass = 'btn-danger';
    public $confirmTargetId = null;

    public function mount(): void
    {
        abort_unless(Auth::check() && Auth::user()->can('manage inventory config'), 403);
    }

    public function render()
    {
        $this->categories = InvCategory::orderBy('name')->get();
        return view('livewire.inventory.category-manager')
            ->extends('admin.layout', ['title' => ' | Categorías'])
            ->section('content');
    }

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
        $this->category_id = null;
        $this->resetValidation(); 
    }

    // --- AQUÍ ESTÁ EL CAMBIO "MANUAL" ---
    public function store()
    {
        // 1. Validamos formato básico
        $this->validate([
            'name' => 'required|min:3'
        ]);

        // 2. Normalizamos el texto (Mayúsculas y sin espacios extra)
        $nameInput = strtoupper(trim($this->name));

        // 3. CONSULTA MANUAL DE DUPLICADOS
        // "Búscame si existe este nombre exacto..."
        $query = InvCategory::where('name', $nameInput);

        // "...pero si estoy editando, ¡ignórame a mí mismo!"
        if ($this->category_id) {
            $query->where('id', '!=', $this->category_id);
        }

        // Si la consulta encuentra algo, es un duplicado real
        if ($query->exists()) {
            $this->addError('name', 'El nombre "' . $nameInput . '" ya existe en otra categoría.');
            return;
        }

        // 4. Guardar
        InvCategory::updateOrCreate(['id' => $this->category_id], [
            'name' => $nameInput
        ]);

        session()->flash('message', $this->category_id ? 'Categoría actualizada.' : 'Categoría creada.');
        $this->closeModal();
    }

    public function edit($id)
    {
        $category = InvCategory::findOrFail($id);
        $this->category_id = $id;
        $this->name = $category->name;
        
        $this->resetValidation(); 
        $this->openModal();
    }

    public function openConfirmDelete($id)
    {
        $this->confirmTargetId = $id;
        $this->confirmTitle = 'Eliminar categoría';
        $this->confirmMessage = '¿Está seguro de eliminar esta categoría? Solo es posible si no tiene activos asociados.';
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
        $category = InvCategory::find($id);
        if (!$category) return;

        if ($category->assets()->count() > 0) {
            session()->flash('error', 'No se puede eliminar: Hay ' . $category->assets()->count() . ' activos en esta categoría.');
            return;
        }

        $category->delete();
        session()->flash('message', 'Categoría eliminada.');
    }
}