<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\InvAssignmentHistoryQuery;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryAssignmentHistoryExportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:read inventory|read inventory assignment history']);
    }

    public function __invoke(Request $request): StreamedResponse
    {
        $filters = $request->only([
            'search', 'type_scope', 'user_id', 'admin_id', 'date_from', 'date_to', 'batch_uuid',
        ]);

        $filename = 'historial_asignaciones_v2_'.date('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($filters) {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($out, [
                'id',
                'fecha',
                'tipo',
                'asset_id',
                'activo_etiqueta',
                'activo_nombre',
                'nuevo_responsable_id',
                'anterior_responsable_id',
                'admin_id',
                'motivo',
                'notas',
                'lote_uuid',
            ], ';');

            $query = InvAssignmentHistoryQuery::base();
            InvAssignmentHistoryQuery::applyFilters($query, $filters);
            $query->orderBy('date')->orderBy('id');

            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $m) {
                    fputcsv($out, [
                        $m->id,
                        $m->date?->format('Y-m-d H:i:s'),
                        $m->type,
                        $m->asset_id,
                        $m->asset?->internal_tag,
                        $m->asset?->name,
                        $m->user_id,
                        $m->previous_user_id,
                        $m->admin_id,
                        $m->reason,
                        $m->notes,
                        $m->batch_uuid,
                    ], ';');
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
