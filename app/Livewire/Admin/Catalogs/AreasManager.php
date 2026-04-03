<?php

namespace App\Livewire\Admin\Catalogs;

use App\Models\Area;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AreasManager extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showInactive = false;
    public int $perPage = 15;

    public ?int $areaId = null;
    public string $name = '';
    public bool $isEditing = false;

    protected $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        abort_unless(Auth::check() && Auth::user()->can('read areas'), 403);
    }

    public function render()
    {
        $query = Area::query();

        if ($this->showInactive) {
            $query->withTrashed();
        }

        if (trim($this->search) !== '') {
            $query->where('name', 'like', '%' . trim($this->search) . '%');
        }

        $areas = $query
            ->orderBy('deleted_at')
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.admin.catalogs.areas-manager', [
            'areas' => $areas,
        ]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedShowInactive(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        abort_unless(Auth::user()->can('create area'), 403);
        $this->resetForm();
        $this->isEditing = false;
        $this->dispatch('open-area-modal');
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        abort_unless(Auth::user()->can('update area'), 403);
        $record = Area::withTrashed()->findOrFail($id);
        $this->areaId = $record->id;
        $this->name = $record->name;
        $this->isEditing = true;
        $this->resetValidation();
        $this->dispatch('open-area-modal');
    }

    public function save(): void
    {
        abort_unless(Auth::user()->can($this->areaId ? 'update area' : 'create area'), 403);

        $validated = $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:255', 'unique:areas,name,' . ($this->areaId ?? 'NULL') . ',id'],
        ]);

        $payload = ['name' => trim($validated['name'])];

        if ($this->areaId) {
            Area::withTrashed()->findOrFail($this->areaId)->update($payload);
            session()->flash('message', 'Area actualizada.');
        } else {
            Area::create($payload);
            session()->flash('message', 'Area guardada.');
        }

        $this->resetForm();
        $this->dispatch('close-area-modal');
    }

    public function suspend(int $id): void
    {
        abort_unless(Auth::user()->can('delete area'), 403);
        $record = Area::findOrFail($id);

        $activeDepartments = $record->departments()->whereNull('deleted_at')->count();
        $activeUsers = $record->users()->whereNull('deleted_at')->count();
        $activeCampaigns = $record->campaigns()->whereNull('deleted_at')->count();

        if ($activeDepartments > 0 || $activeUsers > 0 || $activeCampaigns > 0) {
            session()->flash(
                'error',
                "No se puede suspender el área. Dependencias activas: Departamentos ({$activeDepartments}), Usuarios ({$activeUsers}), Campañas ({$activeCampaigns})."
            );
            return;
        }

        $record->delete();
        session()->flash('message', 'Area suspendida.');
    }

    public function restore(int $id): void
    {
        abort_unless(Auth::user()->can('delete area'), 403);
        $record = Area::withTrashed()->findOrFail($id);
        $record->restore();
        session()->flash('message', 'Area activada.');
    }

    private function resetForm(): void
    {
        $this->areaId = null;
        $this->name = '';
        $this->isEditing = false;
        $this->resetValidation();
    }
}

