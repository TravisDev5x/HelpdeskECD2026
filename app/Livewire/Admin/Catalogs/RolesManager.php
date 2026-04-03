<?php

namespace App\Livewire\Admin\Catalogs;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class RolesManager extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 15;

    protected $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        abort_unless(Auth::check() && Auth::user()->can('read roles'), 403);
    }

    public function render()
    {
        $query = Role::query()->withCount('permissions');

        if (trim($this->search) !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        $roles = $query
            ->orderBy('id')
            ->paginate($this->perPage);

        return view('livewire.admin.catalogs.roles-manager', [
            'roles' => $roles,
        ]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function deleteRole(int $id): void
    {
        abort_unless(Auth::user()->can('delete role'), 403);

        $role = Role::findOrFail($id);
        $role->delete();

        session()->flash('message', 'Rol eliminado correctamente');
    }
}

