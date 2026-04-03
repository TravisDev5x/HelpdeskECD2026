<?php

namespace App\Livewire\Inventory;

use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\InvAsset;
use App\Models\InvMovement;
use App\Models\InvMaintenance;
use App\Models\Company;
use App\Models\Sede;
use App\Livewire\Inventory\Concerns\GuardsInventoryV2Filters;
use App\Support\InventoryV2FilterPermissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class MonitorIndex extends Component
{
    use GuardsInventoryV2Filters;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Url(except: '7d')]
    public $range = '7d';
    #[Url(except: '')]
    public $from_date;
    #[Url(except: '')]
    public $to_date;
    #[Url(except: '')]
    public $sede_filter = '';
    #[Url(except: '')]
    public $company_filter = '';
    #[Url(except: '')]
    public $search = '';
    #[Url(except: '')]
    public $event_type = '';

    public $companies = [];
    public $sedes = [];

    public $kpis = [];
    public $timeline = [];
    public $statusDistribution = [];
    public $categoryDistribution = [];
    public $conditionDistribution = [];
    public $maintenanceSummary = [];
    public $maintenanceMonthlyCost = [];
    public $alerts = [];
    #[Url(except: '')]
    public $selectedAlertType = '';
    public $alertDetailRows = [];
    #[Url(except: 10)]
    public $changesPerPage = 10;

    public function mount()
    {
        abort_unless(Auth::check() && Auth::user()->can('read inventory monitor'), 403);

        $this->companies = Company::select('id', 'name')->orderBy('name')->get();
        $this->sedes = Sede::orderBy('sede')->get();

        if (! $this->userCanInventoryFilter('monitor_range')) {
            $this->range = '7d';
        }

        $this->stripUnauthorizedInventoryFilters([
            'monitor_sede' => 'sede_filter',
            'monitor_company' => 'company_filter',
            'monitor_search' => 'search',
            'monitor_event_type' => 'event_type',
        ]);

        $canDates = $this->userCanInventoryFilter('monitor_dates');
        if (! $canDates || empty($this->from_date) || empty($this->to_date)) {
            $this->applyRange($this->range);
        }
    }

    public function updatedRange($value)
    {
        if (! $this->userCanInventoryFilter('monitor_range')) {
            $this->range = '7d';
            $value = '7d';
        }
        $this->resetPage('changesPage');
        $this->applyRange($value);
    }

    public function useToday()
    {
        if (! $this->userCanInventoryFilter('monitor_range')) {
            return;
        }
        $this->range = 'today';
        $this->resetPage('changesPage');
        $this->applyRange('today');
    }

    public function use7d()
    {
        if (! $this->userCanInventoryFilter('monitor_range')) {
            return;
        }
        $this->range = '7d';
        $this->resetPage('changesPage');
        $this->applyRange('7d');
    }

    public function use30d()
    {
        if (! $this->userCanInventoryFilter('monitor_range')) {
            return;
        }
        $this->range = '30d';
        $this->resetPage('changesPage');
        $this->applyRange('30d');
    }

    public function resetMonitorFilters()
    {
        $this->range = '7d';
        $this->sede_filter = '';
        $this->company_filter = '';
        $this->search = '';
        $this->event_type = '';
        $this->selectedAlertType = '';
        $this->changesPerPage = 10;
        $this->resetPage('changesPage');
        $this->applyRange('7d');
    }

    public function updatingFromDate()
    {
        if ($this->userCanInventoryFilter('monitor_dates')) {
            $this->resetPage('changesPage');
        }
    }

    public function updatedFromDate()
    {
        if (! $this->userCanInventoryFilter('monitor_dates')) {
            $this->applyRange($this->range);
        }
    }

    public function updatingToDate()
    {
        if ($this->userCanInventoryFilter('monitor_dates')) {
            $this->resetPage('changesPage');
        }
    }

    public function updatedToDate()
    {
        if (! $this->userCanInventoryFilter('monitor_dates')) {
            $this->applyRange($this->range);
        }
    }

    public function updatingSedeFilter()
    {
        if (! $this->userCanInventoryFilter('monitor_sede')) {
            $this->sede_filter = '';

            return;
        }
        $this->resetPage('changesPage');
    }

    public function updatingCompanyFilter()
    {
        if (! $this->userCanInventoryFilter('monitor_company')) {
            $this->company_filter = '';

            return;
        }
        $this->resetPage('changesPage');
    }

    public function updatingSearch()
    {
        if (! $this->userCanInventoryFilter('monitor_search')) {
            $this->search = '';

            return;
        }
        $this->resetPage('changesPage');
    }

    public function updatingEventType()
    {
        if (! $this->userCanInventoryFilter('monitor_event_type')) {
            $this->event_type = '';

            return;
        }
        $this->resetPage('changesPage');
    }

    public function updatingChangesPerPage()
    {
        $this->resetPage('changesPage');
    }

    public function applyRange($value)
    {
        $now = Carbon::now();
        if ($value === 'today') {
            $this->from_date = $now->copy()->startOfDay()->format('Y-m-d');
            $this->to_date = $now->copy()->format('Y-m-d');
        } elseif ($value === '30d') {
            $this->from_date = $now->copy()->subDays(30)->format('Y-m-d');
            $this->to_date = $now->copy()->format('Y-m-d');
        } else {
            $this->from_date = $now->copy()->subDays(7)->format('Y-m-d');
            $this->to_date = $now->copy()->format('Y-m-d');
        }
    }

    private function getDates()
    {
        $from = Carbon::parse($this->from_date)->startOfDay();
        $to = Carbon::parse($this->to_date)->endOfDay();
        return [$from, $to];
    }

    private function filteredAssetsBaseQuery()
    {
        $user = Auth::user();
        $companyId = InventoryV2FilterPermissions::effectiveScalar($user, 'monitor_company', $this->company_filter);
        $sedeId = InventoryV2FilterPermissions::effectiveScalar($user, 'monitor_sede', $this->sede_filter);
        $search = InventoryV2FilterPermissions::effectiveScalar($user, 'monitor_search', $this->search);

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
            });
    }

    private function buildKpis($assetIds, $from, $to)
    {
        $assetsQuery = InvAsset::query()->whereIn('id', $assetIds);

        $totalAssets = (clone $assetsQuery)->count();
        $assignedAssets = (clone $assetsQuery)->whereNotNull('current_user_id')->count();
        $freeAssets = (clone $assetsQuery)->whereNull('current_user_id')->count();
        $bajaAssets = (clone $assetsQuery)->whereHas('status', function ($q) {
            $q->where('name', 'like', '%BAJA%')->orWhere('name', 'like', '%SUSPEND%');
        })->count();

        $movementsCount = InvMovement::query()
            ->whereIn('asset_id', $assetIds)
            ->whereBetween('date', [$from, $to])
            ->count();

        $trasladosCount = InvMovement::query()
            ->whereIn('asset_id', $assetIds)
            ->where('type', 'TRASLADO')
            ->whereBetween('date', [$from, $to])
            ->count();

        $maintenancesCount = InvMaintenance::query()
            ->whereIn('asset_id', $assetIds)
            ->whereBetween('start_date', [$from, $to])
            ->count();

        $maintOpen = InvMaintenance::query()
            ->whereIn('asset_id', $assetIds)
            ->whereNull('end_date')
            ->count();

        $warrantyDue30 = (clone $assetsQuery)
            ->whereNotNull('warranty_expiry')
            ->whereBetween('warranty_expiry', [Carbon::today(), Carbon::today()->copy()->addDays(30)])
            ->count();

        $changesCount = 0;
        if (Schema::hasTable('activity_log')) {
            $changesCount = DB::table('activity_log')
                ->where('subject_type', InvAsset::class)
                ->whereIn('subject_id', $assetIds)
                ->whereBetween('created_at', [$from, $to])
                ->count();
        }

        return [
            'total_assets' => $totalAssets,
            'assigned_assets' => $assignedAssets,
            'free_assets' => $freeAssets,
            'baja_assets' => $bajaAssets,
            'events_count' => $movementsCount + $maintenancesCount + $changesCount,
            'traslados_count' => $trasladosCount,
            'maint_open' => $maintOpen,
            'warranty_due_30' => $warrantyDue30,
        ];
    }

    private function buildTimeline($assetIds, $from, $to)
    {
        $events = collect();
        $et = InventoryV2FilterPermissions::userMayUse(Auth::user(), 'monitor_event_type') ? $this->event_type : '';

        if ($et === '' || $et === 'movement') {
            $movements = InvMovement::query()
                ->with(['asset', 'user', 'admin'])
                ->whereIn('asset_id', $assetIds)
                ->whereBetween('date', [$from, $to])
                ->orderBy('date', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($m) {
                    return [
                        'date' => $m->date,
                        'type' => 'MOVIMIENTO',
                        'title' => $m->type,
                        'asset' => optional($m->asset)->internal_tag ?: ('#' . $m->asset_id),
                        'actor' => optional($m->admin)->name ?: optional($m->user)->name ?: 'Sistema',
                        'notes' => $m->notes,
                        'color' => $m->type === 'BAJA' ? 'danger' : ($m->type === 'TRASLADO' ? 'info' : 'primary'),
                    ];
                });
            $events = $events->merge($movements);
        }

        if ($et === '' || $et === 'maintenance') {
            $maintenances = InvMaintenance::query()
                ->with(['asset', 'logger'])
                ->whereIn('asset_id', $assetIds)
                ->whereBetween('start_date', [$from, $to])
                ->orderBy('start_date', 'desc')
                ->limit(15)
                ->get()
                ->map(function ($m) {
                    return [
                        'date' => Carbon::parse($m->start_date),
                        'type' => 'MANTENIMIENTO',
                        'title' => $m->title,
                        'asset' => optional($m->asset)->internal_tag ?: ('#' . $m->asset_id),
                        'actor' => optional($m->logger)->name ?: 'Sistema',
                        'notes' => $m->diagnosis,
                        'color' => 'warning',
                    ];
                });
            $events = $events->merge($maintenances);
        }

        if (($et === '' || $et === 'change') && Schema::hasTable('activity_log')) {
            $changes = DB::table('activity_log as al')
                ->leftJoin('users as u', 'u.id', '=', 'al.causer_id')
                ->where('al.subject_type', InvAsset::class)
                ->whereIn('al.subject_id', $assetIds)
                ->whereBetween('al.created_at', [$from, $to])
                ->orderBy('al.created_at', 'desc')
                ->limit(20)
                ->get(['al.subject_id', 'al.created_at', 'al.description', 'u.name as causer_name'])
                ->map(function ($c) {
                    return [
                        'date' => Carbon::parse($c->created_at),
                        'type' => 'CAMBIO',
                        'title' => strtoupper($c->description ?: 'actualización'),
                        'asset' => '#' . $c->subject_id,
                        'actor' => $c->causer_name ?: 'Sistema',
                        'notes' => null,
                        'color' => 'secondary',
                    ];
                });
            $events = $events->merge($changes);
        }

        return $events->sortByDesc('date')->take(30)->values()->all();
    }

    private function buildChangeRows($assetIds, $from, $to)
    {
        if (!Schema::hasTable('activity_log')) {
            return null;
        }

        $rows = DB::table('activity_log as al')
            ->leftJoin('users as u', 'u.id', '=', 'al.causer_id')
            ->where('al.subject_type', InvAsset::class)
            ->whereIn('al.subject_id', $assetIds)
            ->whereBetween('al.created_at', [$from, $to])
            ->orderBy('al.created_at', 'desc')
            ->paginate((int) $this->changesPerPage, ['al.subject_id', 'al.created_at', 'al.description', 'al.properties', 'u.name as causer_name'], 'changesPage');

        $rows->setCollection(
            $rows->getCollection()->map(function ($row) {
                $properties = json_decode($row->properties, true) ?: [];
                $old = isset($properties['old']) && is_array($properties['old']) ? $properties['old'] : [];
                $new = isset($properties['attributes']) && is_array($properties['attributes']) ? $properties['attributes'] : [];
                $fields = array_values(array_unique(array_merge(array_keys($old), array_keys($new))));
                return [
                    'date' => Carbon::parse($row->created_at),
                    'asset_id' => $row->subject_id,
                    'actor' => $row->causer_name ?: 'Sistema',
                    'action' => $row->description ?: 'updated',
                    'fields' => array_slice($fields, 0, 5),
                ];
            })
        );

        return $rows;
    }

    /**
     * Fase 2: distribuciones útiles del inventario.
     */
    private function buildDistributions($assetIds)
    {
        $statusRows = DB::table('inv_assets as ia')
            ->leftJoin('inv_statuses as st', 'st.id', '=', 'ia.status_id')
            ->whereIn('ia.id', $assetIds)
            ->whereNull('ia.deleted_at')
            ->groupBy('ia.status_id', 'st.name')
            ->orderByRaw('COUNT(*) DESC')
            ->get([
                DB::raw("COALESCE(st.name, 'SIN ESTATUS') as label"),
                DB::raw('COUNT(*) as total')
            ]);

        $categoryRows = DB::table('inv_assets as ia')
            ->leftJoin('inv_categories as ct', 'ct.id', '=', 'ia.category_id')
            ->whereIn('ia.id', $assetIds)
            ->whereNull('ia.deleted_at')
            ->groupBy('ia.category_id', 'ct.name')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(10)
            ->get([
                DB::raw("COALESCE(ct.name, 'SIN CATEGORÍA') as label"),
                DB::raw('COUNT(*) as total')
            ]);

        $conditionRows = DB::table('inv_assets as ia')
            ->whereIn('ia.id', $assetIds)
            ->whereNull('ia.deleted_at')
            ->groupBy('ia.condition')
            ->orderByRaw('COUNT(*) DESC')
            ->get([
                DB::raw("COALESCE(ia.condition, 'NO DEFINIDA') as label"),
                DB::raw('COUNT(*) as total')
            ]);

        return [
            'status' => $statusRows->map(function ($r) { return ['label' => $r->label, 'total' => (int) $r->total]; })->all(),
            'category' => $categoryRows->map(function ($r) { return ['label' => $r->label, 'total' => (int) $r->total]; })->all(),
            'condition' => $conditionRows->map(function ($r) { return ['label' => $r->label, 'total' => (int) $r->total]; })->all(),
        ];
    }

    /**
     * Fase 2: salud de mantenimientos y costo mensual.
     */
    private function buildMaintenanceStats($assetIds, $from, $to)
    {
        $open = InvMaintenance::query()->whereIn('asset_id', $assetIds)->whereNull('end_date')->count();
        $closed = InvMaintenance::query()->whereIn('asset_id', $assetIds)->whereNotNull('end_date')->count();
        $inRange = InvMaintenance::query()->whereIn('asset_id', $assetIds)->whereBetween('start_date', [$from, $to])->count();

        $avgCost = (float) InvMaintenance::query()
            ->whereIn('asset_id', $assetIds)
            ->whereBetween('start_date', [$from, $to])
            ->avg('cost');

        $totalCost = (float) InvMaintenance::query()
            ->whereIn('asset_id', $assetIds)
            ->whereBetween('start_date', [$from, $to])
            ->sum('cost');

        $monthlyCost = InvMaintenance::query()
            ->whereIn('asset_id', $assetIds)
            ->whereBetween('start_date', [$from->copy()->subMonths(5)->startOfMonth(), $to])
            ->groupBy(DB::raw("DATE_FORMAT(start_date, '%Y-%m')"))
            ->orderBy(DB::raw("DATE_FORMAT(start_date, '%Y-%m')"))
            ->get([
                DB::raw("DATE_FORMAT(start_date, '%Y-%m') as period"),
                DB::raw('SUM(cost) as total_cost'),
                DB::raw('COUNT(*) as total_items'),
            ])
            ->map(function ($row) {
                return [
                    'period' => $row->period,
                    'total_cost' => (float) $row->total_cost,
                    'total_items' => (int) $row->total_items,
                ];
            })
            ->all();

        return [
            'summary' => [
                'open' => $open,
                'closed' => $closed,
                'in_range' => $inRange,
                'avg_cost' => $avgCost,
                'total_cost' => $totalCost,
            ],
            'monthly_cost' => $monthlyCost,
        ];
    }

    /**
     * Fase 3: alertas inteligentes operativas y de consistencia.
     */
    private function buildAlerts($assetIds)
    {
        $today = Carbon::today();

        // 1) Garantías críticas en <= 15 días
        $warrantyCritical = InvAsset::query()
            ->whereIn('id', $assetIds)
            ->whereNotNull('warranty_expiry')
            ->whereBetween('warranty_expiry', [$today, $today->copy()->addDays(15)])
            ->count();

        // 2) Inconsistencia: activos asignados sin usuario
        $assignedStatusIds = DB::table('inv_statuses')
            ->where('name', 'like', '%ASIGN%')
            ->pluck('id')
            ->all();
        $assignedWithoutUser = 0;
        if (!empty($assignedStatusIds)) {
            $assignedWithoutUser = InvAsset::query()
                ->whereIn('id', $assetIds)
                ->whereIn('status_id', $assignedStatusIds)
                ->whereNull('current_user_id')
                ->count();
        }

        // 3) Inconsistencia: activos sin empresa asignada
        $withoutCompany = InvAsset::query()
            ->whereIn('id', $assetIds)
            ->whereNull('company_id')
            ->count();

        // 4) Traslados repetidos en 24h por activo (>=2)
        $repeatedTransfers = DB::table('inv_movements as mv')
            ->select('mv.asset_id', DB::raw('COUNT(*) as total'))
            ->whereIn('mv.asset_id', $assetIds)
            ->where('mv.type', 'TRASLADO')
            ->where('mv.date', '>=', Carbon::now()->subDay())
            ->groupBy('mv.asset_id')
            ->havingRaw('COUNT(*) >= 2')
            ->get()
            ->count();

        // 5) Mantenimientos abiertos con más de 30 días
        $oldOpenMaint = InvMaintenance::query()
            ->whereIn('asset_id', $assetIds)
            ->whereNull('end_date')
            ->whereDate('start_date', '<=', $today->copy()->subDays(30))
            ->count();

        $alerts = [];

        if ($warrantyCritical > 0) {
            $alerts[] = [
                'type' => 'warranty_critical',
                'level' => 'warning',
                'title' => 'Garantías por vencer (<= 15 días)',
                'value' => $warrantyCritical,
                'description' => 'Activos con garantía cercana al vencimiento.',
            ];
        }

        if ($assignedWithoutUser > 0) {
            $alerts[] = [
                'type' => 'assigned_without_user',
                'level' => 'danger',
                'title' => 'Asignados sin usuario',
                'value' => $assignedWithoutUser,
                'description' => 'Estatus asignado pero sin usuario actual.',
            ];
        }

        if ($withoutCompany > 0) {
            $alerts[] = [
                'type' => 'without_company',
                'level' => 'warning',
                'title' => 'Activos sin empresa',
                'value' => $withoutCompany,
                'description' => 'Registros incompletos para control administrativo.',
            ];
        }

        if ($repeatedTransfers > 0) {
            $alerts[] = [
                'type' => 'repeated_transfers',
                'level' => 'info',
                'title' => 'Traslados repetidos en 24h',
                'value' => $repeatedTransfers,
                'description' => 'Activos con 2 o más traslados en las últimas 24 horas.',
            ];
        }

        if ($oldOpenMaint > 0) {
            $alerts[] = [
                'type' => 'old_open_maint',
                'level' => 'danger',
                'title' => 'Mantenimientos abiertos > 30 días',
                'value' => $oldOpenMaint,
                'description' => 'Casos potencialmente estancados.',
            ];
        }

        return $alerts;
    }

    /**
     * Detalle por tipo de alerta para drill-down operativo.
     */
    private function buildAlertDetailRows($assetIds, $type)
    {
        if (!$type) {
            return [];
        }

        $today = Carbon::today();
        $base = InvAsset::query()
            ->with(['status', 'company', 'sede', 'currentUser'])
            ->whereIn('id', $assetIds);

        if ($type === 'warranty_critical') {
            return $base
                ->whereNotNull('warranty_expiry')
                ->whereBetween('warranty_expiry', [$today, $today->copy()->addDays(15)])
                ->orderBy('warranty_expiry')
                ->limit(200)
                ->get()
                ->map(function ($a) {
                    return [
                        'asset_id' => $a->id,
                        'tag' => $a->internal_tag,
                        'name' => $a->name,
                        'status' => optional($a->status)->name,
                        'company' => optional($a->company)->name,
                        'sede' => optional($a->sede)->sede ?: optional($a->sede)->name,
                        'extra' => $a->warranty_expiry ? $a->warranty_expiry->format('d/m/Y') : 'N/D',
                    ];
                })->all();
        }

        if ($type === 'assigned_without_user') {
            $assignedStatusIds = DB::table('inv_statuses')->where('name', 'like', '%ASIGN%')->pluck('id')->all();
            if (empty($assignedStatusIds)) {
                return [];
            }
            return $base
                ->whereIn('status_id', $assignedStatusIds)
                ->whereNull('current_user_id')
                ->orderBy('id', 'desc')
                ->limit(200)
                ->get()
                ->map(function ($a) {
                    return [
                        'asset_id' => $a->id,
                        'tag' => $a->internal_tag,
                        'name' => $a->name,
                        'status' => optional($a->status)->name,
                        'company' => optional($a->company)->name,
                        'sede' => optional($a->sede)->sede ?: optional($a->sede)->name,
                        'extra' => 'Sin usuario actual',
                    ];
                })->all();
        }

        if ($type === 'without_company') {
            return $base
                ->whereNull('company_id')
                ->orderBy('id', 'desc')
                ->limit(200)
                ->get()
                ->map(function ($a) {
                    return [
                        'asset_id' => $a->id,
                        'tag' => $a->internal_tag,
                        'name' => $a->name,
                        'status' => optional($a->status)->name,
                        'company' => 'N/D',
                        'sede' => optional($a->sede)->sede ?: optional($a->sede)->name,
                        'extra' => 'Sin empresa',
                    ];
                })->all();
        }

        if ($type === 'repeated_transfers') {
            $assetIdsRepeated = DB::table('inv_movements as mv')
                ->select('mv.asset_id')
                ->whereIn('mv.asset_id', $assetIds)
                ->where('mv.type', 'TRASLADO')
                ->where('mv.date', '>=', Carbon::now()->subDay())
                ->groupBy('mv.asset_id')
                ->havingRaw('COUNT(*) >= 2')
                ->pluck('mv.asset_id')
                ->all();

            if (empty($assetIdsRepeated)) {
                return [];
            }

            return $base
                ->whereIn('id', $assetIdsRepeated)
                ->orderBy('id', 'desc')
                ->limit(200)
                ->get()
                ->map(function ($a) {
                    return [
                        'asset_id' => $a->id,
                        'tag' => $a->internal_tag,
                        'name' => $a->name,
                        'status' => optional($a->status)->name,
                        'company' => optional($a->company)->name,
                        'sede' => optional($a->sede)->sede ?: optional($a->sede)->name,
                        'extra' => '2+ traslados en 24h',
                    ];
                })->all();
        }

        if ($type === 'old_open_maint') {
            $assetIdsMaint = InvMaintenance::query()
                ->whereIn('asset_id', $assetIds)
                ->whereNull('end_date')
                ->whereDate('start_date', '<=', $today->copy()->subDays(30))
                ->pluck('asset_id')
                ->all();
            if (empty($assetIdsMaint)) {
                return [];
            }
            return $base
                ->whereIn('id', $assetIdsMaint)
                ->orderBy('id', 'desc')
                ->limit(200)
                ->get()
                ->map(function ($a) {
                    return [
                        'asset_id' => $a->id,
                        'tag' => $a->internal_tag,
                        'name' => $a->name,
                        'status' => optional($a->status)->name,
                        'company' => optional($a->company)->name,
                        'sede' => optional($a->sede)->sede ?: optional($a->sede)->name,
                        'extra' => 'Mantenimiento abierto >30 días',
                    ];
                })->all();
        }

        return [];
    }

    public function showAlertDetail($type)
    {
        abort_unless(Auth::user()->can('read inventory monitor'), 403);
        $this->selectedAlertType = $type;
    }

    public function clearAlertDetail()
    {
        abort_unless(Auth::user()->can('read inventory monitor'), 403);
        $this->selectedAlertType = '';
        $this->alertDetailRows = [];
    }

    public function render()
    {
        list($from, $to) = $this->getDates();

        $assetIds = $this->filteredAssetsBaseQuery()->pluck('id')->all();
        if (empty($assetIds)) {
            $this->kpis = [
                'total_assets' => 0,
                'assigned_assets' => 0,
                'free_assets' => 0,
                'baja_assets' => 0,
                'events_count' => 0,
                'traslados_count' => 0,
                'maint_open' => 0,
                'warranty_due_30' => 0,
            ];
            $this->timeline = [];
            $this->statusDistribution = [];
            $this->categoryDistribution = [];
            $this->conditionDistribution = [];
            $this->maintenanceSummary = [];
            $this->maintenanceMonthlyCost = [];
            $this->alerts = [];
            $this->alertDetailRows = [];
            $changeRows = null;
        } else {
            $this->kpis = $this->buildKpis($assetIds, $from, $to);
            $this->timeline = $this->buildTimeline($assetIds, $from, $to);
            $changeRows = $this->buildChangeRows($assetIds, $from, $to);
            $dist = $this->buildDistributions($assetIds);
            $this->statusDistribution = $dist['status'];
            $this->categoryDistribution = $dist['category'];
            $this->conditionDistribution = $dist['condition'];
            $maint = $this->buildMaintenanceStats($assetIds, $from, $to);
            $this->maintenanceSummary = $maint['summary'];
            $this->maintenanceMonthlyCost = $maint['monthly_cost'];
            $this->alerts = $this->buildAlerts($assetIds);
            $this->alertDetailRows = $this->buildAlertDetailRows($assetIds, $this->selectedAlertType);
        }

        $user = Auth::user();
        $activeFiltersCount = 0;
        if (InventoryV2FilterPermissions::userMayUse($user, 'monitor_company') && ! empty($this->company_filter)) {
            $activeFiltersCount++;
        }
        if (InventoryV2FilterPermissions::userMayUse($user, 'monitor_sede') && ! empty($this->sede_filter)) {
            $activeFiltersCount++;
        }
        if (InventoryV2FilterPermissions::userMayUse($user, 'monitor_search') && ! empty($this->search)) {
            $activeFiltersCount++;
        }
        if (InventoryV2FilterPermissions::userMayUse($user, 'monitor_event_type') && ! empty($this->event_type)) {
            $activeFiltersCount++;
        }
        if (InventoryV2FilterPermissions::userMayUse($user, 'monitor_range') && $this->range !== '7d') {
            $activeFiltersCount++;
        }

        $selectedCompanyName = '';
        if (!empty($this->company_filter)) {
            $selectedCompany = collect($this->companies)->firstWhere('id', (int) $this->company_filter);
            $selectedCompanyName = $selectedCompany ? $selectedCompany->name : '';
        }

        $selectedSedeName = '';
        if (!empty($this->sede_filter)) {
            $selectedSede = collect($this->sedes)->firstWhere('id', (int) $this->sede_filter);
            $selectedSedeName = $selectedSede ? ($selectedSede->sede ?: $selectedSede->name) : '';
        }

        return view('livewire.inventory.monitor-index', [
                'changeRows' => $changeRows,
                'activeFiltersCount' => $activeFiltersCount,
                'selectedCompanyName' => $selectedCompanyName,
                'selectedSedeName' => $selectedSedeName,
            ])
            ->extends('admin.layout', ['title' => ' | Monitoreo Inventario V2'])
            ->section('content');
    }
}

