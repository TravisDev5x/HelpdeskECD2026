<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class MonitorRowsSheetExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    protected $rows;
    protected $headings;
    protected $title;

    public function __construct(array $rows, array $headings, $title = 'Datos')
    {
        $this->rows = $rows;
        $this->headings = $headings;
        $this->title = $title;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return $this->title;
    }
}

