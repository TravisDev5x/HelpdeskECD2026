<?php

namespace App\Livewire\Admin\Roles;

use App\Support\PermissionCatalog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

class PermissionsSelector extends Component
{
    public string $search = '';

    public array $selected = [];

    public array $permissions = [];

    public function mount(array $selected = []): void
    {
        abort_unless(Auth::check() && Auth::user()->can('read roles'), 403);

        $this->selected = collect($selected)->map(fn ($id) => (int) $id)->values()->all();

        $this->permissions = Permission::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function ($permission) {
                $name = (string) $permission->name;
                $meta = PermissionCatalog::metadata($name);

                return [
                    'id' => (int) $permission->id,
                    'name' => $name,
                    'display_name' => $meta['label'],
                    'group' => $meta['group_label'],
                    'group_sort' => $meta['group_sort'],
                    'description' => $meta['description'],
                ];
            })
            ->values()
            ->all();
    }

    public function render()
    {
        $selectedPermissions = collect($this->permissions)
            ->whereIn('id', $this->selected)
            ->sortBy('display_name')
            ->values()
            ->all();

        return view('livewire.admin.roles.permissions-selector', [
            'groupedPermissions' => $this->groupedPermissions(),
            'selectedCount' => count($this->selected),
            'selectedPermissions' => $selectedPermissions,
        ]);
    }

    public function selectVisible(): void
    {
        $visible = collect($this->filteredPermissions())->pluck('id')->all();
        $this->selected = collect(array_merge($this->selected, $visible))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function clearAll(): void
    {
        $this->selected = [];
    }

    private function groupedPermissions(): array
    {
        $filtered = collect($this->filteredPermissions());
        $byGroup = $filtered->groupBy('group');
        $sortByGroupLabel = $filtered->mapWithKeys(fn ($p) => [$p['group'] => $p['group_sort']]);

        return $byGroup
            ->sortBy(fn ($items, $groupLabel) => $sortByGroupLabel[$groupLabel] ?? 999)
            ->map(fn ($items) => $items->sortBy('display_name')->values()->all())
            ->all();
    }

    private function filteredPermissions(): array
    {
        $term = mb_strtolower(trim($this->search));

        if ($term === '') {
            return $this->permissions;
        }

        return collect($this->permissions)
            ->filter(function ($permission) use ($term) {
                $haystack = mb_strtolower(implode(' ', array_filter([
                    $permission['name'],
                    $permission['display_name'],
                    $permission['group'],
                    $permission['description'] ?? '',
                ])));

                return str_contains($haystack, $term);
            })
            ->values()
            ->all();
    }
}
