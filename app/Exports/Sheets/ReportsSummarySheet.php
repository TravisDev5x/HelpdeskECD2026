<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportsSummarySheet implements FromArray, WithTitle, WithEvents, WithCharts
{
    private const SHEET_TITLE = 'Resumen';

    private int $dataRowCount = 1;

    private int $chartTopRow = 25;

    /** @var array<int, array{name: string, total: int|string}> */
    private array $normalizedEstatus = [];

    /** @var array<int, array{name: string, total: int|string}> */
    private array $normalizedArea = [];

    /** @var array<int, array{name: string, total: int|string}> */
    private array $normalizedResponsable = [];

    private int $responsableCount = 1;

    /** Fila Excel: título bloque responsables */
    private int $responsableTitleRow = 10;

    /** Fila Excel: encabezados Responsable | Tickets */
    private int $responsableHeaderRow = 11;

    /** Primera fila de datos responsables */
    private int $responsableDataStartRow = 12;

    /** Fila Excel: "Tickets por estatus" / "Tickets por área" */
    private int $estatusAreaTitleRow = 24;

    /** Primera fila de datos estatus/área (tablas paralelas) */
    private int $dataStartRow = 26;

    public function __construct(
        private readonly string $fechaInicio,
        private readonly string $fechaFin,
        /** @var array{totalTickets: int, totalFinalizados: int, totalPendientes: int, totalIncidencias: int} */
        private readonly array $kpis,
        /** @var array<int, array{name: string, total: int}> */
        private readonly array $porEstatus,
        /** @var array<int, array{name: string, total: int}> */
        private readonly array $porArea,
        /** @var array<int, array{name: string, total: int}> */
        private readonly array $porResponsable,
    ) {
        $this->prepareDataRanges();
    }

    private function prepareDataRanges(): void
    {
        $resp = array_values(array_slice($this->porResponsable, 0, 10));
        if ($resp === []) {
            $resp = [['name' => 'Sin datos', 'total' => 0]];
        }
        $this->normalizedResponsable = $resp;
        $this->responsableCount = count($this->normalizedResponsable);

        $e = array_values(array_slice($this->porEstatus, 0, 10));
        $a = array_values(array_slice($this->porArea, 0, 10));
        if ($e === []) {
            $e = [['name' => 'Sin datos', 'total' => 0]];
        }
        if ($a === []) {
            $a = [['name' => 'Sin datos', 'total' => 0]];
        }
        $this->dataRowCount = max(count($e), count($a));
        $this->normalizedEstatus = $e;
        $this->normalizedArea = $a;
        while (count($this->normalizedEstatus) < $this->dataRowCount) {
            $this->normalizedEstatus[] = ['name' => '', 'total' => ''];
        }
        while (count($this->normalizedArea) < $this->dataRowCount) {
            $this->normalizedArea[] = ['name' => '', 'total' => ''];
        }

        $this->responsableTitleRow = 10;
        $this->responsableHeaderRow = 11;
        $this->responsableDataStartRow = 12;
        $c = $this->responsableCount;
        $this->estatusAreaTitleRow = 12 + $c + 1;
        $this->dataStartRow = 12 + $c + 3;
        $this->chartTopRow = $this->dataStartRow + $this->dataRowCount + 2;
    }

    public function title(): string
    {
        return self::SHEET_TITLE;
    }

    public function mainTitle(): string
    {
        return 'Reportes Tickets — Fecha inicio: '.$this->fechaInicio.' — Fecha final: '.$this->fechaFin;
    }

    public function array(): array
    {
        $rows = [];
        $rows[] = [
            $this->mainTitle(),
            '',
            '',
            '',
            '',
            '',
            '',
        ];
        $rows[] = [
            'Generado',
            now()->format('Y-m-d H:i'),
            '',
            '',
            '',
            '',
            '',
        ];
        $rows[] = [];
        $rows[] = ['Indicadores clave', ''];
        $rows[] = ['Tickets totales', $this->kpis['totalTickets']];
        $rows[] = ['Tickets cerrados', $this->kpis['totalFinalizados']];
        $rows[] = ['Tickets pendientes', $this->kpis['totalPendientes']];
        $rows[] = ['Incidencias (periodo)', $this->kpis['totalIncidencias']];
        $rows[] = [];
        $rows[] = ['Quién toma más tickets (responsables)', '', '', '', '', '', ''];
        $rows[] = ['Responsable', 'Tickets asignados', '', '', '', '', ''];

        foreach ($this->normalizedResponsable as $r) {
            $rows[] = [
                $r['name'],
                $r['total'],
                '',
                '',
                '',
                '',
                '',
            ];
        }

        $rows[] = [];
        $rows[] = ['Tickets por estatus', '', '', '', 'Tickets por área'];
        $rows[] = ['Estatus', 'Total', '', '', 'Área', 'Total'];

        for ($i = 0; $i < $this->dataRowCount; $i++) {
            $e = $this->normalizedEstatus[$i];
            $ar = $this->normalizedArea[$i];
            $rows[] = [
                $e['name'],
                $e['total'],
                '',
                '',
                $ar['name'],
                $ar['total'],
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $sheet->mergeCells('A1:G1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle('A4:B8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('A5:A8')->getFont()->setBold(true);
                $sheet->getStyle('A4:B4')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE9ECEF');
                $sheet->getStyle('A4:B4')->getFont()->setBold(true);

                $rt = $this->responsableTitleRow;
                $sheet->mergeCells("A{$rt}:G{$rt}");
                $sheet->getStyle("A{$rt}")->getFont()->setBold(true);
                $sheet->getStyle("A{$rt}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE9ECEF');

                $rh = $this->responsableHeaderRow;
                $rLast = $this->responsableDataStartRow + $this->responsableCount - 1;
                $sheet->getStyle("A{$rh}:B{$rh}")->getFont()->setBold(true);
                $sheet->getStyle("A{$rh}:B{$rLast}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $hdr = $this->dataStartRow - 1;
                $sheet->getStyle("A{$hdr}:B{$hdr}")->getFont()->setBold(true);
                $sheet->getStyle("E{$hdr}:F{$hdr}")->getFont()->setBold(true);
                $last = $this->dataStartRow + $this->dataRowCount - 1;
                $sheet->getStyle("A{$hdr}:B{$last}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("E{$hdr}:F{$last}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $sheet->getColumnDimension('A')->setWidth(28);
                $sheet->getColumnDimension('B')->setWidth(18);
                $sheet->getColumnDimension('E')->setWidth(28);
                $sheet->getColumnDimension('F')->setWidth(12);
            },
        ];
    }

    public function charts(): array
    {
        $st = $this->dataStartRow;
        $en = $this->dataStartRow + $this->dataRowCount - 1;
        $t = self::SHEET_TITLE;

        $catE = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_STRING,
            "'{$t}'!\$A\${$st}:\$A\${$en}",
            null,
            $this->dataRowCount
        );
        $valE = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_NUMBER,
            "'{$t}'!\$B\${$st}:\$B\${$en}",
            null,
            $this->dataRowCount
        );
        $seriesE = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_CLUSTERED,
            [0],
            [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, null, null, 1, ['Total'])],
            [$catE],
            [$valE],
            DataSeries::DIRECTION_COL
        );
        $plotE = new PlotArea(null, [$seriesE]);
        $chartE = new Chart(
            'chart_estatus',
            new Title('Tickets por estatus'),
            new Legend(Legend::POSITION_BOTTOM, null, false),
            $plotE
        );
        $top = $this->chartTopRow;
        $chartE->setTopLeftPosition("A{$top}");
        $chartE->setBottomRightPosition('F' . ($top + 16));

        $catA = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_STRING,
            "'{$t}'!\$E\${$st}:\$E\${$en}",
            null,
            $this->dataRowCount
        );
        $valA = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_NUMBER,
            "'{$t}'!\$F\${$st}:\$F\${$en}",
            null,
            $this->dataRowCount
        );
        $seriesA = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_CLUSTERED,
            [0],
            [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, null, null, 1, ['Total'])],
            [$catA],
            [$valA],
            DataSeries::DIRECTION_COL
        );
        $plotA = new PlotArea(null, [$seriesA]);
        $chartA = new Chart(
            'chart_areas',
            new Title('Tickets por área'),
            new Legend(Legend::POSITION_BOTTOM, null, false),
            $plotA
        );
        $chartA->setTopLeftPosition("H{$top}");
        $chartA->setBottomRightPosition('N' . ($top + 16));

        $rs = $this->responsableDataStartRow;
        $re = $this->responsableDataStartRow + $this->responsableCount - 1;
        $catR = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_STRING,
            "'{$t}'!\$A\${$rs}:\$A\${$re}",
            null,
            $this->responsableCount
        );
        $valR = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_NUMBER,
            "'{$t}'!\$B\${$rs}:\$B\${$re}",
            null,
            $this->responsableCount
        );
        $seriesR = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_CLUSTERED,
            [0],
            [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, null, null, 1, ['Tickets'])],
            [$catR],
            [$valR],
            DataSeries::DIRECTION_COL
        );
        $plotR = new PlotArea(null, [$seriesR]);
        $chartR = new Chart(
            'chart_responsables',
            new Title('Tickets por responsable (top)'),
            new Legend(Legend::POSITION_BOTTOM, null, false),
            $plotR
        );
        $rTop = $this->chartTopRow;
        $chartR->setTopLeftPosition("A" . ($rTop + 18));
        $chartR->setBottomRightPosition('F' . ($rTop + 34));

        return [$chartE, $chartA, $chartR];
    }
}
