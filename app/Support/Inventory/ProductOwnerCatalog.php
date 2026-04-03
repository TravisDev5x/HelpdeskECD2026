<?php

namespace App\Support\Inventory;

use Illuminate\Database\Eloquent\Builder;

/**
 * Reglas de negocio por rol sobre el campo products.owner (y CTG contenido) compartidas entre
 * listados, exportaciones y reportes. Misma semántica que el código anterior en cada sitio.
 */
final class ProductOwnerCatalog
{
    /**
     * Valor de products.owner a excluir en agregados del dashboard de inventario (Livewire).
     * Mantenimiento ve datos excluyendo Sistemas; el resto excluye Mantenimiento.
     */
    public static function excludedOwnerForPeerDashboard(?string $primaryRoleName): string
    {
        return $primaryRoleName === 'Mantenimiento' ? 'Sistemas' : 'Mantenimiento';
    }

    /**
     * Filtro por owner en exportación Excel completa (Mantenimiento vs Sistemas).
     */
    public static function ownerForFullSpreadsheetExport(string $primaryRoleName): string
    {
        return $primaryRoleName === 'Mantenimiento' ? 'Mantenimiento' : 'Sistemas';
    }

    /**
     * Área de catálogo CTG para create/edit de productos (ctg_id = 1).
     */
    public static function ctgContenidoAreaForRole(string $primaryRoleName): string
    {
        return $primaryRoleName === 'Mantenimiento' ? 'Mantenimiento' : 'Sistemas';
    }

    /**
     * Filtros del listado DataTables de productos (index / getProducts).
     */
    public static function applyProductsListOwnerFilter(Builder $query, string $roleName): void
    {
        if ($roleName === 'Mantenimiento') {
            $query->where('owner', 'Mantenimiento');
        } elseif ($roleName === 'Operaciones') {
            // Compatibilidad con históricos: en producción legacy muchos registros de
            // Operaciones se guardaron con owner = Sistemas.
            $query->whereIn('owner', ['Operaciones', 'Sistemas']);
        } elseif ($roleName === 'Control') {
            $query->where('modelo', 'A15');
        } else {
            $query->where('owner', 'Sistemas');
        }
    }
}
