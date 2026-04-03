<?php

namespace App\Livewire\Admin\Catalogs;

use App\Models\Area;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentsManager extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showInactive = false;
    public int $perPage = 15;

    public ?int $departmentId = null;
    public string $name = '';
    public ?int $areaId = null;
    public bool $isEditing = false;

    protected $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        abort_unless(Auth::check() && Auth::user()->can('read departments'), 403);
    }

    public function render()
    {
        $query = Department::query()->with('area');

        if ($this->showInactive) {
            $query->withTrashed();
        }

        if (trim($this->search) !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhereHas('area', function ($areaQuery) use ($term) {
                        $areaQuery->where('name', 'like', $term);
                    });
            });
        }

        $departments = $query
            ->orderBy('deleted_at')
            ->orderBy('name')
            ->paginate($this->perPage);

        $areas = Area::orderBy('name')->get(['id', 'name']);

        return view('livewire.admin.catalogs.departments-manager', [
            'departments' => $departments,
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
        abort_unless(Auth::user()->can('create department'), 403);
        $this->resetForm();
        $this->dispatch('open-department-modal');
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        abort_unless(Auth::user()->can('update department'), 403);
        $record = Department::withTrashed()->findOrFail($id);
        $this->departmentId = $record->id;
        $this->name = $record->name;
        $this->areaId = $record->area_id;
        $this->isEditing = true;
        $this->resetValidation();
        $this->dispatch('open-department-modal');
    }

    public function save(): void
    {
        abort_unless(Auth::user()->can($this->departmentId ? 'update department' : 'create department'), 403);

        $validated = $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:255', 'unique:departments,name,' . ($this->departmentId ?? 'NULL') . ',id'],
            'areaId' => ['nullable', 'integer', 'exists:areas,id'],
        ]);

        $payload = [
            'name' => trim($validated['name']),
            'area_id' => $validated['areaId'] ?? null,
        ];

        if ($this->departmentId) {
            Department::withTrashed()->findOrFail($this->departmentId)->update($payload);
            session()->flash('message', 'Departamento actualizado');
        } else {
            Department::create($payload);
            session()->flash('message', 'Departamento guardado');
        }

        $this->resetForm();
        $this->dispatch('close-department-modal');
    }

    public function suspend(int $id): void
    {
        abort_unless(Auth::user()->can('delete department'), 403);
        $record = Department::findOrFail($id);

        $activePositions = $record->positions()->whereNull('deleted_at')->count();
        $activeUsers = $record->users()->whereNull('deleted_at')->count();

        if ($activePositions > 0 || $activeUsers > 0) {
            session()->flash(
                'error',
                "No se puede suspender el departamento. Dependencias activas: Puestos ({$activePositions}), Usuarios ({$activeUsers})."
            );
            return;
        }

        $record->delete();
        session()->flash('message', 'Departamento suspendido');
    }

    public function restore(int $id): void
    {
        abort_unless(Auth::user()->can('delete department'), 403);
        $record = Department::withTrashed()->findOrFail($id);
        $record->restore();
        session()->flash('message', 'Departamento activado');
    }

    private function resetForm(): void
    {
        $this->departmentId = null;
        $this->name = '';
        $this->areaId = null;
        $this->isEditing = false;
        $this->resetValidation();
    }
}

