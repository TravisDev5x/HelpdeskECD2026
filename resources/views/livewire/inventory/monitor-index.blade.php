<div>
    {{-- Breadcrumb del módulo de monitoreo --}}
    @include('partials.breadcrumb', ['items' => [
        ['text' => 'Inicio', 'url' => route('home')],
        ['text' => 'Inventario V2', 'url' => auth()->user()->can('read inventory') ? route('inventory.v2.index') : null],
        ['text' => 'Monitoreo', 'url' => null],
    ]])

    {{-- Tarjeta principal del tablero de monitoreo --}}
    <div class="card card-primary card-outline shadow-sm">
        <div class="card-header py-2">
            <h3 class="card-title">
                <i class="fas fa-chart-line text-primary mr-1"></i> Monitor de Inventario V2
            </h3>
            <div class="card-tools">
                {{-- Exportables de monitoreo --}}
                <a href="{{ route('inventory.monitor.export.changes', ['range' => $range, 'from_date' => $from_date, 'to_date' => $to_date, 'company_filter' => $company_filter, 'sede_filter' => $sede_filter, 'search' => $search]) }}"
                   class="btn btn-outline-secondary btn-sm mr-1" title="Exportar últimos cambios a Excel">
                    <i class="fas fa-file-excel mr-1"></i> Exportar cambios
                </a>
                <a href="{{ route('inventory.monitor.export.alerts', ['range' => $range, 'from_date' => $from_date, 'to_date' => $to_date, 'company_filter' => $company_filter, 'sede_filter' => $sede_filter, 'search' => $search, 'selected_alert_type' => $selectedAlertType]) }}"
                   class="btn btn-outline-secondary btn-sm {{ empty($selectedAlertType) ? 'disabled' : '' }}"
                   title="Exportar detalle de alerta a Excel">
                    <i class="fas fa-file-export mr-1"></i> Exportar detalle alerta
                </a>
            </div>
        </div>

        {{-- Filtros globales de monitoreo --}}
        <div class="card-body border-bottom pb-2">
            {{-- Estado de filtros para navegación clara --}}
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                <div>
                    <span class="badge badge-light border mr-1">Filtros activos: {{ $activeFiltersCount ?? 0 }}</span>
                    <span class="badge badge-secondary mr-1">Rango: {{ strtoupper($range) }}</span>
                    @if(!empty($selectedCompanyName))
                        <span class="badge badge-info mr-1">Empresa: {{ $selectedCompanyName }}</span>
                    @endif
                    @if(!empty($selectedSedeName))
                        <span class="badge badge-info mr-1">Sede: {{ $selectedSedeName }}</span>
                    @endif
                    @if(!empty($event_type))
                        <span class="badge badge-primary mr-1">Evento: {{ ucfirst($event_type) }}</span>
                    @endif
                </div>
                <button type="button" wire:click="resetMonitorFilters" class="btn btn-outline-dark btn-sm">
                    <i class="fas fa-undo-alt mr-1"></i> Limpiar filtros
                </button>
            </div>

            <div wire:loading.flex class="alert alert-light border py-2 mb-2 align-items-center">
                <i class="fas fa-spinner fa-spin mr-2"></i> Actualizando métricas del monitoreo...
            </div>

            <div class="row">
                @can('use inventory filter monitor range')
                <div class="col-md-2 col-sm-6 mb-2">
                    <label class="text-xs font-weight-bold">Rango</label>
                    <select wire:model.live="range" class="form-control form-control-sm">
                        <option value="today">Hoy</option>
                        <option value="7d">Últimos 7 días</option>
                        <option value="30d">Últimos 30 días</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-12 mb-2">
                    <label class="text-xs font-weight-bold d-block">Atajos</label>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary {{ $range === 'today' ? 'active' : '' }}" wire:click="useToday">Hoy</button>
                        <button type="button" class="btn btn-outline-primary {{ $range === '7d' ? 'active' : '' }}" wire:click="use7d">7d</button>
                        <button type="button" class="btn btn-outline-primary {{ $range === '30d' ? 'active' : '' }}" wire:click="use30d">30d</button>
                    </div>
                </div>
                @endcan
                @can('use inventory filter monitor dates')
                <div class="col-md-2 col-sm-6 mb-2">
                    <label class="text-xs font-weight-bold">Desde</label>
                    <input type="date" wire:model.live="from_date" class="form-control form-control-sm">
                </div>
                <div class="col-md-2 col-sm-6 mb-2">
                    <label class="text-xs font-weight-bold">Hasta</label>
                    <input type="date" wire:model.live="to_date" class="form-control form-control-sm">
                </div>
                @endcan
                @can('use inventory filter monitor company')
                <div class="col-md-2 col-sm-6 mb-2">
                    <label class="text-xs font-weight-bold">Empresa</label>
                    <select wire:model.live="company_filter" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endcan
                @can('use inventory filter monitor sede')
                <div class="col-md-2 col-sm-6 mb-2">
                    <label class="text-xs font-weight-bold">Sede</label>
                    <select wire:model.live="sede_filter" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        @foreach($sedes as $sede)
                            <option value="{{ $sede->id }}">{{ $sede->sede ?? $sede->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endcan
                @can('use inventory filter monitor event type')
                <div class="col-md-2 col-sm-6 mb-2">
                    <label class="text-xs font-weight-bold">Evento</label>
                    <select wire:model.live="event_type" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        <option value="movement">Movimientos</option>
                        <option value="maintenance">Mantenimientos</option>
                        <option value="change">Cambios</option>
                    </select>
                </div>
                @endcan
            </div>
            @can('use inventory filter monitor search')
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <label class="text-xs font-weight-bold">Buscar activo</label>
                    <input wire:model.live.debounce.300ms="search" type="text" class="form-control form-control-sm"
                           placeholder="Nombre, etiqueta interna o serie">
                </div>
            </div>
            @endcan
        </div>

        {{-- KPIs de fase 1 --}}
        <div class="card-body pb-1">
            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="small-box bg-info shadow-sm">
                        <div class="inner">
                            <h3>{{ $kpis['total_assets'] ?? 0 }}</h3>
                            <p>Activos Totales</p>
                        </div>
                        <div class="icon"><i class="fas fa-boxes"></i></div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="small-box bg-primary shadow-sm">
                        <div class="inner">
                            <h3>{{ $kpis['assigned_assets'] ?? 0 }}</h3>
                            <p>Activos Asignados</p>
                        </div>
                        <div class="icon"><i class="fas fa-user-check"></i></div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="small-box {{ ($kpis['events_count'] ?? 0) > 200 ? 'bg-danger' : (($kpis['events_count'] ?? 0) > 80 ? 'bg-warning' : 'bg-success') }} shadow-sm">
                        <div class="inner">
                            <h3>{{ $kpis['events_count'] ?? 0 }}</h3>
                            <p>Eventos en Rango</p>
                        </div>
                        <div class="icon"><i class="fas fa-stream"></i></div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="small-box {{ ($kpis['warranty_due_30'] ?? 0) > 20 ? 'bg-danger' : (($kpis['warranty_due_30'] ?? 0) > 0 ? 'bg-warning' : 'bg-success') }} shadow-sm">
                        <div class="inner">
                            <h3>{{ $kpis['warranty_due_30'] ?? 0 }}</h3>
                            <p>Garantía &le; 30 días</p>
                        </div>
                        <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Fase 2: distribución y salud de mantenimientos --}}
        <div class="card-body pt-1 pb-1 border-top">
            <div class="row">
                <div class="col-lg-4">
                    <div class="card card-outline card-secondary h-100">
                        <div class="card-header py-2">
                            <h3 class="card-title text-sm font-weight-bold">
                                <i class="fas fa-layer-group mr-1"></i> Distribución por estatus
                            </h3>
                        </div>
                        <div class="card-body p-2">
                            @php $maxStatus = max(array_column($statusDistribution ?: [['total'=>0]], 'total')) ?: 1; @endphp
                            @forelse($statusDistribution as $row)
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between text-xs">
                                        <span>{{ $row['label'] }}</span>
                                        <strong>{{ $row['total'] }}</strong>
                                    </div>
                                    <div class="progress progress-xs">
                                        <div class="progress-bar bg-primary" style="width: {{ round(($row['total'] / $maxStatus) * 100) }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <small class="text-muted">Sin datos para mostrar.</small>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card card-outline card-secondary h-100">
                        <div class="card-header py-2">
                            <h3 class="card-title text-sm font-weight-bold">
                                <i class="fas fa-tags mr-1"></i> Top categorías
                            </h3>
                        </div>
                        <div class="card-body p-2">
                            @php $maxCat = max(array_column($categoryDistribution ?: [['total'=>0]], 'total')) ?: 1; @endphp
                            @forelse($categoryDistribution as $row)
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between text-xs">
                                        <span>{{ $row['label'] }}</span>
                                        <strong>{{ $row['total'] }}</strong>
                                    </div>
                                    <div class="progress progress-xs">
                                        <div class="progress-bar bg-info" style="width: {{ round(($row['total'] / $maxCat) * 100) }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <small class="text-muted">Sin datos para mostrar.</small>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card card-outline card-secondary h-100">
                        <div class="card-header py-2">
                            <h3 class="card-title text-sm font-weight-bold">
                                <i class="fas fa-tools mr-1"></i> Salud de mantenimientos
                            </h3>
                        </div>
                        <div class="card-body p-2">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="text-xs text-muted">Abiertos</div>
                                    <div class="h5 mb-1 text-warning">{{ $maintenanceSummary['open'] ?? 0 }}</div>
                                </div>
                                <div class="col-4">
                                    <div class="text-xs text-muted">Cerrados</div>
                                    <div class="h5 mb-1 text-success">{{ $maintenanceSummary['closed'] ?? 0 }}</div>
                                </div>
                                <div class="col-4">
                                    <div class="text-xs text-muted">En rango</div>
                                    <div class="h5 mb-1 text-primary">{{ $maintenanceSummary['in_range'] ?? 0 }}</div>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="text-xs d-flex justify-content-between">
                                <span>Costo total rango:</span>
                                <strong>${{ number_format($maintenanceSummary['total_cost'] ?? 0, 2) }}</strong>
                            </div>
                            <div class="text-xs d-flex justify-content-between">
                                <span>Promedio por mantenimiento:</span>
                                <strong>${{ number_format($maintenanceSummary['avg_cost'] ?? 0, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-lg-6">
                    <div class="card card-outline card-secondary h-100">
                        <div class="card-header py-2">
                            <h3 class="card-title text-sm font-weight-bold">
                                <i class="fas fa-clipboard-check mr-1"></i> Distribución por condición
                            </h3>
                        </div>
                        <div class="card-body p-2">
                            @php $maxCond = max(array_column($conditionDistribution ?: [['total'=>0]], 'total')) ?: 1; @endphp
                            @forelse($conditionDistribution as $row)
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between text-xs">
                                        <span>{{ $row['label'] }}</span>
                                        <strong>{{ $row['total'] }}</strong>
                                    </div>
                                    <div class="progress progress-xs">
                                        <div class="progress-bar bg-secondary" style="width: {{ round(($row['total'] / $maxCond) * 100) }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <small class="text-muted">Sin datos para mostrar.</small>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card card-outline card-secondary h-100">
                        <div class="card-header py-2">
                            <h3 class="card-title text-sm font-weight-bold">
                                <i class="fas fa-dollar-sign mr-1"></i> Costo mensual de mantenimientos
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="bg-light text-xs">
                                        <tr>
                                            <th>Periodo</th>
                                            <th>Mantenimientos</th>
                                            <th>Costo total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm">
                                        @forelse($maintenanceMonthlyCost as $row)
                                            <tr>
                                                <td>{{ $row['period'] }}</td>
                                                <td>{{ $row['total_items'] }}</td>
                                                <td>${{ number_format($row['total_cost'], 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted small py-3">Sin costos registrados en el rango.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Fase 3: alertas inteligentes de control operativo --}}
        <div class="card-body pt-1 pb-1 border-top">
            <div class="card card-outline card-danger mb-0">
                <div class="card-header py-2">
                    <h3 class="card-title text-sm font-weight-bold">
                        <i class="fas fa-bell mr-1"></i> Alertas inteligentes
                    </h3>
                </div>
                <div class="card-body p-2">
                    @forelse($alerts as $alert)
                        <div class="alert alert-{{ $alert['level'] }} py-2 px-3 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $alert['title'] }}</strong>
                                    <div class="small">{{ $alert['description'] }}</div>
                                </div>
                                <div class="text-right">
                                    <span class="badge badge-light border d-block mb-1">{{ $alert['value'] }}</span>
                                    <button type="button" wire:click="showAlertDetail('{{ $alert['type'] }}')" class="btn btn-xs btn-outline-dark">
                                        Ver detalle
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-success py-2 px-3 mb-0">
                            <i class="fas fa-check-circle mr-1"></i> Sin alertas críticas para los filtros seleccionados.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Detalle filtrado de alerta (drill-down de fase 3) --}}
        @if(!empty($selectedAlertType))
            <div class="card-body pt-1 pb-1 border-top">
                <div class="card card-outline card-secondary mb-0">
                    <div class="card-header py-2">
                        <h3 class="card-title text-sm font-weight-bold">
                            <i class="fas fa-search-plus mr-1"></i> Detalle de alerta
                        </h3>
                        <div class="card-tools">
                            <button type="button" wire:click="clearAlertDetail" class="btn btn-xs btn-outline-secondary">Cerrar detalle</button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-bordered mb-0">
                                <thead class="bg-light text-xs">
                                    <tr>
                                        <th>ID</th>
                                        <th>Etiqueta</th>
                                        <th>Activo</th>
                                        <th>Estatus</th>
                                        <th>Empresa</th>
                                        <th>Sede</th>
                                        <th>Detalle</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm">
                                    @forelse($alertDetailRows as $row)
                                        <tr>
                                            <td>#{{ $row['asset_id'] }}</td>
                                            <td>{{ $row['tag'] ?: 'N/D' }}</td>
                                            <td>{{ $row['name'] }}</td>
                                            <td>{{ $row['status'] ?: 'N/D' }}</td>
                                            <td>{{ $row['company'] ?: 'N/D' }}</td>
                                            <td>{{ $row['sede'] ?: 'N/D' }}</td>
                                            <td>{{ $row['extra'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted small py-3">
                                                No hay registros para esta alerta en los filtros actuales.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Timeline y tabla de cambios (fase 1) --}}
        <div class="card-body pt-1">
            <div class="row">
                <div class="col-lg-5">
                    <div class="card card-outline card-secondary h-100">
                        <div class="card-header py-2">
                            <h3 class="card-title text-sm font-weight-bold">
                                <i class="fas fa-history mr-1"></i> Timeline de eventos
                            </h3>
                        </div>
                        <div class="card-body p-2" style="max-height: 450px; overflow-y: auto;">
                            @forelse($timeline as $event)
                                <div class="border rounded p-2 mb-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="badge badge-{{ $event['color'] ?? 'secondary' }}">{{ $event['type'] }}</span>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($event['date'])->format('d/m/Y H:i') }}</small>
                                    </div>
                                    <div class="mt-1 text-sm">
                                        <strong>{{ $event['title'] }}</strong> • Activo {{ $event['asset'] }}
                                    </div>
                                    <div class="text-xs text-muted">Por: {{ $event['actor'] }}</div>
                                    @if(!empty($event['notes']))
                                        <div class="text-xs mt-1">{{ \Illuminate\Support\Str::limit($event['notes'], 120) }}</div>
                                    @endif
                                </div>
                            @empty
                                @include('partials.empty-state', [
                                    'icon' => 'fa-clock',
                                    'message' => 'No hay eventos en el rango seleccionado.',
                                ])
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card card-outline card-secondary h-100">
                        <div class="card-header py-2">
                            <h3 class="card-title text-sm font-weight-bold">
                                <i class="fas fa-clipboard-list mr-1"></i> Últimos cambios auditados
                            </h3>
                            <div class="card-tools d-flex align-items-center">
                                <label class="mb-0 mr-1 text-xs text-muted">Mostrar</label>
                                <select wire:model.live="changesPerPage" class="form-control form-control-sm" style="width: 86px;">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-bordered mb-0">
                                    <thead class="bg-light">
                                        <tr class="text-xs">
                                            <th>Fecha</th>
                                            <th>Activo</th>
                                            <th>Usuario</th>
                                            <th>Acción</th>
                                            <th>Campos</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm">
                                        @forelse(($changeRows ? $changeRows->items() : []) as $row)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y H:i') }}</td>
                                                <td>#{{ $row['asset_id'] }}</td>
                                                <td>{{ $row['actor'] }}</td>
                                                <td>{{ strtoupper($row['action']) }}</td>
                                                <td>
                                                    @if(!empty($row['fields']))
                                                        {{ implode(', ', $row['fields']) }}
                                                    @else
                                                        <span class="text-muted">N/D</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="p-0">
                                                    @include('partials.empty-state', [
                                                        'icon' => 'fa-clipboard',
                                                        'message' => 'No hay cambios auditados para los filtros actuales.',
                                                    ])
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if($changeRows && method_exists($changeRows, 'links'))
                                <div class="p-2 border-top">
                                    {{ $changeRows->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

