<div>
    @include('partials.breadcrumb', ['items' => [
        ['text' => 'Inicio', 'url' => route('home')],
        ['text' => 'Inventario V2', 'url' => route('inventory.v2.index')],
        ['text' => 'Listado de Activos', 'url' => null],
    ]])
    {{-- Estilos tema claro/oscuro: admin-theme.css en layout. Mensajes éxito/error se muestran en layout.blade.php --}}

    {{-- TARJETA PRINCIPAL --}}
    <div class="card card-primary card-outline shadow-sm">

        {{-- HEADER & TOOLBAR --}}
        <div class="card-header py-2">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <h3 class="card-title mb-1 mb-md-0"><i class="fas fa-cubes text-primary mr-1"></i> Inventario General V2</h3>
                <div class="d-flex flex-wrap justify-content-end">
                    <a href="{{ route('inventory.v2.assignments.summary') }}" class="btn btn-outline-info btn-sm mr-1 mb-1" title="Resumen por responsable"><i class="fas fa-users mr-1"></i> Resumen asignaciones</a>
                    <a href="{{ route('inventory.v2.assignments') }}" class="btn btn-outline-secondary btn-sm mr-1 mb-1" title="Listado fila por fila"><i class="fas fa-list-ul mr-1"></i> Por activo</a>
                    <a href="{{ route('inventory.v2.maintenance') }}" class="btn btn-outline-primary btn-sm mr-1 mb-1" title="Registrar mantenimientos a uno o varios activos"><i class="fas fa-wrench mr-1"></i> Mantenimientos</a>
                    <a href="{{ route('inventory.export', ['search' => $search, 'category_filter' => $category_filter, 'status_filter' => $status_filter, 'sede_filter' => $sede_filter, 'user_filter' => $user_filter, 'label_filter' => $label_filter, 'date_field' => $export_date_field, 'date_from' => $export_date_from, 'date_to' => $export_date_to]) }}" class="btn btn-outline-secondary btn-sm mr-1 mb-1" title="Exportar listado actual a Excel"><i class="fas fa-file-excel mr-1"></i> Exportar</a>
                    <button wire:click="openImportModal" class="btn btn-outline-success btn-sm mr-1 mb-1" title="Importar hoja Excel al Inventario V2"><i class="fas fa-file-upload mr-1"></i> Importar Excel</button>
                    <button wire:click="create" class="btn btn-success btn-sm shadow-sm font-weight-bold mb-1" title="Registrar nuevo activo en inventario"><i class="fas fa-plus mr-1"></i> Nuevo Activo</button>
                </div>
            </div>

            {{-- FILTROS --}}
            <div class="bg-light border rounded p-2 mt-2">
                <div class="row mx-0">
                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 px-1 mb-2">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend"><span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span></div>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-left-0" placeholder="Serie, etiqueta o IP" title="Buscar por serie, etiqueta o IP">
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6 px-1 mb-2">
                        <select wire:model.live="category_filter" class="form-control form-control-sm" title="Categoría">
                            <option value="">Todas las categorías</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6 px-1 mb-2">
                        <select wire:model.live="status_filter" class="form-control form-control-sm" title="Estatus">
                            <option value="">Todos los estatus</option>
                            @foreach ($statuses as $st)
                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-4 col-lg-6 col-md-12 col-sm-12 px-1 mb-2">
                        <select wire:model.live="user_filter" class="form-control form-control-sm" title="Solo responsables con activos que cumplen los filtros actuales">
                            <option value="">Todos los responsables (en contexto)</option>
                            @foreach ($assigneeOptions as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} {{ $u->ap_paterno ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mx-0">
                    <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6 px-1 mb-2 mb-xl-0">
                        <select wire:model.live="sede_filter" class="form-control form-control-sm" title="Sede">
                            <option value="">Todas las sedes</option>
                            @foreach ($sedes as $sede)
                                <option value="{{ $sede->id }}">{{ $sede->sede }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6 px-1 mb-2 mb-xl-0">
                        <select wire:model.live="label_filter" class="form-control form-control-sm" title="Etiqueta de sede">
                            <option value="">Todas las etiquetas</option>
                            <option value="missing">Sin etiqueta de sede</option>
                            <option value="with">Con etiqueta de sede</option>
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 px-1 mb-2 mb-xl-0">
                        <select wire:model.live="perPage" class="form-control form-control-sm" title="Filas por página">
                            <option value="10">10 por página</option>
                            <option value="25">25 por página</option>
                            <option value="50">50 por página</option>
                            <option value="100">100 por página</option>
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-8 col-sm-6 px-1 mb-2 mb-xl-0">
                        <select wire:model.live="export_date_field" class="form-control form-control-sm" title="Campo de fecha para filtro y exportación">
                            <option value="created_at">Fecha de alta</option>
                            <option value="purchase_date">Fecha de compra</option>
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6 px-1 mb-2 mb-xl-0">
                        <input type="date" wire:model.live="export_date_from" class="form-control form-control-sm" title="Desde">
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6 px-1 mb-2 mb-xl-0">
                        <input type="date" wire:model.live="export_date_to" class="form-control form-control-sm" title="Hasta">
                    </div>
                </div>
            </div>

            {{-- BARRA ACCIÓN MASIVA --}}
            @if (count($selected) > 0)
                <div
                    class="alert alert-warning d-flex justify-content-between align-items-center shadow-sm py-1 px-3 mt-2 mb-0">
                    <span class="text-sm">
                        <i class="fas fa-check-square text-orange mr-1"></i> <strong>{{ count($selected) }}</strong>
                        items seleccionados
                    </span>
                    <div class="d-flex flex-wrap justify-content-end gap-xs" style="gap: 4px;">
                        <button type="button" wire:click="openBulkAssignment" class="btn btn-info btn-xs shadow-sm" title="Asignar todos los seleccionados a un responsable">
                            <i class="fas fa-user-plus mr-1"></i> Asignar a responsable
                        </button>
                        <button type="button" wire:click="openBulkCheckin" class="btn btn-success btn-xs shadow-sm" title="Devolver / quitar responsable en lote">
                            <i class="fas fa-user-minus mr-1"></i> Desasignar / devolver
                        </button>
                        <button type="button" wire:click="openBulkEdit" class="btn btn-primary btn-xs shadow-sm">
                            <i class="fas fa-edit mr-1"></i> Editar Masivo
                        </button>
                        <button type="button" wire:click="openConfirmDeleteSelected" class="btn btn-danger btn-xs shadow-sm">
                            <i class="fas fa-trash mr-1"></i> Borrar
                        </button>
                    </div>
                </div>
            @endif
        </div>

        {{-- TABLA COMPACTA (EXCEL STYLE) --}}
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered table-sm mb-0">
                    <thead class="bg-light text-uppercase">
                        <tr class="text-xs text-center">
                            <th style="width: 30px;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="checkAll"
                                        wire:model.live="selectAll">
                                    <label class="custom-control-label" for="checkAll"></label>
                                </div>
                            </th>
                            <th class="text-left">Etiqueta / Cat.</th>
                            <th class="text-left">Equipo / Serie</th>
                            <th class="text-left">Detalles Técnicos</th>
                            <th class="text-left">Asignado</th>
                            <th class="text-left">Ubicación</th>
                            <th style="width: 80px;">Estado</th>
                            <th style="width: 200px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse($assets as $asset)
                            <tr class="{{ in_array($asset->id, $selected) ? 'table-warning' : '' }}">
                                {{-- CHECKBOX --}}
                                <td class="text-center align-middle">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="chk-{{ $asset->id }}"
                                            value="{{ $asset->id }}" wire:model.live="selected">
                                        <label class="custom-control-label" for="chk-{{ $asset->id }}"></label>
                                    </div>
                                </td>

                                {{-- ETIQUETA / CAT --}}
                                <td class="align-middle">
                                    <span
                                        class="font-weight-bold d-block text-primary">{{ $asset->internal_tag ?: ($asset->serial ?: 'S/N') }}</span>
                                    <small class="d-block text-muted text-xs"><i class="fas fa-tag mr-1"></i>{{ $asset->label->name ?? 'Sin etiqueta de sede' }}</small>
                                    <small class="text-muted text-xs">{{ $asset->category->name }}</small>
                                </td>

                                {{-- EQUIPO / SERIE --}}
                                <td class="align-middle">
                                    <span class="d-block text-truncate" style="max-width: 180px;"
                                        title="{{ $asset->name }}">
                                        {{ $asset->name }}
                                    </span>
                                    @if ($asset->serial)
                                        <small class="text-monospace text-muted text-xs"><i
                                                class="fas fa-barcode mr-1"></i>{{ $asset->serial }}</small>
                                    @endif
                                </td>

                                {{-- DETALLES --}}
                                <td class="align-middle text-wrap"
                                    style="max-width: 200px; font-size: 0.8rem; line-height: 1.2;">
                                    @if ($asset->specs)
                                        @if (isset($asset->specs['marca']))
                                            <span
                                                class="d-inline-block mr-1"><b>{{ $asset->specs['marca'] }}</b></span>
                                        @endif
                                        @if (isset($asset->specs['modelo']))
                                            <span
                                                class="d-inline-block text-muted">{{ $asset->specs['modelo'] }}</span>
                                        @endif
                                        @if (isset($asset->specs['ip']))
                                            <div class="mt-1 text-info text-xs font-weight-bold"><i
                                                    class="fas fa-network-wired mr-1"></i>{{ $asset->specs['ip'] }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-muted font-italic text-xs">- Sin specs -</span>
                                    @endif
                                </td>

                                {{-- ASIGNADO --}}
                                <td class="align-middle">
                                    @if ($asset->currentUser)
                                        <span class="text-dark font-weight-bold text-xs">
                                            {{ strtoupper(trim($asset->currentUser->name . ' ' . ($asset->currentUser->ap_paterno ?? ''))) }}
                                        </span>
                                    @else
                                        <span class="text-muted font-italic text-xs">Sin asignar</span>
                                    @endif
                                </td>

                                {{-- UBICACIÓN --}}
                                <td class="align-middle">
                                    <small class="d-block text-xs" title="{{ $asset->sede->sede ?? '' }}">
                                        <i class="fas fa-map-marker-alt mr-1 text-secondary"></i>
                                        {{ Str::limit($asset->sede->sede ?? 'Sin sede', 18) }}
                                    </small>
                                    <small class="d-block text-muted text-xs" title="{{ $asset->company->name ?? '' }}">
                                        <i class="fas fa-building mr-1"></i>
                                        {{ Str::limit($asset->company->name ?? 'N/A', 18) }}
                                    </small>
                                </td>

                                {{-- ESTATUS --}}
                                <td class="text-center align-middle">
                                    <span
                                        class="badge badge-{{ $asset->status->badge_class ?? 'secondary' }} text-uppercase"
                                        style="font-size: 0.7rem;">
                                        {{ $asset->status->name }}
                                    </span>
                                </td>

                                {{-- ACCIONES: en móvil menú desplegable con texto, en escritorio botones con icono --}}
                                <td class="text-center align-middle">
                                    {{-- Móvil: menú Acciones con texto --}}
                                    <div class="dropdown d-md-none">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="acc-{{ $asset->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Acciones</button>
                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="acc-{{ $asset->id }}">
                                            @if (!$asset->current_user_id)
                                                <button type="button" wire:click="openAssignment({{ $asset->id }})" class="dropdown-item"><i class="fas fa-user-plus mr-2"></i>Asignar</button>
                                            @else
                                                <button type="button" wire:click="openCheckinModal({{ $asset->id }})" class="dropdown-item"><i class="fas fa-undo mr-2"></i>Devolver equipo</button>
                                            @endif
                                            <button type="button" wire:click="openTrasladoModal({{ $asset->id }})" class="dropdown-item"><i class="fas fa-truck-moving mr-2"></i>Traslado</button>
                                            <button type="button" wire:click="openBajaModal({{ $asset->id }})" class="dropdown-item"><i class="fas fa-archive mr-2"></i>Dar de baja</button>
                                            @if($asset->components()->where('status', '!=', 'SUSPENDIDO')->exists())
                                                <button type="button" wire:click="openDespieceModal({{ $asset->id }})" class="dropdown-item"><i class="fas fa-tools mr-2"></i>Despiece</button>
                                            @endif
                                            <button type="button" wire:click="openPhotoModal({{ $asset->id }})" class="dropdown-item"><i class="fas fa-camera mr-2"></i>Fotos</button>
                                            <button type="button" wire:click="viewAsset({{ $asset->id }})" class="dropdown-item"><i class="fas fa-eye mr-2"></i>Ver ficha</button>
                                            <button type="button" wire:click="edit({{ $asset->id }})" class="dropdown-item"><i class="fas fa-pencil-alt mr-2"></i>Editar</button>
                                            <div class="dropdown-divider"></div>
                                            <button type="button" wire:click="openConfirmDeleteAsset({{ $asset->id }})" class="dropdown-item text-danger"><i class="fas fa-trash mr-2"></i>Eliminar</button>
                                        </div>
                                    </div>
                                    {{-- Escritorio: botones solo icono --}}
                                    <div class="d-none d-md-flex btn-group">
                                        @if (!$asset->current_user_id)
                                            <button wire:click="openAssignment({{ $asset->id }})" class="btn btn-xs btn-outline-primary" title="Asignar"><i class="fas fa-user-plus"></i></button>
                                        @else
                                            <button wire:click="openCheckinModal({{ $asset->id }})" class="btn btn-xs btn-outline-success" title="Devolver equipo"><i class="fas fa-undo"></i></button>
                                        @endif
                                        <button wire:click="openTrasladoModal({{ $asset->id }})" class="btn btn-xs btn-outline-info" title="Traslado sede/ubicación"><i class="fas fa-truck-moving"></i></button>
                                        <button wire:click="openBajaModal({{ $asset->id }})" class="btn btn-xs btn-outline-dark" title="Dar de baja"><i class="fas fa-archive"></i></button>
                                        @if($asset->components()->where('status', '!=', 'SUSPENDIDO')->exists())
                                            <button wire:click="openDespieceModal({{ $asset->id }})" class="btn btn-xs btn-warning" title="Iniciar despiece"><i class="fas fa-tools"></i></button>
                                        @endif
                                        <button wire:click="openPhotoModal({{ $asset->id }})" class="btn btn-xs btn-outline-secondary" title="Subir Evidencia/Fotos"><i class="fas fa-camera"></i></button>
                                        <button wire:click="viewAsset({{ $asset->id }})" class="btn btn-xs btn-outline-info" title="Ficha Técnica"><i class="fas fa-eye"></i></button>
                                        <button wire:click="edit({{ $asset->id }})" class="btn btn-xs btn-outline-warning" title="Editar"><i class="fas fa-pencil-alt"></i></button>
                                        <button wire:click="openConfirmDeleteAsset({{ $asset->id }})" class="btn btn-xs btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-0">
                                    @include('partials.empty-state', [
                                        'icon' => 'fa-box-open',
                                        'message' => 'No hay activos que coincidan con los filtros. Pruebe a ampliar la búsqueda o registrar un nuevo activo.',
                                        'actionLabel' => 'Nuevo Activo',
                                        'actionWire' => 'create',
                                    ])
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- FOOTER PAGINACIÓN --}}
            @if ($assets->hasPages())
                <div class="card-footer py-2">
                    {{ $assets->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ======================================================= --}}
    {{-- MODAL 1: FICHA TÉCNICA (KARDEX) --}}
    {{-- ======================================================= --}}
    @if ($viewMode && $selectedAsset)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-info py-2 text-white">
                        <h5 class="modal-title font-weight-bold text-sm"><i class="fas fa-id-card mr-2"></i> Ficha
                            Técnica</h5>
                        <button wire:click="closeView" class="close text-white p-2"><span>&times;</span></button>
                    </div>
                    <div class="modal-body bg-light p-3">
                        <div class="row">
                            {{-- COLUMNA IZQUIERDA: RESUMEN --}}
                            <div class="col-md-4 text-center border-right">
                                <div class="bg-white p-3 rounded shadow-sm mb-3 h-100">
                                    <div class="mb-3">
                                        <i class="fas fa-laptop fa-4x text-secondary opacity-50"></i>
                                    </div>
                                    <h5 class="font-weight-bold mb-1 text-primary">
                                        {{ $selectedAsset->internal_tag ?? 'S/N' }}</h5>
                                    <span
                                        class="badge badge-{{ $selectedAsset->status->badge_class }} px-3 py-1 mb-3">{{ $selectedAsset->status->name }}</span>

                                    <div class="text-left text-sm border-top pt-3">
                                        <p class="mb-1"><strong><i class="fas fa-building mr-1"></i>
                                                Empresa:</strong><br> {{ $selectedAsset->company->name ?? 'N/A' }}</p>
                                        <p class="mb-1"><strong><i class="fas fa-map-marker-alt mr-1"></i>
                                                Sede:</strong><br> {{ $selectedAsset->sede->name ?? 'N/A' }}</p>
                                        <p class="mb-0"><strong><i class="fas fa-thumbtack mr-1"></i>
                                                Ubicación:</strong><br> {{ $selectedAsset->ubicacion->name ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- COLUMNA DERECHA: TABS --}}
                            <div class="col-md-8">
                                <div class="card shadow-none border h-100 mb-0">
                                    <div class="card-header p-0 border-bottom-0">
                                        <ul class="nav nav-tabs" role="tablist">
                                            <li class="nav-item"><a class="nav-link active py-2 text-sm"
                                                    href="#specs" data-toggle="tab">Detalles</a></li>
                                            <li class="nav-item"><a class="nav-link py-2 text-sm" href="#historial"
                                                    data-toggle="tab">Historial</a></li>
                                        </ul>
                                    </div>
                                    <div class="card-body p-3 overflow-auto" style="height: 300px;">
                                        <div class="tab-content">
                                            {{-- TAB 1: SPECS --}}
                                            <div class="tab-pane active" id="specs">
                                                <table class="table table-sm table-borderless text-sm">
                                                    <tbody>
                                                        <tr>
                                                            <th class="w-25">Equipo:</th>
                                                            <td>{{ $selectedAsset->name }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Serie:</th>
                                                            <td class="text-monospace">{{ $selectedAsset->serial }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Costo:</th>
                                                            <td>${{ number_format($selectedAsset->cost, 2) }}</td>
                                                        </tr>
                                                        @if ($selectedAsset->specs)
                                                            <tr>
                                                                <td colspan="2">
                                                                    <hr class="my-1">
                                                                </td>
                                                            </tr>
                                                            @foreach ($selectedAsset->specs as $k => $v)
                                                                @if ($v)
                                                                    <tr>
                                                                        <th class="text-capitalize">
                                                                            {{ str_replace('_', ' ', $k) }}:</th>
                                                                        <td>{{ $v }}</td>
                                                                    </tr>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>

                                            {{-- TAB 2: HISTORIAL --}}
                                            <div class="tab-pane" id="historial">
                                                <div class="timeline timeline-inverse">
                                                    @forelse($selectedAsset->movements as $mov)
                                                        <div>
                                                            <i class="fas {{ $mov->type == 'CHECKOUT' ? 'fa-user bg-primary' : ($mov->type == 'CHECKIN' ? 'fa-undo bg-success' : ($mov->type == 'BAJA' ? 'fa-trash bg-danger' : ($mov->type == 'TRASLADO' ? 'fa-truck-moving bg-info' : ($mov->type == 'DESPIECE' ? 'fa-tools bg-warning' : 'fa-check bg-secondary')))) }} text-white"
                                                                style="font-size: 10px; width: 25px; height: 25px; line-height: 25px;"></i>
                                                            <div class="timeline-item shadow-sm border">
                                                                <span class="time text-xs"><i
                                                                        class="far fa-clock"></i>
                                                                    {{ $mov->date->format('d/m/Y') }}</span>
                                                                <h3 class="timeline-header text-xs p-2">
                                                                    <strong class="text-primary">{{ $mov->type }}</strong>
                                                                    · {{ optional($mov->admin)->name ?? 'Sistema' }}
                                                                    @if($mov->type === 'CHECKOUT' && $mov->user)
                                                                        <span class="text-muted">→</span> {{ $mov->user->name }} {{ $mov->user->ap_paterno }}
                                                                        @if($mov->previousUser)
                                                                            <span class="text-muted small">(antes: {{ $mov->previousUser->name }} {{ $mov->previousUser->ap_paterno }})</span>
                                                                        @endif
                                                                    @endif
                                                                    @if($mov->type === 'CHECKIN' && $mov->previousUser)
                                                                        <span class="text-muted">Entregaba:</span> {{ $mov->previousUser->name }} {{ $mov->previousUser->ap_paterno }}
                                                                    @endif
                                                                </h3>
                                                                @if ($mov->reason)
                                                                    <div class="timeline-body p-2 text-xs border-top"><strong>Motivo:</strong> {{ $mov->reason }}</div>
                                                                @endif
                                                                @if ($mov->batch_uuid)
                                                                    <div class="timeline-body p-2 text-xs border-top bg-light"><small class="text-muted">Lote:</small> <code class="small">{{ $mov->batch_uuid }}</code></div>
                                                                @endif
                                                                @if ($mov->notes)
                                                                    <div
                                                                        class="timeline-body p-2 text-xs border-top bg-light">
                                                                        {{ $mov->notes }}</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <div class="text-center text-muted mt-4"><small>Sin movimientos
                                                                registrados.</small></div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer py-2 bg-white">
                        <button wire:click="closeView" class="btn btn-secondary btn-sm">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ======================================================= --}}
    {{-- MODAL 2: FORMULARIO CRUD (CREAR / EDITAR) --}}
    {{-- ======================================================= --}}
    @if ($showFormModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.6); overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header py-2 {{ $isEditMode ? 'bg-warning' : 'bg-success' }} text-white">
                        <h5 class="modal-title font-weight-bold text-sm">
                            <i class="fas {{ $isEditMode ? 'fa-edit' : 'fa-plus' }} mr-2"></i>
                            {{ $isEditMode ? 'Editar Activo' : 'Nuevo Activo' }}
                        </h5>
                        <button wire:click="closeFormModal" class="close text-white p-2"><span>&times;</span></button>
                    </div>
                    <div class="modal-body p-4">
                        <form wire:submit="store">
                            <div class="row">
                                {{-- SECCIÓN 1: DATOS BÁSICOS --}}
                                <div class="col-12 mb-2">
                                    <h6 class="text-primary border-bottom pb-1 text-sm font-weight-bold">Datos Básicos
                                    </h6>
                                </div>

                                <div class="col-md-6 form-group mb-2">
                                    <label class="text-xs font-weight-bold">Nombre Equipo *</label>
                                    <input wire:model="name" type="text"
                                        class="form-control form-control-sm" placeholder="Ej: Laptop Dell Latitude">
                                    @error('name')
                                        <span class="text-danger text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="text-xs font-weight-bold">Tag interno del activo</label>
                                    <input wire:model="internal_tag" type="text" class="form-control form-control-sm" placeholder="Ej: 0492">
                                    @include('partials.form-help', ['text' => 'Folio interno único del activo (no es la etiqueta de sede). Si lo dejas vacío al crear, se genera automáticamente.'])
                                    @error('internal_tag')
                                        <span class="text-danger text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="text-xs font-weight-bold">Serie</label>
                                    <input wire:model="serial" type="text"
                                        class="form-control form-control-sm" placeholder="Número de serie del fabricante">
                                    @include('partials.form-help', ['text' => 'Opcional. Número de serie que trae el equipo.'])
                                    @error('serial')
                                        <span class="text-danger text-xs">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-4 form-group mb-2">
                                    <label class="text-xs font-weight-bold">Categoría *</label>
                                    <select wire:model="category_id" class="form-control form-control-sm">
                                        <option value="">Seleccione...</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                    @include('partials.form-help', ['text' => 'Tipo de equipo: Laptop, Monitor, etc.'])
                                    @error('category_id')
                                        <span class="text-danger text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-4 form-group mb-2">
                                    <label class="text-xs font-weight-bold">Estatus *</label>
                                    <select wire:model="status_id" class="form-control form-control-sm">
                                        <option value="">Seleccione...</option>
                                        @foreach ($statuses as $st)
                                            <option value="{{ $st->id }}">{{ $st->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 form-group mb-2">
                                    <label class="text-xs font-weight-bold">Costo</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                        <input wire:model="cost" type="number" step="0.01"
                                            class="form-control" placeholder="0.00">
                                    </div>
                                    @include('partials.form-help', ['text' => 'Valor en moneda local. Solo referencia.'])
                                </div>

                                <div class="col-md-4 form-group mb-2">
                                    <label class="text-xs font-weight-bold">Condición</label>
                                    <select wire:model="condition" class="form-control form-control-sm">
                                        <option value="NUEVO">Nuevo</option>
                                        <option value="BUENO">Bueno</option>
                                        <option value="REGULAR">Regular</option>
                                        <option value="MALO">Malo</option>
                                        <option value="PARA_PIEZAS">Para piezas</option>
                                    </select>
                                </div>
                                <div class="col-md-4 form-group mb-2">
                                    <label class="text-xs font-weight-bold">Fecha de compra</label>
                                    <input wire:model="purchase_date" type="date" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-4 form-group mb-2">
                                    <label class="text-xs font-weight-bold">Garantía hasta</label>
                                    <input wire:model="warranty_expiry" type="date" class="form-control form-control-sm">
                                </div>

                                {{-- SECCIÓN 2: UBICACIÓN --}}
                                <div class="col-12 mt-3 mb-2">
                                    <h6 class="text-primary border-bottom pb-1 text-sm font-weight-bold">Ubicación y
                                        Estructura</h6>
                                </div>

                                <div class="col-md-4 form-group mb-2">
                                    <label class="text-xs font-weight-bold">Empresa *</label>
                                    <select wire:model.live="company_id" class="form-control form-control-sm">
                                        <option value="">Seleccione...</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('company_id')
                                        <span class="text-danger text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-4 form-group mb-2">
                                    <label class="text-xs font-weight-bold">Sede (Sucursal)</label>
                                    <select wire:model.live="sede_id" class="form-control form-control-sm">
                                        <option value="">Seleccione...</option>
                                        @foreach ($sedes as $sede)
                                            <option value="{{ $sede->id }}">{{ $sede->sede }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 form-group mb-2">
                                    <label class="text-xs font-weight-bold">Ubicación Física</label>
                                    <select wire:model.live="ubicacion_id" class="form-control form-control-sm"
                                        {{ empty($ubicaciones) ? 'disabled' : '' }}>
                                        <option value="">Seleccione...</option>
                                        @foreach ($ubicaciones as $ubi)
                                            <option value="{{ $ubi->id }}">{{ $ubi->ubicacion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 form-group mb-2">
                                    <label class="text-xs font-weight-bold">Etiqueta de sede (rápida)</label>
                                    <select wire:model.live="label_id" class="form-control form-control-sm">
                                        <option value="">Seleccione etiqueta...</option>
                                        @foreach ($activeLabels as $label)
                                            <option value="{{ $label->id }}">{{ $label->name }} @if($label->sede) - {{ $label->sede->sede }} @endif</option>
                                        @endforeach
                                    </select>
                                    @if($selected_sede_label_name)
                                        <small class="text-success d-block mt-1"><i class="fas fa-check-circle mr-1"></i>Activa: {{ $selected_sede_label_name }}</small>
                                    @endif
                                    @include('partials.form-help', ['text' => 'Puedes elegir la etiqueta directamente; al cambiarla se ajusta la sede automáticamente.'])
                                    @error('label_id')
                                        <span class="text-danger text-xs">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- SECCIÓN 3: SPECS --}}
                                <div class="col-12 mt-3 mb-2">
                                    <h6 class="text-primary border-bottom pb-1 text-sm font-weight-bold">
                                        Especificaciones Adicionales</h6>
                                </div>

                                <div class="col-md-3 form-group mb-2"><label
                                        class="text-xs text-muted">Marca</label><input wire:model="brand"
                                        type="text" class="form-control form-control-sm"></div>
                                <div class="col-md-3 form-group mb-2"><label
                                        class="text-xs text-muted">Modelo</label><input wire:model="model"
                                        type="text" class="form-control form-control-sm"></div>
                                <div class="col-md-3 form-group mb-2"><label class="text-xs text-muted">IP
                                        Address</label><input wire:model="ip" type="text"
                                        class="form-control form-control-sm"></div>
                                <div class="col-md-3 form-group mb-2"><label class="text-xs text-muted">MAC
                                        Address</label><input wire:model="mac" type="text"
                                        class="form-control form-control-sm"></div>

                                <div class="col-12 form-group mb-0">
                                    <label class="text-xs text-muted">Notas Internas</label>
                                    <textarea wire:model="notes" class="form-control form-control-sm" rows="2"></textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer py-2">
                        <button wire:click="closeFormModal" type="button"
                            class="btn btn-secondary btn-sm">Cancelar</button>
                        <button wire:click="store"
                            class="btn {{ $isEditMode ? 'btn-warning' : 'btn-success' }} btn-sm font-weight-bold"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="store"><i class="fas fa-save mr-1"></i> Guardar</span>
                            <span wire:loading wire:target="store"><i class="fas fa-spinner fa-spin mr-1"></i> Guardando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ======================================================= --}}
    {{-- MODAL 3: ASIGNACIÓN (CHECK-OUT) --}}
    {{-- ======================================================= --}}
    @if ($showAssignModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.6);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header py-2 bg-primary text-white">
                        <h5 class="modal-title font-weight-bold text-sm"><i class="fas fa-handshake mr-2"></i> Asignar
                            Activo</h5>
                        <button wire:click="closeAssignment"
                            class="close text-white p-2"><span>&times;</span></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-info py-2 px-3 text-sm mb-3">
                            <i class="fas fa-info-circle mr-1"></i> El activo cambiará automáticamente a estatus
                            <b>ASIGNADO</b>.
                        </div>

                        <form wire:submit="storeAssignment">
                            <div class="form-group mb-3">
                                <label class="text-sm font-weight-bold">Usuario / Empleado *</label>
                                <select wire:model.live="assign_user_id" class="form-control form-control-sm">
                                    <option value="">Seleccione Usuario...</option>
                                    @foreach ($users_list as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} {{ $user->ap_paterno ?? '' }}</option>
                                    @endforeach
                                </select>
                                @error('assign_user_id') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="text-sm font-weight-bold">Fecha de entrega *</label>
                                <input type="date" wire:model="assign_date" class="form-control form-control-sm">
                                @error('assign_date') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="text-sm font-weight-bold">Motivo de asignación *</label>
                                <input type="text" wire:model="assign_reason" class="form-control form-control-sm" placeholder="Ej: Alta de personal, reemplazo de equipo dañado...">
                                @error('assign_reason') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="text-sm font-weight-bold">Detalles / Notas de entrega</label>
                                <textarea wire:model="assign_notes" class="form-control form-control-sm" rows="2" placeholder="Ej: Se entrega con cargador original, funda y mouse..."></textarea>
                                @error('assign_notes') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group mb-0">
                                <label class="text-sm font-weight-bold">Responsiva firmada (PDF o foto)</label>
                                <input type="file" wire:model="assign_responsiva" class="form-control-file form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Máx. 10 MB — PDF, JPG o PNG</small>
                                @error('assign_responsiva') <span class="text-danger text-xs d-block">{{ $message }}</span> @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer py-2">
                        <button wire:click="closeAssignment" type="button" class="btn btn-secondary btn-sm">Cancelar</button>
                        <button wire:click="storeAssignment" class="btn btn-primary btn-sm font-weight-bold" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="storeAssignment"><i class="fas fa-paper-plane mr-1"></i> Confirmar Asignación</span>
                            <span wire:loading wire:target="storeAssignment"><i class="fas fa-spinner fa-spin mr-1"></i> Procesando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ASIGNACIÓN MASIVA (mismo responsable para todos los seleccionados) --}}
    @if ($showBulkAssignModal)
        <div class="modal fade show d-block modal-livewire-confirm" style="z-index: 1050;" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header py-2 bg-info text-white">
                        <h5 class="modal-title font-weight-bold text-sm">
                            <i class="fas fa-users mr-2"></i> Asignación masiva ({{ count($selected) }} activos)
                        </h5>
                        <button type="button" class="close text-white p-2" wire:click="closeBulkAssignment" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body p-3">
                        <div class="alert alert-light border small mb-3 py-2 mb-2">
                            Solo se asignarán activos cuyo <strong>estatus actual permita asignación</strong>. El resto se omitirá y verás un resumen al final.
                        </div>
                        <div class="form-group mb-2">
                            <label class="text-sm font-weight-bold">Responsable (usuario) *</label>
                            <select wire:model.live="bulk_assign_user_id" class="form-control form-control-sm">
                                <option value="">Seleccione...</option>
                                @foreach ($users_list as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} {{ $user->ap_paterno ?? '' }}</option>
                                @endforeach
                            </select>
                            @error('bulk_assign_user_id') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-2">
                            <label class="text-sm font-weight-bold">Fecha de entrega *</label>
                            <input type="date" wire:model="bulk_assign_date" class="form-control form-control-sm">
                            @error('bulk_assign_date') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-2">
                            <label class="text-sm font-weight-bold">Motivo de asignación *</label>
                            <input type="text" wire:model="bulk_assign_reason" class="form-control form-control-sm" placeholder="Ej: Dotación de equipos para nuevo proyecto">
                            @error('bulk_assign_reason') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-2">
                            <label class="text-sm font-weight-bold">Detalles / Notas</label>
                            <textarea wire:model="bulk_assign_notes" class="form-control form-control-sm" rows="2" placeholder="Ej. Entrega masiva a área de ventas"></textarea>
                            @error('bulk_assign_notes') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label class="text-sm font-weight-bold">Responsiva firmada (PDF o foto)</label>
                            <input type="file" wire:model="bulk_assign_responsiva" class="form-control-file form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Máx. 10 MB — PDF, JPG o PNG</small>
                            @error('bulk_assign_responsiva') <span class="text-danger text-xs d-block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" wire:click="closeBulkAssignment" class="btn btn-secondary btn-sm">Cancelar</button>
                        <button type="button" wire:click="storeBulkAssignment" class="btn btn-info btn-sm font-weight-bold" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="storeBulkAssignment"><i class="fas fa-check mr-1"></i> Asignar {{ count($selected) }} activo(s)</span>
                            <span wire:loading wire:target="storeBulkAssignment"><i class="fas fa-spinner fa-spin mr-1"></i> Procesando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- DEVOLUCIÓN / DESASIGNACIÓN MASIVA --}}
    @if ($showBulkCheckinModal)
        <div class="modal fade show d-block modal-livewire-confirm" style="z-index: 1050;" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header py-2 bg-success text-white">
                        <h5 class="modal-title font-weight-bold text-sm">
                            <i class="fas fa-undo-alt mr-2"></i> Desasignar / devolver en lote ({{ count($selected) }})
                        </h5>
                        <button type="button" class="close text-white p-2" wire:click="closeBulkCheckin" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body p-3">
                        <p class="text-muted small mb-2">Se quitará el responsable de cada activo que <strong>tenga asignación</strong>, se pondrá estatus disponible y se registrará un movimiento de devolución por activo.</p>
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Fecha de devolución *</label>
                            <input type="date" wire:model="bulk_checkin_date" class="form-control form-control-sm">
                            @error('bulk_checkin_date') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Motivo de devolución *</label>
                            <input type="text" wire:model="bulk_checkin_reason" class="form-control form-control-sm" placeholder="Ej: Cierre de proyecto, baja de personal...">
                            @error('bulk_checkin_reason') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Detalles / Notas</label>
                            <textarea wire:model="bulk_checkin_notes" class="form-control form-control-sm" rows="2" placeholder="Ej. Devolución masiva por cierre de proyecto"></textarea>
                            @error('bulk_checkin_notes') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label class="text-xs font-weight-bold">Responsiva firmada (PDF o foto)</label>
                            <input type="file" wire:model="bulk_checkin_responsiva" class="form-control-file form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Máx. 10 MB — PDF, JPG o PNG</small>
                            @error('bulk_checkin_responsiva') <span class="text-danger text-xs d-block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" wire:click="closeBulkCheckin" class="btn btn-secondary btn-sm">Cancelar</button>
                        <button type="button" wire:click="storeBulkCheckin" class="btn btn-success btn-sm font-weight-bold" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="storeBulkCheckin"><i class="fas fa-check mr-1"></i> Confirmar desasignación</span>
                            <span wire:loading wire:target="storeBulkCheckin"><i class="fas fa-spinner fa-spin mr-1"></i> Procesando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ======================================================= --}}
    {{-- MODAL 4: EDICIÓN MASIVA --}}
    {{-- ======================================================= --}}
    @if ($showBulkModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.6);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header py-2 bg-dark text-white">
                        <h5 class="modal-title font-weight-bold text-sm">
                            <i class="fas fa-layer-group mr-2"></i> Edición Masiva ({{ count($selected) }} items)
                        </h5>
                        <button wire:click="closeBulkModal" class="close text-white p-2"><span>&times;</span></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-muted text-center text-xs mb-3 bg-light p-2 rounded">
                            <i class="fas fa-exclamation-circle mr-1"></i> Solo selecciona los campos que deseas
                            cambiar. Los campos vacíos ("No cambiar") mantendrán su valor actual.
                        </p>

                        <form wire:submit="saveBulk">
                            <div class="row">
                                <div class="col-md-6 form-group mb-2">
                                    <label class="text-xs font-weight-bold">Categoría</label>
                                    <select wire:model.live="bulk_category_id" class="form-control form-control-sm">
                                        <option value="">-- No cambiar --</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 form-group mb-2">
                                    <label class="text-xs font-weight-bold">Estatus</label>
                                    <select wire:model.live="bulk_status_id" class="form-control form-control-sm">
                                        <option value="">-- No cambiar --</option>
                                        @foreach ($statuses as $st)
                                            <option value="{{ $st->id }}">{{ $st->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <hr class="my-2">
                                </div>

                                <div class="col-md-12 form-group mb-2">
                                    <label class="text-xs font-weight-bold">Empresa / Cliente</label>
                                    <select wire:model.live="bulk_company_id" class="form-control form-control-sm">
                                        <option value="">-- No cambiar --</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 form-group mb-2">
                                    <label class="text-xs font-weight-bold">Sede</label>
                                    <select wire:model.live="bulk_sede_id" class="form-control form-control-sm">
                                        <option value="">-- No cambiar --</option>
                                        @foreach ($sedes as $sede)
                                            <option value="{{ $sede->id }}">{{ $sede->sede }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 form-group mb-2">
                                    <label class="text-xs font-weight-bold">Ubicación Física</label>
                                    <select wire:model.live="bulk_ubicacion_id" class="form-control form-control-sm"
                                        {{ empty($bulk_ubicaciones) ? 'disabled' : '' }}>
                                        <option value="">-- No cambiar --</option>
                                        @foreach ($bulk_ubicaciones as $ubi)
                                            <option value="{{ $ubi->id }}">{{ $ubi->ubicacion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            @error('bulk_error')
                                <div class="text-danger small text-center mt-2 font-weight-bold">{{ $message }}</div>
                            @enderror

                            <div class="text-right pt-3 border-top mt-3">
                                <button wire:click="closeBulkModal" type="button"
                                    class="btn btn-secondary btn-sm">Cancelar</button>
                                <button type="button" wire:click="saveBulk" class="btn btn-primary btn-sm shadow-sm"
                                    wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="saveBulk"><i class="fas fa-check-double mr-1"></i> Aplicar Cambios</span>
                                    <span wire:loading wire:target="saveBulk"><i class="fas fa-spinner fa-spin mr-1"></i> Aplicando...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ======================================================= --}}
    {{-- MODAL 5: EVIDENCIA FOTOGRÁFICA (FOTOS) --}}
    {{-- ======================================================= --}}
    @if ($showPhotoModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.6);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-dark py-2">
                        <h6 class="modal-title text-white"><i class="fas fa-camera mr-2"></i> Evidencia Fotográfica
                        </h6>
                        <button wire:click="closePhotoModal" class="close text-white">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Subir nuevas fotos:</label>
                            <div class="custom-file">
                                <input type="file" wire:model.live="evidence_photos" class="custom-file-input"
                                    id="evidenceInput" multiple accept="image/*">
                                <label class="custom-file-label" for="evidenceInput">Elegir archivos...</label>
                            </div>
                            <div wire:loading wire:target="evidence_photos" class="text-info small mt-1">
                                <i class="fas fa-spinner fa-spin"></i> Cargando imágenes...
                            </div>
                        </div>

                        @if (count($evidence_photos) > 0)
                            <button wire:click="savePhotos" class="btn btn-success btn-sm btn-block mb-3">
                                <i class="fas fa-upload"></i> Guardar Fotos Seleccionadas
                            </button>
                        @endif

                        <hr>
                        <h6 class="font-weight-bold">Fotos Guardadas:</h6>
                        <div class="row mt-2">
                            @forelse($stored_photos as $img)
                                <div class="col-4 mb-3 text-center position-relative">
                                    <div class="img-thumbnail p-1">
                                        <img src="{{ asset('storage/' . $img->path) }}"
                                            style="height: 80px; width: 100%; object-fit: cover;">
                                    </div>
                                    <button wire:click="deletePhoto({{ $img->id }})"
                                        class="btn btn-danger btn-xs position-absolute"
                                        style="top:-5px; right:5px; border-radius: 50%;" title="Eliminar foto">
                                        &times;
                                    </button>
                                </div>
                            @empty
                                <div class="col-12 text-center text-muted small py-3">No hay evidencia cargada.</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="modal-footer py-1">
                        <button wire:click="closePhotoModal" class="btn btn-secondary btn-sm">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL DEVOLUCIÓN (CHECK-IN) --}}
    @if ($showCheckinModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.6); z-index: 1050;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header py-2 bg-success text-white">
                        <h5 class="modal-title font-weight-bold text-sm"><i class="fas fa-undo mr-2"></i> Devolver equipo</h5>
                        <button type="button" class="close text-white" wire:click="closeCheckinModal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-2">El equipo quedará disponible y se registrará un movimiento de devolución.</p>
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Fecha de devolución *</label>
                            <input type="date" wire:model="checkin_date" class="form-control form-control-sm">
                            @error('checkin_date') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Motivo de devolución *</label>
                            <input type="text" wire:model="checkin_reason" class="form-control form-control-sm" placeholder="Ej: Baja de personal, cambio de equipo...">
                            @error('checkin_reason') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Detalles / Notas</label>
                            <textarea wire:model="checkin_notes" class="form-control form-control-sm" rows="2" placeholder="Ej: Equipo en buen estado, incluye cargador"></textarea>
                            @error('checkin_notes') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label class="text-xs font-weight-bold">Responsiva firmada (PDF o foto)</label>
                            <input type="file" wire:model="checkin_responsiva" class="form-control-file form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Máx. 10 MB — PDF, JPG o PNG</small>
                            @error('checkin_responsiva') <span class="text-danger text-xs d-block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="closeCheckinModal">Cancelar</button>
                        <button type="button" class="btn btn-success btn-sm font-weight-bold" wire:click="storeCheckin" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="storeCheckin">Confirmar devolución</span>
                            <span wire:loading wire:target="storeCheckin"><i class="fas fa-spinner fa-spin mr-1"></i> Procesando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL BAJA --}}
    @if ($showBajaModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.6); z-index: 1050;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header py-2 bg-danger text-white">
                        <h5 class="modal-title font-weight-bold text-sm"><i class="fas fa-archive mr-2"></i> Dar de baja</h5>
                        <button type="button" class="close text-white" wire:click="closeBajaModal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-2">Seleccione el estatus de baja (ej. BAJA, DESMANTELADO) y opcionalmente indique el motivo.</p>
                        <div class="form-group">
                            <label class="text-xs font-weight-bold">Estatus de baja *</label>
                            <select wire:model.live="baja_status_id" class="form-control form-control-sm">
                                <option value="">Seleccione...</option>
                                @foreach ($statuses as $st)
                                    <option value="{{ $st->id }}">{{ $st->name }}</option>
                                @endforeach
                            </select>
                            @error('baja_status_id') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label class="text-xs font-weight-bold">Motivo / notas</label>
                            <textarea wire:model.live="baja_notes" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="closeBajaModal">Cancelar</button>
                        <button type="button" class="btn btn-danger btn-sm" wire:click="storeBaja">Confirmar baja</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL TRASLADO --}}
    @if ($showTrasladoModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.6); z-index: 1050;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header py-2 bg-info text-white">
                        <h5 class="modal-title font-weight-bold text-sm"><i class="fas fa-truck-moving mr-2"></i> Traslado de activo</h5>
                        <button type="button" class="close text-white" wire:click="closeTrasladoModal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-light border small py-2 mb-3">
                            <i class="fas fa-map-marker-alt text-secondary mr-1"></i>
                            <strong>Ubicación actual:</strong> {{ $traslado_origen_sede }} / {{ $traslado_origen_ubicacion }}
                        </div>
                        <p class="text-xs text-muted font-weight-bold mb-2"><i class="fas fa-arrow-right mr-1"></i> Destino</p>
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Sede destino</label>
                            <select wire:model.live="traslado_sede_id" class="form-control form-control-sm">
                                <option value="">Sin sede</option>
                                @foreach ($sedes as $sede)
                                    <option value="{{ $sede->id }}">{{ $sede->sede }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label class="text-xs font-weight-bold">Ubicación destino</label>
                            <select wire:model.live="traslado_ubicacion_id" class="form-control form-control-sm" {{ empty($traslado_ubicaciones) ? 'disabled' : '' }}>
                                <option value="">Seleccione...</option>
                                @foreach ($traslado_ubicaciones as $ubi)
                                    <option value="{{ $ubi->id }}">{{ $ubi->ubicacion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <hr class="my-2">
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Fecha del traslado *</label>
                            <input type="date" wire:model="traslado_date" class="form-control form-control-sm">
                            @error('traslado_date') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Motivo del traslado *</label>
                            <input type="text" wire:model="traslado_reason" class="form-control form-control-sm" placeholder="Ej: Reubicación por cambio de área, cierre de sucursal...">
                            @error('traslado_reason') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label class="text-xs font-weight-bold">Notas adicionales</label>
                            <textarea wire:model="traslado_notes" class="form-control form-control-sm" rows="2" placeholder="Ej: Se traslada junto con periféricos"></textarea>
                            @error('traslado_notes') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="closeTrasladoModal">Cancelar</button>
                        <button type="button" class="btn btn-info btn-sm font-weight-bold" wire:click="storeTraslado" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="storeTraslado"><i class="fas fa-check mr-1"></i> Confirmar traslado</span>
                            <span wire:loading wire:target="storeTraslado"><i class="fas fa-spinner fa-spin mr-1"></i> Procesando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Despiece / Canibalización --}}
    @if($showDespieceModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">

                    <div class="modal-header bg-warning py-2">
                        <h5 class="modal-title">
                            <i class="fas fa-tools mr-2"></i>
                            Despiece de activo:
                            @if($despieceAsset)
                                <strong>{{ $despieceAsset->internal_tag }}</strong>
                                – {{ $despieceAsset->name }}
                            @endif
                        </h5>
                        <button type="button" class="close" wire:click="$set('showDespieceModal', false)">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Los componentes seleccionados pasarán a <strong>STOCK</strong> y quedarán disponibles para reasignación. Esta acción queda registrada en el historial del activo y de cada componente.
                        </div>

                        @if(!empty($despieceComponents))
                            <table class="table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width:40px">
                                            <input type="checkbox"
                                                {{ count($despieceComponents) > 0 && count($selectedForExtraction) === count($despieceComponents) ? 'checked' : '' }}
                                                wire:click="toggleAllDespieceComponents">
                                        </th>
                                        <th>Nombre</th>
                                        <th>Marca / Modelo</th>
                                        <th>Serie</th>
                                        <th>Estado</th>
                                        <th>Origen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($despieceComponents as $comp)
                                    <tr>
                                        <td>
                                            <input type="checkbox"
                                                   class="comp-check"
                                                   wire:model.live="selectedForExtraction"
                                                   value="{{ $comp['id'] }}">
                                        </td>
                                        <td>{{ $comp['name'] ?? '–' }}</td>
                                        <td>{{ $comp['marca'] ?? '–' }} {{ $comp['modelo'] ?? '' }}</td>
                                        <td>{{ $comp['serie'] ?? '–' }}</td>
                                        <td>
                                            <span class="badge badge-secondary">{{ $comp['status'] ?? '–' }}</span>
                                        </td>
                                        <td>
                                            @if(!empty($comp['origin_asset_id']))
                                                <span class="badge badge-info" title="Extraído de otro activo">
                                                    <i class="fas fa-recycle mr-1"></i> Canibalizado
                                                </span>
                                            @else
                                                <span class="text-muted">–</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            @include('partials.empty-state', [
                                'icon'    => 'fa-puzzle-piece',
                                'message' => 'Este activo no tiene componentes disponibles para despiece.',
                            ])
                        @endif

                        <div class="form-group mt-3">
                            <label>Notas del despiece</label>
                            <textarea wire:model="despieceNotes"
                                      class="form-control"
                                      rows="2"
                                      placeholder="Motivo del despiece, observaciones..."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="$set('showDespieceModal', false)">
                            Cancelar
                        </button>
                        <button type="button"
                                class="btn btn-danger btn-sm"
                                wire:click="confirmDespiece"
                                @if(empty($selectedForExtraction)) disabled @endif>
                            <i class="fas fa-tools mr-1"></i>
                            Extraer ({{ count($selectedForExtraction) }}) componente(s)
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

    {{-- MODAL IMPORTAR EXCEL --}}
    @if($showImportModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5); overflow-y: auto;" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white py-2">
                        <h5 class="modal-title mb-0"><i class="fas fa-file-upload mr-2"></i>Importar Excel a Inventario V2</h5>
                        <button type="button" class="close text-white" wire:click="closeImportModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body p-3">
                        <div class="alert alert-info py-2 mb-3">
                            <strong>Modo:</strong> crear/actualizar por serie. Si la serie existe, actualiza; si no existe, crea activo nuevo.
                        </div>
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                            <div class="btn-group btn-group-sm mb-1" role="group" aria-label="Navegación importación">
                                <a href="#import-config" class="btn btn-outline-secondary">Configuración</a>
                                <a href="#import-results" class="btn btn-outline-secondary">Resultados</a>
                            </div>
                            <button type="button" class="btn btn-link btn-sm mb-1 p-0" data-toggle="collapse" data-target="#import-config" aria-expanded="{{ empty($import_preview) ? 'true' : 'false' }}" aria-controls="import-config">
                                {{ empty($import_preview) ? 'Ocultar configuración' : 'Mostrar configuración' }}
                            </button>
                        </div>

                        <div id="import-config" class="collapse {{ empty($import_preview) ? 'show' : '' }} border rounded p-2 mb-3" aria-live="polite">
                            <div class="row">
                                <div class="col-md-4 form-group mb-2">
                                    <label class="font-weight-bold text-sm mb-1">Archivo Excel</label>
                                    <input type="file" class="form-control-file" wire:model="import_file" accept=".xlsx,.xls,.csv">
                                    @error('import_file')
                                        <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="font-weight-bold text-sm mb-1">Categoría por defecto (altas)</label>
                                    <select wire:model="import_default_category_id" class="form-control form-control-sm">
                                        <option value="">(usar columna)</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="font-weight-bold text-sm mb-1">Empresa por defecto (altas)</label>
                                    <select wire:model="import_default_company_id" class="form-control form-control-sm">
                                        <option value="">(usar columna)</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 form-group mb-2">
                                    <label class="font-weight-bold text-sm mb-1">Estatus por defecto (altas)</label>
                                    <select wire:model="import_default_status_id" class="form-control form-control-sm">
                                        <option value="">(usar columna)</option>
                                        @foreach ($statuses as $st)
                                            <option value="{{ $st->id }}">{{ $st->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12 form-group mb-0">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="importCreateMissingUsers" wire:model="import_create_missing_users">
                                        <label class="custom-control-label text-sm" for="importCreateMissingUsers">
                                            Crear usuarios faltantes en modo pendiente (rol básico, sin campaña) al importar
                                        </label>
                                    </div>
                                    <small class="text-muted d-block">Si en la columna de responsable no existe el usuario, se creará automáticamente para revisión posterior de rol/puesto/campaña.</small>
                                </div>
                            </div>
                        </div>

                        @if(!empty($import_preview))
                            <div id="import-results" class="row mb-2">
                                <div class="col-md-12">
                                    <div class="small d-flex flex-wrap">
                                        <span class="badge badge-secondary mr-1">Filas: {{ $import_summary['total'] }}</span>
                                        <span class="badge badge-success mr-1">Válidas: {{ $import_summary['valid'] }}</span>
                                        <span class="badge badge-danger mr-1">Errores: {{ $import_summary['errors'] }}</span>
                                        <span class="badge badge-warning text-dark mr-1">Advertencias: {{ $import_summary['warnings'] ?? 0 }}</span>
                                        <span class="badge badge-primary mr-1">Crear: {{ $import_summary['create'] }}</span>
                                        <span class="badge badge-info">Actualizar: {{ $import_summary['update'] }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive border rounded" style="max-height: 45vh; overflow: auto;">
                                <table class="table table-sm table-striped mb-0">
                                    <thead class="thead-light" style="position: sticky; top: 0; z-index: 2;">
                                        <tr>
                                            <th>Fila</th>
                                            <th>Serie</th>
                                            <th>Nombre</th>
                                            <th>Acción</th>
                                            <th>Resultado de validación</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($import_preview as $row)
                                            <tr>
                                                <td>{{ $row['row'] }}</td>
                                                <td class="text-monospace">{{ $row['serie'] }}</td>
                                                <td class="text-truncate" style="max-width: 260px;" title="{{ $row['nombre'] }}">{{ $row['nombre'] }}</td>
                                                <td><span class="badge badge-{{ $row['accion'] === 'CREAR' ? 'primary' : 'info' }}">{{ $row['accion'] }}</span></td>
                                                <td class="{{ ($row['tipo'] ?? '') === 'ok' ? 'text-success' : ((($row['tipo'] ?? '') === 'warn') ? 'text-warning' : 'text-danger') }} text-truncate" style="max-width: 520px;" title="{{ $row['estado'] }}">{{ $row['estado'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer py-2 bg-white border-top" style="position: sticky; bottom: 0; z-index: 3;">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="closeImportModal">Cerrar</button>
                        <button type="button" class="btn btn-primary btn-sm" wire:click="previewImport" wire:loading.attr="disabled" wire:target="previewImport,import_file">
                            <span wire:loading.remove wire:target="previewImport,import_file"><i class="fas fa-search mr-1"></i>Previsualizar</span>
                            <span wire:loading wire:target="previewImport,import_file"><i class="fas fa-circle-notch fa-spin mr-1"></i>Procesando...</span>
                        </button>
                        <button type="button" class="btn btn-success btn-sm" wire:click="executeImport" wire:loading.attr="disabled" wire:target="executeImport" @if(($import_summary['valid'] ?? 0) < 1) disabled @endif>
                            <span wire:loading.remove wire:target="executeImport"><i class="fas fa-check mr-1"></i>Importar válidas</span>
                            <span wire:loading wire:target="executeImport"><i class="fas fa-circle-notch fa-spin mr-1"></i>Importando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de confirmación (eliminar activo / eliminar seleccionados) --}}
    @include('partials.confirm-modal')

</div>