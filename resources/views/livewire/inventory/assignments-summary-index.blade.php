<div>
    @include('partials.breadcrumb', ['items' => [
        ['text' => 'Inicio', 'url' => route('home')],
        ['text' => 'Inventario V2', 'url' => route('inventory.v2.index')],
        ['text' => 'Resumen de asignaciones', 'url' => null],
    ]])

    <div class="card card-outline card-secondary shadow-sm">
        <div class="card-header py-2 d-flex flex-wrap align-items-center justify-content-between">
            <h3 class="card-title text-sm mb-0">
                <i class="fas fa-users text-secondary mr-1"></i> Resumen por responsable
            </h3>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="text-muted small">
                    Global: <strong>{{ $totalAssignees }}</strong> responsable(s) ·
                    <strong>{{ $totalAssignedAssets }}</strong> activo(s)
                </span>
                <a href="{{ route('inventory.v2.assignments') }}" class="btn btn-sm btn-outline-info">
                    <i class="fas fa-list-ul mr-1"></i> Ver listado por activo
                </a>
            </div>
        </div>
        <div class="card-body pt-3 pb-3">
            <p class="text-muted small mb-3">
                Cantidad de equipos asignados a cada persona según los filtros.
                Para ver fila por fila (paginado) usa <strong>Listado por activo</strong>.
            </p>

            <div class="row align-items-center bg-light p-2 rounded mx-0 mb-3">
                <div class="col-md-4 col-sm-6 mb-2 mb-md-0 px-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-left-0"
                            placeholder="Etiqueta, nombre o serie del activo">
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-2 mb-md-0 px-1">
                    <select wire:model.live="user_filter" class="form-control form-control-sm" title="Solo usuarios con activos asignados según búsqueda y sede">
                        <option value="">Todos los responsables (en contexto)</option>
                        @foreach ($assignees as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} {{ $u->ap_paterno ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-sm-12 mb-2 mb-md-0 px-1">
                    <select wire:model.live="sede_filter" class="form-control form-control-sm">
                        <option value="">Todas las sedes</option>
                        @foreach ($sedes as $sede)
                            <option value="{{ $sede->id }}">{{ $sede->sede }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row align-items-center mx-0 mb-3">
                <div class="col-md-6 col-lg-4 px-1">
                    <select wire:model.live="assignee_employment" class="form-control form-control-sm" title="Responsable activo en sistema vs dado de baja">
                        <option value="">Responsable: todos</option>
                        <option value="active">Solo responsable en nómina</option>
                        <option value="baja">Solo responsable dado de baja</option>
                    </select>
                </div>
            </div>

            @if ($summaryByPerson->isNotEmpty())
                <p class="text-muted small mb-2">
                    Con filtros actuales: <strong>{{ $filteredTotal }}</strong> activo(s) en
                    <strong>{{ $summaryByPerson->count() }}</strong> responsable(s).
                    @if ($user_filter !== '')
                        <button type="button" wire:click="$set('user_filter', '')" class="btn btn-link btn-sm p-0 align-baseline">Quitar filtro de persona</button>
                    @endif
                </p>
            @endif

            @if ($summaryByPerson->isEmpty())
                <div class="alert alert-light border mb-0">
                    @include('partials.empty-state', [
                        'icon' => 'fa-user-slash',
                        'message' => 'No hay activos asignados que coincidan con los filtros.',
                    ])
                </div>
            @else
                <div class="rounded border bg-white p-3" style="min-height: 200px; max-height: calc(100vh - 320px); overflow-y: auto;">
                    <div class="row mx-n2">
                        @foreach ($summaryByPerson as $row)
                            @php
                                $u = $row['user'];
                                $isActiveCard = (string) $user_filter === (string) $row['user_id'];
                                $detailParams = array_filter([
                                    'search' => $search !== '' ? $search : null,
                                    'user_filter' => (string) $row['user_id'],
                                    'sede_filter' => $sede_filter !== '' ? $sede_filter : null,
                                    'assignee_employment' => ($u && $u->trashed()) ? 'baja' : ($assignee_employment !== '' ? $assignee_employment : null),
                                ], fn ($v) => $v !== null && $v !== '');
                            @endphp
                            <div class="col-xl-3 col-lg-4 col-md-6 px-2 mb-3">
                                <div class="card h-100 shadow-sm mb-0 {{ $isActiveCard ? 'border-primary border-2' : '' }} {{ $u && $u->trashed() ? 'border-warning' : '' }} border">
                                    <div class="card-body py-3 px-3 d-flex flex-column">
                                        @if ($u && $u->trashed())
                                            <div class="alert alert-warning py-1 px-2 small mb-2">
                                                <i class="fas fa-user-times mr-1"></i><strong>Usuario en baja</strong> con equipos aún asignados.
                                            </div>
                                        @endif
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="pr-2 min-w-0">
                                                <div class="font-weight-bold text-sm text-truncate" title="@if($u){{ $u->name }} {{ $u->ap_paterno ?? '' }}@endif">
                                                    @if ($u)
                                                        {{ $u->name }} {{ $u->ap_paterno ?? '' }}
                                                        @if ($u->trashed())
                                                            <span class="badge badge-danger">Baja</span>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">Usuario #{{ $row['user_id'] }}</span>
                                                    @endif
                                                </div>
                                                @if ($u && $u->usuario)
                                                    <div class="text-xs text-muted text-truncate">{{ $u->usuario }}</div>
                                                @endif
                                            </div>
                                            <div class="text-right flex-shrink-0">
                                                <span class="h3 mb-0 text-info font-weight-bold d-block">{{ $row['count'] }}</span>
                                                <span class="text-xs text-muted">activo(s)</span>
                                            </div>
                                        </div>
                                        <div class="mt-3 pt-2 border-top d-flex flex-wrap" style="gap: 6px;">
                                            <button type="button"
                                                wire:click="$set('user_filter', '{{ $row['user_id'] }}')"
                                                class="btn btn-sm btn-outline-secondary"
                                                title="Mostrar solo esta persona en el resumen">
                                                <i class="fas fa-user mr-1"></i> Solo esta persona
                                            </button>
                                            <a href="{{ route('inventory.v2.assignments', $detailParams) }}"
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-list-ul mr-1"></i> Listado
                                            </a>
                                            <a href="{{ route('inventory.v2.index', ['user_filter' => $row['user_id']]) }}"
                                               class="btn btn-sm btn-default">
                                                <i class="fas fa-external-link-alt mr-1"></i> Inventario
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-3 d-flex flex-wrap justify-content-between align-items-center">
                <a href="{{ route('inventory.v2.index') }}" class="btn btn-sm btn-default">
                    <i class="fas fa-cubes mr-1"></i> Inventario V2
                </a>
            </div>
        </div>
    </div>
</div>
