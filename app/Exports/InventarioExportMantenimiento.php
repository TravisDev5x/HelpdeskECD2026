<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use \Maatwebsite\Excel\Sheet;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class InventarioExportMantenimiento implements FromView
{
  //use Exportable;
 

  /**
  * @return \Illuminate\Support\Collection
  */
public function view(): View
    {
      $products = Product::selectRaw('name')
    ->selectRaw('count(name) cantidad')
    ->selectRaw('(SELECT COUNT(status) FROM products p WHERE products.name = p.name AND p.status = "OPERABLE" AND p.company_id = 1) OPERABLE')
    ->selectRaw('(SELECT COUNT(status) FROM products p WHERE products.name = p.name AND p.status = "INOPERABLE" AND p.company_id = 1) INOPERABLE')
    ->selectRaw('(SELECT COUNT(status) FROM products p WHERE products.name = p.name AND p.status = "CONSUMIBLE" AND p.company_id = 1) CONSUMIBLE')
    ->selectRaw('(SELECT COUNT(status) FROM products p WHERE products.name = p.name AND p.status = "STOCK" AND p.company_id = 1) STOCK')
    ->selectRaw('(SELECT COUNT(status) FROM products p WHERE products.name = p.name AND p.status = "ROBADO" AND p.company_id = 1) ROBADO')
    ->selectRaw('(SELECT COUNT(status) FROM products p WHERE products.name = p.name AND p.status = "RECICLADO" AND p.company_id = 1) RECICLADO')
    ->selectRaw('(SELECT COUNT(status) FROM products p WHERE products.name = p.name AND p.status = "EN_REPARACION" AND p.company_id = 1) EN_REPARACION')
    ->where('owner', '!=', 'Sistemas')
    ->where('company_id', 1)
    ->groupBy('name')
    ->orderBy('cantidad', 'desc')
    ->get();

        return view('admin.reports.download', compact('products'));
    }
  // public function collection()
  // {
  
  // }

  // public function registerEvents(): array
  // {
  //   return [
  //     AfterSheet::class => function(AfterSheet $event) {
  //       $cellRange = 'A1:T1'; // All headers
  //       $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(12);
  //       $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setBold(true);
  //     },
  //   ];
  // }

  // public function headings(): array
  // {
  //   return [
  //     'Id',
  //     'Usuario',
  //     'Vacante',
  //     'Id registro',
  //     'Nombre',
  //     'Ap paterno',
  //     'Ap materno',
  //     'Email',
  //     'Telefono',
  //     'Fecha entrevista',
  //     'Status',
  //     'Origen',
  //     'Filtro area',
  //     'Filtro RH',
  //     'Acudio a entrevista',
  //     'Alcaldia',
  //     'Reingreso',
  //     'Experiencia',
  //     'Motivo rechazo'
  //   ];
  // }
}
