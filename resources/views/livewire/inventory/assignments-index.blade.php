<div>
    @include('partials.breadcrumb', ['items' => [
        ['text' => 'Inicio', 'url' => route('home')],
        ['text' => 'Inventario V2', 'url' => route('inventory.v2.index')],
        ['text' => 'Asignaciones por activo', 'url' => null],
    ]])

    <div class="card card-outline card-info shadow-sm">
        <div class="card-header py-2 d-flex flex-wrap align-items-center justify-content-between">
            <h3 class="card-title text-sm mb-0">
                <i class="fas fa-list-ul text-info mr-1"></i> Asignaciones por activo
            </h3>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="text-muted small">
                    Global: <strong>{{ $totalAssignees }}</strong> responsable(s) ·
                    <strong>{{ $totalAssignedAssets }}</strong> activo(s)
                </span>
                <a href="{{ route('inventory.v2.assignments.summary') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-users mr-1"></i> Resumen por responsable
                </a>
            </div>
        </div>
        <div class="card-body pt-2 pb-2">
            <p class="text-muted small mb-2">
                Una fila por equipo con responsable. Para ver <strong>cuántos activos tiene cada persona</strong> (tarjetas) usa
                <a href="{{ route('inventory.v2.assignments.summary') }}">Resumen por responsable</a>.
            </p>

            <div class="row align-items-center bg-light p-2 rounded mx-0 mb-2">
                <div class="col-md-3 col-sm-6 mb-2 mb-md-0 px-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-left-0"
                            placeholder="Etiqueta, nombre o serie">
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2 mb-md-0 px-1">
                    <select wire:model.live="user_filter" class="form-control form-control-sm" title="Solo aparecen personas con activos asignados según búsqueda y sede">
                        <option value="">Todos los responsables (en contexto)</option>
                        @foreach ($assignees as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} {{ $u->ap_paterno ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6 mb-2 mb-md-0 px-1">
                    <select wire:model.live="sede_filter" class="form-control form-control-sm">
                        <option value="">Todas las sedes</option>
                        @foreach ($sedes as $sede)
                            <option value="{{ $sede->id }}">{{ $sede->sede }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6 mb-2 mb-md-0 px-1 text-md-right">
                    <select wire:model.live="perPage" class="form-control form-control-sm d-inline-block w-auto">
                        <option value="15">15 por página</option>
                        <option value="25">25 por página</option>
                        <option value="50">50 por página</option>
                        <option value="100">100 por página</option>
                    </select>
                </div>
            </div>
            <div class="row align-items-center mx-0 mb-2">
                <div class="col-md-6 col-lg-4 px-1">
                    <label class="sr-only">Estado del responsable</label>
                    <select wire:model.live="assignee_employment" class="form-control form-control-sm" title="Filtrar por si el usuario responsable sigue activo en el sistema">
                        <option value="">Responsable: todos (activos y bajas)</option>
                        <option value="active">Solo responsable en nómina (activo)</option>
                        <option value="baja">Solo responsable dado de baja</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-striped table-bordered mb-0">
                    <thead class="bg-light text-uppercase text-xs">
                        <tr>
                            <th>Responsable</th>
                            <th>Etiqueta</th>
                            <th>Equipo</th>
                            <th>Categoría</th>
                            <th>Estatus</th>
                            <th>Sede</th>
                            <th class="text-center" style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assets as $asset)
                            @php
                                $assigneeEnBaja = $asset->currentUser && $asset->currentUser->trashed();
                            @endphp
                            <tr class="{{ $assigneeEnBaja ? 'table-warning' : '' }}">
                                <td class="text-sm">
                                    @if ($asset->currentUser)
                                        @if ($assigneeEnBaja)
                                            <div class="alert alert-danger py-1 px-2 mb-1 small">
                                                <i class="fas fa-exclamation-triangle mr-1"></i><strong>Responsable dado de baja</strong> — reasignar o devolver equipo.
                                            </div>
                                        @endif
                                        <strong>{{ $asset->currentUser->name }} {{ $asset->currentUser->ap_paterno ?? '' }}</strong>
                                        @if ($asset->currentUser->usuario)
                                            <br><span class="text-muted">{{ $asset->currentUser->usuario }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">Usuario no disponible</span>
                                    @endif
                                </td>
                                <td class="text-monospace text-sm">{{ $asset->internal_tag ?? '—' }}</td>
                                <td class="text-sm">{{ $asset->name }}</td>
                                <td class="text-sm">{{ $asset->category->name ?? '—' }}</td>
                                <td class="text-sm">
                                    @if ($asset->status)
                                        <span class="badge badge-{{ $asset->status->badge_class ?? 'secondary' }}">{{ $asset->status->name }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-sm">{{ $asset->sede->sede ?? '—' }}</td>
                                <td class="text-center text-nowrap">
                                    <a href="{{ route('inventory.v2.index', ['user_filter' => $asset->current_user_id]) }}"
                                       class="btn btn-xs btn-outline-primary" title="Ver en listado de inventario con filtro por este responsable">
                                        <i class="fas fa-external-link-alt mr-1"></i> Inventario
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    @include('partials.empty-state', [
                                        'icon' => 'fa-user-slash',
                                        'message' => 'Sin resultados: no hay activos con responsable que coincidan con los filtros.',
                                    ])
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-2 d-flex justify-content-between align-items-center flex-wrap">
                <a href="{{ route('inventory.v2.index') }}" class="btn btn-sm btn-default">
                    <i class="fas fa-list-ul mr-1"></i> Ir al listado completo de activos
                </a>
                <div>
                    {{ $assets->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
