<?php

namespace App\Livewire\Inventory\Concerns;

use App\Models\InvAsset;
use App\Models\User;
use App\Support\InventoryV2FilterPermissions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BuildsAssignmentSummary
{
    protected function assignmentsBaseQuery(): Builder
    {
        $q = InvAsset::query()->whereNotNull('current_user_id');

        $search = (string) InventoryV2FilterPermissions::effectiveScalar(Auth::user(), 'search', $this->search ?? '', '');
        if ($search !== '') {
            $term = '%'.$search.'%';
            $q->where(function ($sub) use ($term) {
                $sub->where('name', 'like', $term)
                    ->orWhere('internal_tag', 'like', $term)
                    ->orWhere('serial', 'like', $term);
            });
        }

        if (
            ($this->user_filter ?? '') !== ''
            && InventoryV2FilterPermissions::userMayUse(Auth::user(), 'assignee')
        ) {
            $q->where('current_user_id', (int) $this->user_filter);
        }

        if (
            ($this->sede_filter ?? '') !== ''
            && InventoryV2FilterPermissions::userMayUse(Auth::user(), 'sede')
        ) {
            $q->where('sede_id', (int) $this->sede_filter);
        }

        $this->applyAssigneeEmploymentFilter($q);

        return $q;
    }

    /**
     * Responsable activo (en nómina) vs dado de baja (soft delete en users).
     * Requiere propiedad opcional assignee_employment: '' | active | baja
     */
    protected function applyAssigneeEmploymentFilter(Builder $q): void
    {
        if (! InventoryV2FilterPermissions::userMayUse(Auth::user(), 'assignee_employment')) {
            return;
        }

        $state = property_exists($this, 'assignee_employment') ? (string) $this->assignee_employment : '';

        if ($state === 'active') {
            $q->whereExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('users')
                    ->whereColumn('users.id', 'inv_assets.current_user_id')
                    ->whereNull('users.deleted_at');
            });

            return;
        }

        if ($state === 'baja') {
            $q->whereExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('users')
                    ->whereColumn('users.id', 'inv_assets.current_user_id')
                    ->whereNotNull('users.deleted_at');
            });
        }
    }

    /**
     * Activos asignados acotados por búsqueda y sede (sin filtro por responsable),
     * para poblar el combo solo con personas que sí tienen equipos en ese contexto.
     */
    protected function assignmentsContextForAssigneeDropdownQuery(): Builder
    {
        $q = InvAsset::query()->whereNotNull('current_user_id');

        $search = (string) InventoryV2FilterPermissions::effectiveScalar(Auth::user(), 'search', $this->search ?? '', '');
        if ($search !== '') {
            $term = '%'.$search.'%';
            $q->where(function ($sub) use ($term) {
                $sub->where('name', 'like', $term)
                    ->orWhere('internal_tag', 'like', $term)
                    ->orWhere('serial', 'like', $term);
            });
        }

        if (
            ($this->sede_filter ?? '') !== ''
            && InventoryV2FilterPermissions::userMayUse(Auth::user(), 'sede')
        ) {
            $q->where('sede_id', (int) $this->sede_filter);
        }

        $this->applyAssigneeEmploymentFilter($q);

        return $q;
    }

    /**
     * Usuarios con al menos un activo asignado en el contexto (búsqueda + sede).
     * Incluye withTrashed() para responsables dados de baja que aún tienen activos.
     * Si hay user_filter activo y no cae en el conjunto, se añade para no romper el select.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    protected function assigneesForDropdown()
    {
        $ids = $this->assignmentsContextForAssigneeDropdownQuery()
            ->distinct()
            ->pluck('current_user_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if (
            ($this->user_filter ?? '') !== ''
            && InventoryV2FilterPermissions::userMayUse(Auth::user(), 'assignee')
        ) {
            $uid = (int) $this->user_filter;
            if (! $ids->contains($uid)) {
                $ids = $ids->push($uid)->unique()->values();
            }
        }

        if ($ids->isEmpty()) {
            return User::query()->whereRaw('1 = 0')->get();
        }

        return User::query()
            ->whereIn('id', $ids)
            ->withTrashed()
            ->orderBy('name')
            ->orderBy('ap_paterno')
            ->get();
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{user_id: int, user: ?User, count: int}>
     */
    protected function buildSummaryByPerson()
    {
        $countByUser = $this->assignmentsBaseQuery()
            ->selectRaw('current_user_id, COUNT(*) as cnt')
            ->groupBy('current_user_id')
            ->pluck('cnt', 'current_user_id');

        if ($countByUser->isEmpty()) {
            return collect();
        }

        $usersById = User::query()
            ->whereIn('id', $countByUser->keys()->map(fn ($id) => (int) $id)->all())
            ->withTrashed()
            ->get()
            ->keyBy('id');

        return $countByUser
            ->map(function ($count, $userId) use ($usersById) {
                $uid = (int) $userId;

                return [
                    'user_id' => $uid,
                    'user' => $usersById->get($uid),
                    'count' => (int) $count,
                ];
            })
            ->values()
            ->sortByDesc('count')
            ->values();
    }
}
