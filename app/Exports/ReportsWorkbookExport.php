<?php

namespace App\Exports;

use App\Exports\Sheets\ReportsDetailSheet;
use App\Exports\Sheets\ReportsSummarySheet;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReportsWorkbookExport implements WithMultipleSheets
{
    use Exportable;

    /**
     * @param  array{totalTickets: int, totalFinalizados: int, totalPendientes: int, totalIncidencias: int}  $kpis
     * @param  array<int, array{name: string, total: int}>  $porEstatus
     * @param  array<int, array{name: string, total: int}>  $porArea
     * @param  array<int, array{name: string, total: int}>  $porResponsable
     */
    public function __construct(
        private readonly string $fechaInicio,
        private readonly string $fechaFin,
        private readonly array $kpis,
        private readonly array $porEstatus,
        private readonly array $porArea,
        private readonly array $porResponsable,
        private readonly Collection $services,
    ) {}

    public function sheets(): array
    {
        return [
            new ReportsSummarySheet(
                $this->fechaInicio,
                $this->fechaFin,
                $this->kpis,
                $this->porEstatus,
                $this->porArea,
                $this->porResponsable,
            ),
            new ReportsDetailSheet($this->services, $this->fechaInicio, $this->fechaFin),
        ];
    }
}
