<?php

namespace App\Exports;

use App\Models\Product;
use App\Support\Authorization\UserPrimaryRole;
use App\Support\Inventory\ProductOwnerCatalog;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InvetarioCompletoExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    use Exportable;

    public function __construct()
    {
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $owner = ProductOwnerCatalog::ownerForFullSpreadsheetExport(UserPrimaryRole::name() ?? '');

        return Product::leftjoin('users', 'users.id', '=', 'products.employee_id')
            ->leftjoin('positions', 'positions.id', '=', 'users.position_id')
            ->leftjoin('departments', 'departments.id', '=', 'users.department_id')
            ->leftjoin('campaigns', 'campaigns.id', '=', 'users.campaign_id')
            ->select(
                'products.id',
                'products.serie',
                'products.name',
                'products.etiqueta',
                'products.marca',
                'products.modelo',
                'products.medio',
                'products.ip',
                'products.mac',
                'products.observacion',
                'products.status',
                'products.costo',
                'products.fecha_ingreso',
                'products.owner',
                'users.usuario as no_empleado',
                DB::raw("CONCAT(COALESCE(users.name, ''), ' ', COALESCE(users.ap_paterno, ''), ' ', COALESCE(users.ap_materno, '')) as nombre_asignado"),
                'positions.name as puesto',
                'departments.name as departamento',
                'campaigns.name as campania',
            )->where('products.owner', $owner)->get();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $cellRange = 'A1:T1';
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(12);
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setBold(true);
            },
        ];
    }

    public function headings(): array
    {
        return [
            'ID DB',
            'Serie',
            'Nombre',
            'Etiqueta',
            'Marca',
            'Modelo',
            'Medio',
            'IP',
            'Mac',
            'Observaciones',
            'Status',
            'Costo',
            'Fecha de Ingreso',
            'Owner',
            'No. Empleado',
            'Persona Responsable',
            'Puesto',
            'Departamento',
            'Campaña',
        ];
    }
}
