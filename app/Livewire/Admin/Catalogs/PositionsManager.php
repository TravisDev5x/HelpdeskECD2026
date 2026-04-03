<?php

namespace App\Livewire\Admin\Catalogs;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PositionsManager extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showInactive = false;
    public int $perPage = 15;

    public ?int $positionId = null;
    public string $name = '';
    public string $area = '';
    public ?int $departmentId = null;
    public ?string $extension = null;
    public bool $isEditing = false;

    protected $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        abort_unless(Auth::check() && Auth::user()->can('read positions'), 403);
    }

    public function render()
    {
        $query = Position::query()->with('department');

        if ($this->showInactive) {
            $query->withTrashed();
        }

        if (trim($this->search) !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('area', 'like', $term)
                    ->orWhereHas('department', function ($departmentQuery) use ($term) {
                        $departmentQuery->where('name', 'like', $term);
                    });
            });
        }

        $positions = $query
            ->orderBy('deleted_at')
            ->orderBy('name')
            ->paginate($this->perPage);

        $departments = Department::orderBy('name')->get(['id', 'name']);

        return view('livewire.admin.catalogs.positions-manager', [
            'positions' => $positions,
            'departments' => $departments,
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
        abort_unless(Auth::user()->can('create position'), 403);
        $this->resetForm();
        $this->dispatch('open-position-modal');
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        abort_unless(Auth::user()->can('update position'), 403);
        $record = Position::withTrashed()->findOrFail($id);
        $this->positionId = $record->id;
        $this->name = $record->name;
        $this->area = (string) $record->area;
        $this->departmentId = $record->department_id;
        $this->extension = $record->extension !== null ? (string) $record->extension : null;
        $this->isEditing = true;
        $this->resetValidation();
        $this->dispatch('open-position-modal');
    }

    public function save(): void
    {
        abort_unless(Auth::user()->can($this->positionId ? 'update position' : 'create position'), 403);

        $validated = $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:255', 'unique:positions,name,' . ($this->positionId ?? 'NULL') . ',id'],
            'area' => ['required', 'string', 'min:3', 'max:255'],
            'departmentId' => ['nullable', 'integer', 'exists:departments,id'],
            'extension' => ['nullable', 'numeric'],
        ]);

        $payload = [
            'name' => trim($validated['name']),
            'area' => trim($validated['area']),
            'department_id' => $validated['departmentId'] ?? null,
            'extension' => $validated['extension'] !== null ? (string) $validated['extension'] : null,
        ];

        if ($this->positionId) {
            Position::withTrashed()->findOrFail($this->positionId)->update($payload);
            session()->flash('message', 'Puesto actualizado');
        } else {
            Position::create($payload);
            session()->flash('message', 'Puesto guardado');
        }

        $this->resetForm();
        $this->dispatch('close-position-modal');
    }

    public function suspend(int $id): void
    {
        abort_unless(Auth::user()->can('delete position'), 403);
        $record = Position::findOrFail($id);

        $activeUsers = $record->users()->whereNull('deleted_at')->count();
        if ($activeUsers > 0) {
            session()->flash(
                'error',
                "No se puede suspender el puesto. Tiene {$activeUsers} usuario(s) activo(s) asignado(s)."
            );
            return;
        }

        $record->delete();
        session()->flash('message', 'Puesto suspendido');
    }

    public function restore(int $id): void
    {
        abort_unless(Auth::user()->can('delete position'), 403);
        $record = Position::withTrashed()->findOrFail($id);
        $record->restore();
        session()->flash('message', 'Puesto activado');
    }

    private function resetForm(): void
    {
        $this->positionId = null;
        $this->name = '';
        $this->area = '';
        $this->departmentId = null;
        $this->extension = null;
        $this->isEditing = false;
        $this->resetValidation();
    }
}

