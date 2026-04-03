<div>
    @php
        $bcInvV2 = [
            ['text' => 'Inicio', 'url' => route('home')],
        ];
        if ($canFullInventory) {
            $bcInvV2[] = ['text' => 'Inventario V2', 'url' => route('inventory.v2.index')];
        } else {
            $bcInvV2[] = ['text' => 'Inventario V2', 'url' => null];
        }
        $bcInvV2[] = ['text' => 'Mis equipos asignados', 'url' => null];
    @endphp
    @include('partials.breadcrumb', ['items' => $bcInvV2])

    <div class="card card-outline card-success shadow-sm">
        <div class="card-header py-2 d-flex flex-wrap align-items-center justify-content-between">
            <h3 class="card-title text-sm mb-0">
                <i class="fas fa-user-check text-success mr-1"></i> Mis equipos asignados (Inventario V2)
            </h3>
            <span class="text-muted small">
                Tienes <strong>{{ $myAssignedCount }}</strong> activo(s) a tu nombre en este inventario.
            </span>
        </div>
        <div class="card-body pt-2 pb-2">
            <p class="text-muted small mb-2">
                Listado de activos cuya <strong>responsabilidad</strong> eres tú. Si necesitas el catálogo completo o asignaciones de otras personas, solicita el permiso correspondiente al administrador.
            </p>

            <div class="row align-items-center bg-light p-2 rounded mx-0 mb-2">
                <div class="col-md-4 col-sm-6 mb-2 mb-md-0 px-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-left-0" placeholder="Etiqueta, nombre o serie">
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-2 mb-md-0 px-1">
                    <select wire:model.live="sede_filter" class="form-control form-control-sm">
                        <option value="">Todas las sedes</option>
                        @foreach ($sedes as $sede)
                            <option value="{{ $sede->id }}">{{ $sede->sede }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-sm-6 mb-2 mb-md-0 px-1 text-md-right">
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
                    <label class="sr-only">Estado en nómina</label>
                    <select wire:model.live="assignee_employment" class="form-control form-control-sm" title="Solo aplica si tu usuario está activo o dado de baja en el sistema">
                        <option value="">Mi usuario: todos los estados</option>
                        <option value="active">Solo si sigo activo en nómina</option>
                        <option value="baja">Solo si estoy dado de baja (equipos pendientes)</option>
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
                                                <i class="fas fa-exclamation-triangle mr-1"></i><strong>Dado de baja</strong> — contacta a sistemas para reasignación.
                                            </div>
                                        @endif
                                        <strong>{{ $asset->currentUser->name }} {{ $asset->currentUser->ap_paterno ?? '' }}</strong>
                                        @if ($asset->currentUser->usuario)
                                            <br><span class="text-muted">{{ $asset->currentUser->usuario }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
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
                                    @if ($canFullInventory)
                                        <a href="{{ route('inventory.v2.index', ['user_filter' => $asset->current_user_id]) }}" class="btn btn-xs btn-outline-primary" title="Ver en listado de inventario">
                                            <i class="fas fa-external-link-alt mr-1"></i> Inventario
                                        </a>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    @include('partials.empty-state', [
                                        'icon' => 'fa-laptop-house',
                                        'message' => 'No tienes activos asignados en inventario V2 con estos filtros.',
                                    ])
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-2 d-flex justify-content-between align-items-center flex-wrap">
                @if ($canFullInventory)
                    <a href="{{ route('inventory.v2.index') }}" class="btn btn-sm btn-default">
                        <i class="fas fa-list-ul mr-1"></i> Ir al listado completo de activos
                    </a>
                @else
                    <span></span>
                @endif
                <div>
                    {{ $assets->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
