<?php

namespace App\Livewire\Inventory\Concerns;

use App\Support\InventoryV2FilterPermissions;
use Illuminate\Support\Facades\Auth;

trait GuardsInventoryV2Filters
{
    protected function userCanInventoryFilter(string $filterKey): bool
    {
        return InventoryV2FilterPermissions::userMayUse(Auth::user(), $filterKey);
    }

    /**
     * Limpia propiedades públicas cuando el rol no tiene permiso (tras mount / URL).
     *
     * @param  array<string, string>  $map  filterKey => nombre de propiedad del componente
     */
    protected function stripUnauthorizedInventoryFilters(array $map): void
    {
        foreach ($map as $filterKey => $prop) {
            if ($this->userCanInventoryFilter($filterKey)) {
                continue;
            }
            $cur = $this->{$prop} ?? null;
            if (is_int($cur)) {
                $this->{$prop} = 0;
            } elseif (is_bool($cur)) {
                $this->{$prop} = false;
            } else {
                $this->{$prop} = '';
            }
        }
    }

    protected function denyInventoryFilterValue(string $filterKey, string $property): void
    {
        if (! $this->userCanInventoryFilter($filterKey)) {
            $this->{$property} = '';
        }
    }
}
