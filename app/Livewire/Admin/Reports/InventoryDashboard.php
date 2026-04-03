<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Product;
use App\Models\User;
use App\Support\Authorization\UserPrimaryRole;
use App\Support\Inventory\ProductOwnerCatalog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class InventoryDashboard extends Component
{
    public string $filterName = '';
    public string $filterBrand = '';
    public string $filterStatus = '';

    public $products;
    public $productsMarca;
    public $productsStatus;
    public $users;

    public function mount(): void
    {
        abort_unless(Auth::check() && Auth::user()->can('read reports inventory'), 403);

        $ownerToExclude = ProductOwnerCatalog::excludedOwnerForPeerDashboard(UserPrimaryRole::name());
        $cacheKey = sprintf('inventory-dashboard:%s', $ownerToExclude);

        $data = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($ownerToExclude) {
            $products = Product::query()
                ->selectRaw('name')
                ->selectRaw('count(id) cantidad')
                ->where('owner', '!=', $ownerToExclude)
                ->whereIn('company_id', [1, 14])
                ->groupBy('name')
                ->orderByDesc('cantidad')
                ->get();

            $productsMarca = Product::query()
                ->selectRaw('name')
                ->selectRaw('marca')
                ->selectRaw('count(name) cantidad')
                ->where('owner', '!=', $ownerToExclude)
                ->whereIn('company_id', [1, 14])
                ->groupBy('name')
                ->groupBy('marca')
                ->orderByDesc('cantidad')
                ->get();

            // Aggregate by status in a single grouped query.
            $productsStatus = Product::query()
                ->selectRaw('name')
                ->selectRaw('count(*) as cantidad')
                ->selectRaw('SUM(CASE WHEN status = "OPERABLE" THEN 1 ELSE 0 END) as OPERABLE')
                ->selectRaw('SUM(CASE WHEN status = "INOPERABLE" THEN 1 ELSE 0 END) as INOPERABLE')
                ->selectRaw('SUM(CASE WHEN status = "CONSUMIBLE" THEN 1 ELSE 0 END) as CONSUMIBLE')
                ->selectRaw('SUM(CASE WHEN status = "STOCK" THEN 1 ELSE 0 END) as STOCK')
                ->selectRaw('SUM(CASE WHEN status = "ROBADO" THEN 1 ELSE 0 END) as ROBADO')
                ->selectRaw('SUM(CASE WHEN status = "RECICLADO" THEN 1 ELSE 0 END) as RECICLADO')
                ->selectRaw('SUM(CASE WHEN status = "EN_REPARACION" THEN 1 ELSE 0 END) as EN_REPARACION')
                ->where('owner', '!=', $ownerToExclude)
                ->whereIn('company_id', [1, 14])
                ->groupBy('name')
                ->orderByDesc('cantidad')
                ->get();

            $users = User::query()
                ->join('products', 'users.id', '=', 'products.employee_id')
                ->where('products.owner', '!=', $ownerToExclude)
                ->selectRaw('users.id')
                ->selectRaw('users.name')
                ->selectRaw('users.ap_paterno')
                ->selectRaw('users.ap_materno')
                ->selectRaw('count(products.id) as cantidad')
                ->groupBy('users.id', 'users.name', 'users.ap_paterno', 'users.ap_materno')
                ->orderByDesc('cantidad')
                ->get();

            return compact('products', 'productsMarca', 'productsStatus', 'users');
        });

        $this->products = $data['products'];
        $this->productsMarca = $data['productsMarca'];
        $this->productsStatus = $data['productsStatus'];
        $this->users = $data['users'];
    }

    public function applyFilters(): void
    {
        $this->dispatch('inventory-table-refresh');
    }

    public function clearFilters(): void
    {
        $this->filterName = '';
        $this->filterBrand = '';
        $this->filterStatus = '';
        $this->dispatch('inventory-table-refresh');
    }

    public function render()
    {
        $name = trim($this->filterName);
        $brand = trim($this->filterBrand);
        $status = trim($this->filterStatus);
        $statusColumn = in_array($status, ['OPERABLE', 'INOPERABLE', 'CONSUMIBLE', 'STOCK', 'ROBADO', 'RECICLADO', 'EN_REPARACION'], true)
            ? $status
            : null;

        $products = collect($this->products);
        $productsMarca = collect($this->productsMarca);
        $productsStatus = collect($this->productsStatus);
        $users = collect($this->users);

        if ($name !== '') {
            $products = $products->filter(fn ($row) => stripos((string) $row->name, $name) !== false)->values();
            $productsMarca = $productsMarca->filter(fn ($row) => stripos((string) $row->name, $name) !== false)->values();
            $productsStatus = $productsStatus->filter(fn ($row) => stripos((string) $row->name, $name) !== false)->values();
        }

        if ($brand !== '') {
            $productsMarca = $productsMarca->filter(fn ($row) => stripos((string) ($row->marca ?? ''), $brand) !== false)->values();
        }

        if ($statusColumn !== null) {
            $productsStatus = $productsStatus
                ->filter(fn ($row) => (int) ($row->{$statusColumn} ?? 0) > 0)
                ->values();
        }

        if ($name !== '') {
            $users = $users->filter(function ($row) use ($name) {
                $fullName = trim(($row->name ?? '') . ' ' . ($row->ap_paterno ?? '') . ' ' . ($row->ap_materno ?? ''));
                return stripos($fullName, $name) !== false;
            })->values();
        }

        return view('livewire.admin.reports.inventory-dashboard', compact(
            'products',
            'productsMarca',
            'productsStatus',
            'users'
        ));
    }
}

