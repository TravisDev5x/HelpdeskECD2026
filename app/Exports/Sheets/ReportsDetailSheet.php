<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ReportsDetailSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithCustomStartCell, WithEvents
{
    use Exportable;

    public function __construct(
        private readonly Collection $services,
        private readonly string $fechaInicio,
        private readonly string $fechaFin,
    ) {}

    public function collection(): Collection
    {
        return $this->services;
    }

    public function title(): string
    {
        return 'Detalle tickets';
    }

    public function startCell(): string
    {
        return 'A2';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $title = 'Reportes Tickets — Fecha inicio: '.$this->fechaInicio.' — Fecha final: '.$this->fechaFin;
                $sheet->mergeCells('A1:L1');
                $sheet->setCellValue('A1', $title);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            },
        ];
    }

    public function headings(): array
    {
        return [
            'Área solicita',
            'Usuario',
            'Falla',
            'Descripción',
            'Solución',
            'Observaciones',
            'Estatus',
            'Fecha solicitud',
            'Fecha cierre',
            'Área atiende',
            'Responsable',
            'Sede',
        ];
    }

    /**
     * @param  \App\Models\DetailService  $row
     * @return array<int, string|int|float|null>
     */
    public function map($row): array
    {
        return [
            $row->area_solicita,
            $row->usuario,
            $row->falla,
            $row->description,
            $row->solution,
            $row->observations,
            $row->status,
            $this->formatDateTime($row->fecha_solicitud),
            $this->formatDateTime($row->fecha_fin),
            $row->area_atiende,
            $row->responsable,
            $row->sede,
        ];
    }

    private function formatDateTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d H:i');
        } catch (\Throwable) {
            return (string) $value;
        }
    }
}
