<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InvAssetsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $collection;

    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    public function collection()
    {
        return $this->collection;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Etiqueta',
            'Nombre',
            'Serie',
            'Categoría',
            'Estatus',
            'Condición',
            'Empresa',
            'Sede',
            'Ubicación',
            'Asignado a',
            'Costo',
            'Garantía hasta',
            'Alta',
        ];
    }

    public function map($asset): array
    {
        return [
            $asset->id,
            $asset->internal_tag ?? '',
            $asset->name,
            $asset->serial ?? '',
            $asset->category->name ?? '',
            $asset->status->name ?? '',
            $asset->condition ?? '',
            $asset->company->name ?? '',
            $asset->sede ? (isset($asset->sede->sede) ? $asset->sede->sede : $asset->sede->name ?? '') : '',
            $asset->ubicacion ? (isset($asset->ubicacion->ubicacion) ? $asset->ubicacion->ubicacion : $asset->ubicacion->name ?? '') : '',
            $asset->currentUser ? $asset->currentUser->name : 'Libre',
            $asset->cost ? number_format($asset->cost, 2) : '',
            $asset->warranty_expiry ? $asset->warranty_expiry->format('d/m/Y') : '',
            $asset->created_at ? $asset->created_at->format('d/m/Y H:i') : '',
        ];
    }
}
