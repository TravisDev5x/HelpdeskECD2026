<?php

namespace App\Livewire\Admin\Catalogs;

use App\Models\Area;
use App\Models\Campaign;
use App\Models\Did;
use App\Models\Sede;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CampaignsManager extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showInactive = false;
    public int $perPage = 15;

    public ?int $campaignId = null;
    public string $name = '';
    public ?int $didId = null;
    public ?int $areaId = null;
    public ?int $sedeId = null;
    public bool $isEditing = false;

    protected $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        abort_unless(Auth::check() && Auth::user()->can('read campaigns'), 403);
    }

    public function render()
    {
        $query = Campaign::query()->with(['did', 'area', 'sede']);

        if ($this->showInactive) {
            $query->withTrashed();
        }

        if (trim($this->search) !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhereHas('did', fn ($didQuery) => $didQuery->where('did', 'like', $term))
                    ->orWhereHas('area', fn ($areaQuery) => $areaQuery->where('name', 'like', $term))
                    ->orWhereHas('sede', fn ($sedeQuery) => $sedeQuery->where('sede', 'like', $term));
            });
        }

        $campaigns = $query
            ->orderBy('deleted_at')
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.admin.catalogs.campaigns-manager', [
            'campaigns' => $campaigns,
            'dids' => Did::orderBy('did')->get(['id', 'did']),
            'areas' => Area::orderBy('name')->get(['id', 'name']),
            'sedes' => Sede::orderBy('sede')->get(['id', 'sede']),
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
        abort_unless(Auth::user()->can('create campaign'), 403);
        $this->resetForm();
        $this->dispatch('open-campaign-modal');
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        abort_unless(Auth::user()->can('update campaign'), 403);
        $record = Campaign::withTrashed()->findOrFail($id);
        $this->campaignId = $record->id;
        $this->name = $record->name;
        $this->didId = $record->did_id;
        $this->areaId = $record->area_id;
        $this->sedeId = $record->sede_id;
        $this->isEditing = true;
        $this->resetValidation();
        $this->dispatch('open-campaign-modal');
    }

    public function save(): void
    {
        abort_unless(Auth::user()->can($this->campaignId ? 'update campaign' : 'create campaign'), 403);

        $validated = $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:255', 'unique:campaigns,name,' . ($this->campaignId ?? 'NULL') . ',id'],
            'didId' => ['nullable', 'integer', 'exists:dids,id'],
            'areaId' => ['nullable', 'integer', 'exists:areas,id'],
            'sedeId' => ['nullable', 'integer', 'exists:sedes,id'],
        ]);

        $payload = [
            'name' => trim($validated['name']),
            'did_id' => $validated['didId'] ?? null,
            'area_id' => $validated['areaId'] ?? null,
            'sede_id' => $validated['sedeId'] ?? null,
        ];

        if ($this->campaignId) {
            Campaign::withTrashed()->findOrFail($this->campaignId)->update($payload);
            session()->flash('message', 'Campaña actualizada');
        } else {
            Campaign::create($payload);
            session()->flash('message', 'Campaña guardada');
        }

        $this->resetForm();
        $this->dispatch('close-campaign-modal');
    }

    public function suspend(int $id): void
    {
        abort_unless(Auth::user()->can('delete campaign'), 403);
        $campaign = Campaign::findOrFail($id);
        $activeUsers = $campaign->users()->whereNull('deleted_at')->count();

        if ($activeUsers > 0) {
            session()->flash(
                'error',
                "No se puede suspender la campaña. Tiene {$activeUsers} usuario(s) activo(s) asignado(s)."
            );
            return;
        }

        $campaign->delete();
        session()->flash('message', 'Campaña suspendida');
    }

    public function restore(int $id): void
    {
        abort_unless(Auth::user()->can('delete campaign'), 403);
        Campaign::withTrashed()->findOrFail($id)->restore();
        session()->flash('message', 'Campaña activada');
    }

    private function resetForm(): void
    {
        $this->campaignId = null;
        $this->name = '';
        $this->didId = null;
        $this->areaId = null;
        $this->sedeId = null;
        $this->isEditing = false;
        $this->resetValidation();
    }
}

