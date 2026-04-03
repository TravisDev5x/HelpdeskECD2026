<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MonitorWorkbookExport implements WithMultipleSheets
{
    protected $summaryRows;
    protected $dataRows;
    protected $dataHeadings;
    protected $dataTitle;

    public function __construct(array $summaryRows, array $dataRows, array $dataHeadings, $dataTitle = 'Datos')
    {
        $this->summaryRows = $summaryRows;
        $this->dataRows = $dataRows;
        $this->dataHeadings = $dataHeadings;
        $this->dataTitle = $dataTitle;
    }

    public function sheets(): array
    {
        return [
            new MonitorRowsSheetExport($this->summaryRows, ['Campo', 'Valor'], 'Resumen'),
            new MonitorRowsSheetExport($this->dataRows, $this->dataHeadings, $this->dataTitle),
        ];
    }
}

