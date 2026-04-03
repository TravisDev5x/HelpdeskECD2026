<div>
    @include('partials.breadcrumb', ['items' => [
        ['text' => 'Inicio', 'url' => route('home')],
        ['text' => 'Inventario V2', 'url' => route('inventory.v2.index')],
        ['text' => 'Mantenimientos', 'url' => null],
    ]])

    <div class="card card-primary card-outline shadow-sm">
        <div class="card-header py-2">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-wrench text-primary mr-1"></i> Mantenimientos de activos
                </h3>
                <div class="card-tools">
                    @can('manage inventory maintenance catalogs')
                        <a href="{{ route('inventory.config.maintenance-catalogs') }}" class="btn btn-outline-info btn-sm mr-1" title="Origen y modalidad (catálogos)">
                            <i class="fas fa-cog mr-1"></i> Catálogos
                        </a>
                    @endcan
                    <a href="{{ route('inventory.v2.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-cubes mr-1"></i> Listado de activos
                    </a>
                </div>
            </div>
            <p class="text-muted small mb-0 mt-2">Use <strong>Acciones → +</strong> en cada fila para registrar mantenimiento a un activo, o marque varios y use el botón inferior (mismo evento para todos).</p>
        </div>

        <div class="card-body py-2">
            <div class="row align-items-center bg-light p-2 rounded mx-0">
                <div class="col-lg-3 col-md-6 mb-2 mb-lg-0 px-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-left-0" placeholder="Etiqueta, serie o nombre">
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-2 mb-lg-0 px-1">
                    <select wire:model.live="category_filter" class="form-control form-control-sm">
                        <option value="">Todas las categorías</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 mb-2 mb-lg-0 px-1">
                    <select wire:model.live="status_filter" class="form-control form-control-sm">
                        <option value="">Todos los estatus</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st->id }}">{{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 mb-2 mb-lg-0 px-1">
                    <select wire:model.live="sede_filter" class="form-control form-control-sm">
                        <option value="">Todas las sedes</option>
                        @foreach ($sedes as $sede)
                            <option value="{{ $sede->id }}">{{ $sede->sede }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-12 px-1 text-lg-right">
                    <select wire:model.live="perPage" class="form-control form-control-sm d-inline-block w-auto">
                        <option value="15">15 / pág.</option>
                        <option value="25">25 / pág.</option>
                        <option value="50">50 / pág.</option>
                    </select>
                </div>
            </div>

            @if (count($selected) > 0)
                <div class="alert alert-info d-flex flex-wrap justify-content-between align-items-center py-2 px-3 mt-2 mb-0">
                    <span class="text-sm mb-1 mb-md-0"><strong>{{ count($selected) }}</strong> activo(s) seleccionado(s)</span>
                    @can('edit inventory')
                        <button type="button" wire:click="openMaintModal" class="btn btn-primary btn-sm font-weight-bold" wire:loading.attr="disabled">
                            <i class="fas fa-clipboard-check mr-1"></i> Registrar mantenimiento
                        </button>
                    @endcan
                </div>
            @endif
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover table-striped mb-0 text-sm">
                    <thead class="bg-light text-uppercase text-xs">
                        <tr>
                            <th style="width: 36px;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="mt-check-all" wire:model.live="selectAll">
                                    <label class="custom-control-label" for="mt-check-all"></label>
                                </div>
                            </th>
                            <th>Etiqueta</th>
                            <th>Equipo</th>
                            <th>Categoría</th>
                            <th>Estatus</th>
                            <th>Sede</th>
                            <th class="text-center">Mant. abiertos</th>
                            <th class="text-center" style="min-width: 100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $asset)
                            <tr class="{{ in_array((string) $asset->id, $selected, true) ? 'table-warning' : '' }}">
                                <td class="align-middle text-center">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="mt-chk-{{ $asset->id }}" value="{{ $asset->id }}" wire:model.live="selected">
                                        <label class="custom-control-label" for="mt-chk-{{ $asset->id }}"></label>
                                    </div>
                                </td>
                                <td class="align-middle font-weight-bold text-primary">{{ $asset->internal_tag ?? '—' }}</td>
                                <td class="align-middle">{{ Str::limit($asset->name, 40) }}</td>
                                <td class="align-middle text-muted">{{ $asset->category->name ?? '—' }}</td>
                                <td class="align-middle"><span class="badge badge-secondary">{{ $asset->status->name ?? '—' }}</span></td>
                                <td class="align-middle text-muted small">{{ Str::limit($asset->sede->sede ?? '—', 18) }}</td>
                                <td class="align-middle text-center">
                                    @if(($asset->open_maintenances_count ?? 0) > 0)
                                        <span class="badge badge-warning">{{ $asset->open_maintenances_count }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="align-middle text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        @can('edit inventory')
                                            <button type="button" class="btn btn-xs btn-primary" wire:click="openMaintModalForAsset({{ $asset->id }})" title="Registrar mantenimiento">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        @endcan
                                        <button type="button" class="btn btn-xs btn-outline-secondary" wire:click="openTraceModal({{ $asset->id }})" title="Ver historial de mantenimientos">
                                            <i class="fas fa-history"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No hay activos con estos filtros.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer py-2">
            {{ $assets->links() }}
        </div>
    </div>

    @if ($showMaintModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.55); z-index: 1050;" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header py-2 bg-primary text-white">
                        <h5 class="modal-title text-sm font-weight-bold">
                            <i class="fas fa-wrench mr-1"></i> Mantenimiento para {{ count($selected) }} activo(s)
                        </h5>
                        <button type="button" class="close text-white" wire:click="closeMaintModal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted">Se creará un registro <strong>por cada activo seleccionado</strong> con los mismos datos.</p>
                        <div class="row">
                            <div class="col-md-6 form-group mb-2">
                                <label class="text-xs font-weight-bold">Origen *</label>
                                <select class="form-control form-control-sm" wire:model="mt_origin_id" @if($maintenanceOrigins->isEmpty()) disabled @endif>
                                    <option value="">{{ $maintenanceOrigins->isEmpty() ? '— Sin catálogo (ejecute seeder)' : 'Seleccione…' }}</option>
                                    @foreach ($maintenanceOrigins as $o)
                                        <option value="{{ $o->id }}">{{ $o->name }}</option>
                                    @endforeach
                                </select>
                                @error('mt_origin_id') <span class="text-danger text-xs d-block">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 form-group mb-2">
                                <label class="text-xs font-weight-bold">Modalidad *</label>
                                <select class="form-control form-control-sm" wire:model="mt_modality_id" @if($maintenanceModalities->isEmpty()) disabled @endif>
                                    <option value="">{{ $maintenanceModalities->isEmpty() ? '— Sin catálogo' : 'Seleccione…' }}</option>
                                    @foreach ($maintenanceModalities as $m)
                                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                                    @endforeach
                                </select>
                                @error('mt_modality_id') <span class="text-danger text-xs d-block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Título *</label>
                            <input type="text" class="form-control form-control-sm" wire:model="mt_title" placeholder="Ej: Cambio de pantalla" maxlength="255">
                            @error('mt_title') <span class="text-danger text-xs d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Diagnóstico *</label>
                            <textarea class="form-control form-control-sm" rows="2" wire:model="mt_diagnosis"></textarea>
                            @error('mt_diagnosis') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Solución / trabajo (opcional)</label>
                            <textarea class="form-control form-control-sm" rows="2" wire:model="mt_solution"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 form-group mb-2">
                                <label class="text-xs font-weight-bold">Inicio *</label>
                                <input type="date" class="form-control form-control-sm" wire:model="mt_start_date">
                                @error('mt_start_date') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                <label class="text-xs font-weight-bold">Cierre (opcional)</label>
                                <input type="date" class="form-control form-control-sm" wire:model="mt_end_date">
                                @error('mt_end_date') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4 form-group mb-2">
                                <label class="text-xs font-weight-bold">Costo (c/u)</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                    <input type="number" step="0.01" class="form-control" wire:model="mt_cost" placeholder="0">
                                </div>
                                @error('mt_cost') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="custom-control custom-checkbox mb-0">
                            <input type="checkbox" class="custom-control-input" id="mt-allow-multi" wire:model.live="mt_allow_multiple_open">
                            <label class="custom-control-label text-xs" for="mt-allow-multi">Permitir otro mantenimiento <strong>abierto</strong> aunque el activo ya tenga uno (casos excepcionales).</label>
                        </div>
                        @error('mt_allow_multiple_open') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="closeMaintModal">Cancelar</button>
                        <button type="button" class="btn btn-primary btn-sm font-weight-bold" wire:click="storeBulkMaintenance" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="storeBulkMaintenance"><i class="fas fa-save mr-1"></i> Guardar en todos</span>
                            <span wire:loading wire:target="storeBulkMaintenance"><i class="fas fa-spinner fa-spin mr-1"></i></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($showTraceModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.55); z-index: 1060;" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header py-2 bg-dark text-white">
                        <h5 class="modal-title text-sm font-weight-bold">
                            <i class="fas fa-history mr-1"></i>
                            @if($traceAsset)
                                Mantenimientos — <span class="text-warning">{{ $traceAsset->internal_tag ?? 'Activo #'.$traceAsset->id }}</span>
                            @else
                                Mantenimientos
                            @endif
                        </h5>
                        <button type="button" class="close text-white" wire:click="closeTraceModal">&times;</button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh;">
                        @if(!$traceAsset)
                            <p class="text-muted small mb-0">No se pudo cargar el activo.</p>
                        @else
                            <p class="small text-muted mb-2">{{ $traceAsset->name }}</p>
                            @forelse($traceAsset->maintenances as $m)
                                <div class="border rounded p-2 mb-2 text-xs">
                                    <div class="d-flex flex-wrap justify-content-between align-items-start">
                                        <div>
                                            <strong>{{ $m->title }}</strong>
                                            @if($m->end_date)
                                                <span class="badge badge-secondary ml-1">Cerrado</span>
                                            @else
                                                <span class="badge badge-warning ml-1">Abierto</span>
                                            @endif
                                            @if($m->origin)<span class="badge badge-light border ml-1">{{ $m->origin->name }}</span>@endif
                                            @if($m->modality)<span class="badge badge-info ml-1">{{ $m->modality->name }}</span>@endif
                                        </div>
                                        @can('edit inventory')
                                            @if(!$m->end_date)
                                                <button type="button" class="btn btn-outline-success btn-xs py-0 px-1" wire:click="closeMaintenance({{ $m->id }})" wire:confirm="¿Marcar este mantenimiento como cerrado con la fecha de hoy?" title="Cerrar">Cerrar</button>
                                            @endif
                                        @endcan
                                    </div>
                                    <div class="mt-1 text-muted">
                                        <i class="far fa-calendar-alt mr-1"></i>Inicio: {{ $m->start_date->format('d/m/Y') }}
                                        @if($m->end_date) · Fin: {{ $m->end_date->format('d/m/Y') }} @endif
                                        @if($m->cost) · <span class="text-success">${{ number_format($m->cost, 2) }}</span> @endif
                                    </div>
                                    @if($m->logger)
                                        <div class="mt-1"><i class="fas fa-user mr-1"></i>{{ trim($m->logger->name . ' ' . ($m->logger->ap_paterno ?? '')) }}</div>
                                    @endif
                                    <div class="mt-1"><span class="font-weight-bold text-secondary">Diagnóstico:</span> {{ $m->diagnosis }}</div>
                                    @if($m->solution)
                                        <div class="mt-1"><span class="font-weight-bold text-secondary">Solución:</span> {{ $m->solution }}</div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-muted small">Sin mantenimientos registrados para este activo.</div>
                            @endforelse
                        @endif
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="closeTraceModal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
