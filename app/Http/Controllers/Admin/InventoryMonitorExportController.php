<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\MonitorWorkbookExport;
use App\Models\InvAsset;
use App\Models\InvMaintenance;
use App\Models\Company;
use App\Models\Sede;
use App\Support\InventoryV2FilterPermissions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class InventoryMonitorExportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:read inventory monitor']);
    }

    private function alertTypeLabel($type)
    {
        $map = [
            'warranty_critical' => 'Garantias por vencer (<= 15 dias)',
            'assigned_without_user' => 'Asignados sin usuario',
            'without_company' => 'Activos sin empresa',
            'repeated_transfers' => 'Traslados repetidos en 24h',
            'old_open_maint' => 'Mantenimientos abiertos > 30 dias',
        ];

        return isset($map[$type]) ? $map[$type] : 'Sin seleccionar';
    }

    private function buildSummaryRows(Request $request, Carbon $from, Carbon $to, $totalAssets, $type = '')
    {
        $user = Auth::user();
        $companyFilter = InventoryV2FilterPermissions::effectiveScalar($user, 'monitor_company', $request->input('company_filter', ''));
        $sedeFilter = InventoryV2FilterPermissions::effectiveScalar($user, 'monitor_sede', $request->input('sede_filter', ''));
        $eventTypeEff = InventoryV2FilterPermissions::effectiveScalar($user, 'monitor_event_type', $request->input('event_type', ''));
        $searchEff = InventoryV2FilterPermissions::effectiveScalar($user, 'monitor_search', $request->input('search', ''));

        $companyName = 'Todas';
        if ($companyFilter !== '' && $companyFilter !== null) {
            $companyName = optional(Company::find($companyFilter))->name ?: 'N/D';
        }

        $sedeName = 'Todas';
        if ($sedeFilter !== '' && $sedeFilter !== null) {
            $sede = Sede::find($sedeFilter);
            $sedeName = ($sede && ($sede->sede ?: $sede->name)) ? ($sede->sede ?: $sede->name) : 'N/D';
        }

        $rangeLabel = InventoryV2FilterPermissions::userMayUse($user, 'monitor_range')
            ? $request->input('range', '7d')
            : '7d';

        $rows = [
            ['Generado el', now()->format('d/m/Y H:i:s')],
            ['Rango', $rangeLabel],
            ['Desde', $from->format('d/m/Y H:i:s')],
            ['Hasta', $to->format('d/m/Y H:i:s')],
            ['Empresa', $companyName],
            ['Sede', $sedeName],
            ['Tipo de evento', $eventTypeEff ?: 'Todos'],
            ['Busqueda', $searchEff ?: 'N/A'],
            ['Total activos filtrados', (string) $totalAssets],
        ];

        if ($type !== '') {
            $rows[] = ['Tipo de alerta', $this->alertTypeLabel($type)];
        }

        return $rows;
    }

    private function resolveDates(Request $request)
    {
        $user = Auth::user();
        $range = InventoryV2FilterPermissions::userMayUse($user, 'monitor_range')
            ? $request->input('range', '7d')
            : '7d';
        $canDates = InventoryV2FilterPermissions::userMayUse($user, 'monitor_dates');
        $from = $canDates ? $request->input('from_date') : null;
        $to = $canDates ? $request->input('to_date') : null;

        if (! $from || ! $to) {
            $now = Carbon::now();
            if ($range === 'today') {
                $from = $now->copy()->startOfDay()->format('Y-m-d');
                $to = $now->copy()->format('Y-m-d');
            } elseif ($range === '30d') {
                $from = $now->copy()->subDays(30)->format('Y-m-d');
                $to = $now->copy()->format('Y-m-d');
            } else {
                $from = $now->copy()->subDays(7)->format('Y-m-d');
                $to = $now->copy()->format('Y-m-d');
            }
        }

        return [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()];
    }

    private function filteredAssetIds(Request $request)
    {
        $user = Auth::user();
        $companyId = InventoryV2FilterPermissions::effectiveScalar($user, 'monitor_company', $request->input('company_filter'));
        $sedeId = InventoryV2FilterPermissions::effectiveScalar($user, 'monitor_sede', $request->input('sede_filter'));
        $search = InventoryV2FilterPermissions::effectiveScalar($user, 'monitor_search', $request->input('search'));

        return InvAsset::query()
            ->when($companyId, function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->when($sedeId, function ($q) use ($sedeId) {
                $q->where('sede_id', $sedeId);
            })
            ->when($search, function ($q) use ($search) {
                $term = '%' . $search . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('internal_tag', 'like', $term)
                        ->orWhere('serial', 'like', $term);
                });
            })
            ->pluck('id')
            ->all();
    }

    public function exportChanges(Request $request)
    {
        $assetIds = $this->filteredAssetIds($request);
        if (empty($assetIds)) {
            $assetIds = [0];
        }
        list($from, $to) = $this->resolveDates($request);

        $rowsDb = DB::table('activity_log as al')
            ->leftJoin('users as u', 'u.id', '=', 'al.causer_id')
            ->leftJoin('inv_assets as ia', 'ia.id', '=', 'al.subject_id')
            ->where('al.subject_type', InvAsset::class)
            ->whereIn('al.subject_id', $assetIds)
            ->whereBetween('al.created_at', [$from, $to])
            ->orderBy('al.created_at', 'desc')
            ->get([
                'al.subject_id',
                'al.created_at',
                'al.description',
                'al.properties',
                'u.name as causer_name',
                'ia.internal_tag as asset_tag',
                'ia.name as asset_name',
            ]);

        $rows = collect($rowsDb)->map(function ($row) {
            $properties = json_decode($row->properties, true) ?: [];
            $old = isset($properties['old']) && is_array($properties['old']) ? $properties['old'] : [];
            $new = isset($properties['attributes']) && is_array($properties['attributes']) ? $properties['attributes'] : [];
            $fields = array_values(array_unique(array_merge(array_keys($old), array_keys($new))));
            return [
                Carbon::parse($row->created_at)->format('d/m/Y H:i:s'),
                $row->subject_id,
                $row->asset_tag ?: 'N/D',
                $row->asset_name ?: 'N/D',
                $row->causer_name ?: 'Sistema',
                strtoupper($row->description ?: 'updated'),
                implode(', ', array_slice($fields, 0, 10)),
            ];
        })->all();

        $summaryRows = $this->buildSummaryRows($request, $from, $to, count($assetIds));

        return Excel::download(
            new MonitorWorkbookExport(
                $summaryRows,
                $rows,
                ['Fecha', 'Activo ID', 'Etiqueta', 'Activo', 'Usuario', 'Accion', 'Campos'],
                'Ultimos cambios'
            ),
            'monitor_cambios_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function exportAlertDetail(Request $request)
    {
        $assetIds = $this->filteredAssetIds($request);
        if (empty($assetIds)) {
            $assetIds = [0];
        }
        list($from, $to) = $this->resolveDates($request);
        $type = $request->input('selected_alert_type', '');
        $today = Carbon::today();

        $base = InvAsset::query()
            ->with(['status', 'company', 'sede'])
            ->whereIn('id', $assetIds);

        if ($type === 'warranty_critical') {
            $result = $base->whereNotNull('warranty_expiry')
                ->whereBetween('warranty_expiry', [$today, $today->copy()->addDays(15)])
                ->orderBy('warranty_expiry')
                ->get();
        } elseif ($type === 'assigned_without_user') {
            $assignedStatusIds = DB::table('inv_statuses')->where('name', 'like', '%ASIGN%')->pluck('id')->all();
            $result = empty($assignedStatusIds)
                ? collect()
                : $base->whereIn('status_id', $assignedStatusIds)->whereNull('current_user_id')->get();
        } elseif ($type === 'without_company') {
            $result = $base->whereNull('company_id')->get();
        } elseif ($type === 'repeated_transfers') {
            $assetIdsRepeated = DB::table('inv_movements as mv')
                ->select('mv.asset_id')
                ->whereIn('mv.asset_id', $assetIds)
                ->where('mv.type', 'TRASLADO')
                ->where('mv.date', '>=', Carbon::now()->subDay())
                ->groupBy('mv.asset_id')
                ->havingRaw('COUNT(*) >= 2')
                ->pluck('mv.asset_id')
                ->all();
            $result = empty($assetIdsRepeated) ? collect() : $base->whereIn('id', $assetIdsRepeated)->get();
        } elseif ($type === 'old_open_maint') {
            $assetIdsMaint = InvMaintenance::query()
                ->whereIn('asset_id', $assetIds)
                ->whereNull('end_date')
                ->whereDate('start_date', '<=', $today->copy()->subDays(30))
                ->pluck('asset_id')
                ->all();
            $result = empty($assetIdsMaint) ? collect() : $base->whereIn('id', $assetIdsMaint)->get();
        } else {
            $result = collect();
        }

        $rows = $result->map(function ($a) use ($type) {
            $extra = '';
            if ($type === 'warranty_critical') {
                $extra = $a->warranty_expiry ? Carbon::parse($a->warranty_expiry)->format('d/m/Y') : 'N/D';
            } elseif ($type === 'assigned_without_user') {
                $extra = 'Sin usuario actual';
            } elseif ($type === 'without_company') {
                $extra = 'Sin empresa';
            } elseif ($type === 'repeated_transfers') {
                $extra = '2+ traslados en 24h';
            } elseif ($type === 'old_open_maint') {
                $extra = 'Mantenimiento abierto >30 días';
            }

            return [
                $a->id,
                $a->internal_tag ?: 'N/D',
                $a->name,
                optional($a->status)->name ?: 'N/D',
                optional($a->company)->name ?: 'N/D',
                (optional($a->sede)->sede ?: optional($a->sede)->name) ?: 'N/D',
                $extra,
            ];
        })->all();

        $summaryRows = $this->buildSummaryRows($request, $from, $to, count($assetIds), $type);

        return Excel::download(
            new MonitorWorkbookExport(
                $summaryRows,
                $rows,
                ['Activo ID', 'Etiqueta', 'Activo', 'Estatus', 'Empresa', 'Sede', 'Detalle'],
                'Detalle alerta'
            ),
            'monitor_alerta_detalle_' . now()->format('Ymd_His') . '.xlsx'
        );
    }
}

