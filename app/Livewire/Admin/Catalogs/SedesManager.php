<?php

namespace App\Livewire\Admin\Catalogs;

use App\Models\Sede;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class SedesManager extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showInactive = false;
    public int $perPage = 15;

    public ?int $sedeId = null;
    public string $sede = '';
    public bool $isEditing = false;

    protected $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        abort_unless(Auth::check() && Auth::user()->can('modulo.sedes'), 403);
    }

    public function render()
    {
        $query = Sede::query();

        if ($this->showInactive) {
            $query->withTrashed();
        }

        if (trim($this->search) !== '') {
            $query->where('sede', 'like', '%' . trim($this->search) . '%');
        }

        $sedes = $query
            ->orderBy('deleted_at')
            ->orderBy('sede')
            ->paginate($this->perPage);

        return view('livewire.admin.catalogs.sedes-manager', [
            'sedes' => $sedes,
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
        $this->resetForm();
        $this->isEditing = false;
        $this->dispatch('open-sede-modal');
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $record = Sede::withTrashed()->findOrFail($id);
        $this->sedeId = $record->id;
        $this->sede = $record->sede;
        $this->isEditing = true;
        $this->resetValidation();
        $this->dispatch('open-sede-modal');
    }

    public function save(): void
    {
        $validated = $this->validate([
            'sede' => ['required', 'string', 'max:150', 'unique:sedes,sede,' . ($this->sedeId ?? 'NULL') . ',id'],
        ]);

        $payload = [
            'sede' => strtoupper(trim($validated['sede'])),
        ];

        if ($this->sedeId) {
            Sede::withTrashed()->findOrFail($this->sedeId)->update($payload);
            session()->flash('message', 'Sede actualizada.');
        } else {
            Sede::create($payload);
            session()->flash('message', 'Sede creada.');
        }

        $this->resetForm();
        $this->dispatch('close-sede-modal');
    }

    public function suspend(int $id): void
    {
        $record = Sede::findOrFail($id);

        $activeUsers = $record->users()->whereNull('users.deleted_at')->count();
        $activeCampaigns = $record->campaigns()->whereNull('deleted_at')->count();

        if ($activeUsers > 0 || $activeCampaigns > 0) {
            session()->flash(
                'error',
                "No se puede suspender la sede. Dependencias activas: Usuarios ({$activeUsers}), Campañas ({$activeCampaigns})."
            );
            return;
        }

        $record->delete();
        session()->flash('message', 'Sede suspendida.');
    }

    public function restore(int $id): void
    {
        $record = Sede::withTrashed()->findOrFail($id);
        $record->restore();
        session()->flash('message', 'Sede activada.');
    }

    private function resetForm(): void
    {
        $this->sedeId = null;
        $this->sede = '';
        $this->isEditing = false;
        $this->resetValidation();
    }
}

