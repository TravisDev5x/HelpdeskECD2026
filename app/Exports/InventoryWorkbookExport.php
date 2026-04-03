<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InventoryWorkbookExport implements WithMultipleSheets
{
    public function __construct(
        private readonly Collection $assets,
        private readonly array $meta = []
    ) {}

    public function sheets(): array
    {
        $summaryRows = $this->buildSummaryRows();
        $allRows = $this->buildAllAssetsRows();
        $categoryRows = $this->buildByCategoryRows();
        $statusRows = $this->buildByStatusRows();

        return [
            new MonitorRowsSheetExport($summaryRows, ['Campo', 'Valor'], 'Resumen'),
            new MonitorRowsSheetExport($allRows, $this->allAssetsHeadings(), 'Todos_los_activos'),
            new MonitorRowsSheetExport($categoryRows, ['Categoría', 'Cantidad', 'Costo total', 'Asignados', 'Libres'], 'Por_categoria'),
            new MonitorRowsSheetExport($statusRows, ['Estatus', 'Cantidad', 'Costo total', '% del total'], 'Por_estatus'),
        ];
    }

    private function buildSummaryRows(): array
    {
        $total = $this->assets->count();
        $assigned = $this->assets->whereNotNull('current_user_id')->count();
        $free = $total - $assigned;
        $cost = (float) $this->assets->sum(fn ($a) => (float) ($a->cost ?? 0));

        return [
            ['Generado', now()->format('Y-m-d H:i:s')],
            ['Total activos', $total],
            ['Asignados', $assigned],
            ['Libres', $free],
            ['Costo total', number_format($cost, 2)],
            ['Campo fecha', $this->cleanCell($this->meta['date_field'] ?? 'created_at')],
            ['Desde', $this->cleanCell($this->meta['date_from'] ?? '')],
            ['Hasta', $this->cleanCell($this->meta['date_to'] ?? '')],
            ['Filtro categoría', $this->cleanCell($this->meta['category_label'] ?? 'Todas')],
            ['Filtro estatus', $this->cleanCell($this->meta['status_label'] ?? 'Todos')],
            ['Filtro sede', $this->cleanCell($this->meta['sede_label'] ?? 'Todas')],
            ['Filtro responsable', $this->cleanCell($this->meta['assignee_label'] ?? 'Todos')],
            ['Filtro etiqueta', $this->cleanCell($this->meta['label_filter_label'] ?? 'Todas')],
            ['Búsqueda', $this->cleanCell($this->meta['search'] ?? '')],
        ];
    }

    private function allAssetsHeadings(): array
    {
        return [
            'ID', 'Etiqueta', 'Etiqueta de sede', 'Nombre', 'Serie', 'Categoría', 'Estatus', 'Condición',
            'Empresa', 'Sede', 'Ubicación', 'Asignado a', 'Owner', 'Medio', 'Costo',
            'Fecha compra', 'Garantía', 'Alta',
        ];
    }

    private function buildAllAssetsRows(): array
    {
        return $this->assets->map(function ($asset) {
            $specs = (array) ($asset->specs ?? []);
            return [
                $asset->id,
                $this->cleanCell($asset->internal_tag ?? ''),
                $this->cleanCell($asset->label->name ?? ''),
                $this->cleanCell($asset->name ?? ''),
                $this->cleanCell($asset->serial ?? ''),
                $this->cleanCell($asset->category->name ?? ''),
                $this->cleanCell($asset->status->name ?? ''),
                $this->cleanCell($asset->condition ?? ''),
                $this->cleanCell($asset->company->name ?? ''),
                $this->cleanCell($asset->sede?->sede ?? ''),
                $this->cleanCell($asset->ubicacion?->ubicacion ?? ''),
                $this->cleanCell($asset->currentUser ? trim(($asset->currentUser->name ?? '').' '.($asset->currentUser->ap_paterno ?? '')) : 'Libre'),
                $this->cleanCell($specs['owner'] ?? ''),
                $this->cleanCell($specs['medio'] ?? ''),
                $asset->cost !== null ? (float) $asset->cost : '',
                $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '',
                $asset->warranty_expiry ? $asset->warranty_expiry->format('Y-m-d') : '',
                $asset->created_at ? $asset->created_at->format('Y-m-d H:i') : '',
            ];
        })->values()->all();
    }

    private function buildByCategoryRows(): array
    {
        $rows = [];
        $grouped = $this->assets->groupBy(fn ($a) => $a->category->name ?? 'Sin categoría');
        foreach ($grouped as $name => $items) {
            $count = $items->count();
            $cost = (float) $items->sum(fn ($a) => (float) ($a->cost ?? 0));
            $assigned = $items->whereNotNull('current_user_id')->count();
            $rows[] = [$this->cleanCell($name), $count, number_format($cost, 2), $assigned, $count - $assigned];
        }

        usort($rows, fn ($a, $b) => $b[1] <=> $a[1]);
        return $rows;
    }

    private function buildByStatusRows(): array
    {
        $rows = [];
        $total = max(1, $this->assets->count());
        $grouped = $this->assets->groupBy(fn ($a) => $a->status->name ?? 'Sin estatus');
        foreach ($grouped as $name => $items) {
            $count = $items->count();
            $cost = (float) $items->sum(fn ($a) => (float) ($a->cost ?? 0));
            $rows[] = [$this->cleanCell($name), $count, number_format($cost, 2), round(($count / $total) * 100, 2).'%'];
        }

        usort($rows, fn ($a, $b) => $b[1] <=> $a[1]);
        return $rows;
    }

    private function cleanCell($value): string
    {
        $text = (string) $value;
        // Evita filas altas en Excel por saltos de línea/tabulaciones embebidas.
        $text = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $text);
        $text = preg_replace('/\s{2,}/', ' ', $text) ?? $text;

        return trim($text);
    }
}
