<div>
    @include('partials.breadcrumb', ['items' => [
        ['text' => 'Inicio', 'url' => route('home')],
        ['text' => 'Inventario V2', 'url' => route('inventory.v2.index')],
        ['text' => 'Componentes', 'url' => route('inventory.components')],
        ['text' => 'Gestión de Componentes', 'url' => null],
    ]])
    {{-- Tema claro/oscuro: admin-theme.css en layout. Mensajes éxito/error se muestran en layout.blade.php --}}

    <div class="card card-outline card-primary">
        
        {{-- HEADER --}}
        <div class="card-header py-2">
            <div class="row align-items-center">
                {{-- TÍTULO --}}
                <div class="col-md-3">
                    <h3 class="card-title">
                        <i class="fas fa-list-alt mr-1"></i> Gestión de Componentes
                    </h3>
                </div>

                {{-- HERRAMIENTAS --}}
                <div class="col-md-9">
                    <div class="d-flex justify-content-end align-items-center flex-wrap">
                        
                        {{-- PAGINACIÓN --}}
                        <select wire:model.live="perPage" class="form-control form-control-sm mr-2 mb-1" style="width: 110px;" title="Registros por página">
                            <option value="10">10 por página</option>
                            <option value="25">25 por página</option>
                            <option value="50">50 por página</option>
                            <option value="100">100 por página</option>
                        </select>

                        {{-- BUSCADOR --}}
                        <div class="input-group input-group-sm mr-2 mb-1" style="width: 220px;">
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Buscar por nombre, serie, marca o equipo...">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                        </div>

                        {{-- FILTROS --}}
                        <div class="btn-group btn-group-sm mr-2 mb-1">
                            <button wire:click="$set('status_filter', '')" class="btn {{ $status_filter == '' ? 'btn-secondary' : 'btn-outline-secondary' }}">Todos</button>
                            <button wire:click="$set('status_filter', 'stock')" class="btn {{ $status_filter == 'stock' ? 'btn-success' : 'btn-outline-secondary' }}">Stock</button>
                            <button wire:click="$set('status_filter', 'assigned')" class="btn {{ $status_filter == 'assigned' ? 'btn-primary' : 'btn-outline-secondary' }}">Asignados</button>
                            <button wire:click="$set('status_filter', 'suspended')" class="btn {{ $status_filter == 'suspended' ? 'btn-danger' : 'btn-outline-secondary' }}">Bajas</button>
                        </div>

                        {{-- BOTÓN NUEVO --}}
                        <button wire:click="create" class="btn btn-primary btn-sm mb-1" title="Nuevo componente">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLA --}}
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped table-hover mb-0">
                    <thead class="bg-light text-uppercase">
                        <tr class="text-xs text-center">
                            <th style="width: 40px;">ID</th>
                            <th class="text-left">Componente / Marca / Modelo</th>
                            <th>Serie / Capacidad</th>
                            <th>Estado</th>
                            <th>Ubicación (Activo V2)</th>
                            <th style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse($components as $comp)
                        <tr class="{{ $comp->status === 'SUSPENDIDO' ? 'bg-suspended' : '' }}">
                            
                            {{-- ID --}}
                            <td class="text-center font-weight-bold align-middle">{{ $comp->id }}</td>

                            {{-- NOMBRE --}}
                            <td class="align-middle">
                                <span class="font-weight-bold d-block text-truncate" style="max-width: 250px;">{{ $comp->name }}</span>
                                <small class="text-muted">{{ $comp->marca }} {{ $comp->modelo }}</small>
                            </td>

                            {{-- SERIE --}}
                            <td class="align-middle text-center">
                                @if($comp->serie)<span class="d-block text-monospace text-xs">{{ $comp->serie }}</span>@endif
                                @if($comp->capacidad)<small class="text-muted">{{ $comp->capacidad }}</small>@endif
                            </td>

                            {{-- ESTADO --}}
                            <td class="text-center align-middle">
                                @if($comp->status === 'SUSPENDIDO')
                                    <span class="badge badge-danger">BAJA</span>
                                @elseif($comp->asset)
                                    <span class="badge badge-primary">ASIGNADO</span>
                                @else
                                    <span class="badge badge-success">STOCK</span>
                                @endif
                            </td>

                            {{-- UBICACIÓN --}}
                            <td class="align-middle">
                                @if($comp->asset)
                                    <i class="fas fa-desktop text-primary mr-1 text-xs"></i> 
                                    <strong>{{ $comp->asset->internal_tag }}</strong>
                                    <br>
                                    <small class="text-muted">{{ Str::limit($comp->asset->name, 20) }}</small>
                                @elseif($comp->status === 'SUSPENDIDO')
                                    <span class="text-danger text-xs text-uppercase font-weight-bold">Inhabilitado</span>
                                @else
                                    <span class="text-success text-xs font-weight-bold">En Almacén</span>
                                @endif
                            </td>

                            {{-- ACCIONES: móvil menú con texto, escritorio iconos --}}
                            <td class="text-center align-middle">
                                <div class="dropdown d-md-none">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="comp-acc-{{ $comp->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Acciones</button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="comp-acc-{{ $comp->id }}">
                                        <button type="button" wire:click="edit({{ $comp->id }})" class="dropdown-item"><i class="fas fa-pencil-alt text-warning mr-2"></i>Editar</button>
                                        @if($comp->status !== 'SUSPENDIDO')
                                            <button type="button" wire:click="openAssignModal({{ $comp->id }})" class="dropdown-item"><i class="fas fa-exchange-alt text-primary mr-2"></i>Asignar</button>
                                        @endif
                                        <button type="button" wire:click="openConfirmToggleSuspend({{ $comp->id }})" class="dropdown-item">
                                            <i class="fas {{ $comp->status === 'SUSPENDIDO' ? 'fa-recycle text-success' : 'fa-ban text-danger' }} mr-2"></i>{{ $comp->status === 'SUSPENDIDO' ? 'Reactivar' : 'Marcar baja' }}
                                        </button>
                                    </div>
                                </div>
                                <div class="d-none d-md-flex btn-group">
                                    <button wire:click="openDetailModal({{ $comp->id }})" class="btn btn-xs btn-default border" title="Ver detalle">
                                        <i class="fas fa-eye text-info"></i>
                                    </button>
                                    <button wire:click="edit({{ $comp->id }})" class="btn btn-xs btn-default border" title="Editar"><i class="fas fa-pencil-alt text-warning"></i></button>
                                    @if($comp->status !== 'SUSPENDIDO')
                                        <button wire:click="openAssignModal({{ $comp->id }})" class="btn btn-xs btn-default border" title="Asignar"><i class="fas fa-exchange-alt text-primary"></i></button>
                                    @endif
                                    <button wire:click="openConfirmToggleSuspend({{ $comp->id }})" class="btn btn-xs btn-default border" title="{{ $comp->status === 'SUSPENDIDO' ? 'Reactivar' : 'Marcar baja' }}">
                                        <i class="fas {{ $comp->status === 'SUSPENDIDO' ? 'fa-recycle text-success' : 'fa-ban text-danger' }}"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-0">
                                @include('partials.empty-state', [
                                    'icon' => 'fa-puzzle-piece',
                                    'message' => 'No hay componentes que coincidan con los filtros.',
                                    'actionLabel' => 'Nuevo Componente',
                                    'actionWire' => 'create',
                                ])
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($components->hasPages())
                <div class="card-footer py-2">
                    {{ $components->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ======================================================= --}}
    {{-- MODAL 1: CREAR / EDITAR --}}
    {{-- ======================================================= --}}
    @if($showFormModal)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header py-2 {{ $isEditMode ? 'bg-warning' : 'bg-primary' }}">
                    <h6 class="modal-title font-weight-bold text-white">{{ $isEditMode ? 'Editar' : 'Registrar' }} Componente</h6>
                    <button wire:click="closeFormModal" class="close text-white p-2">&times;</button>
                </div>
                <div class="modal-body p-3">
                    <div class="row">
                        <div class="col-12 form-group mb-2">
                            <label class="text-sm mb-1">Nombre del Componente <span class="text-danger">*</span></label>
                            <input wire:model="name" type="text" class="form-control form-control-sm" placeholder="Ej: Memoria RAM">
                            @include('partials.form-help', ['text' => 'Tipo de componente: RAM, Disco, Fuente, etc.'])
                            @error('name') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="text-sm mb-1">Marca <span class="text-danger">*</span></label>
                            <input wire:model="marca" type="text" class="form-control form-control-sm" placeholder="Ej: Kingston">
                            @error('marca') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="text-sm mb-1">Modelo</label>
                            <input wire:model="modelo" type="text" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="text-sm mb-1">Serie</label>
                            <input wire:model="serie" type="text" class="form-control form-control-sm" placeholder="S/N...">
                            @error('serie') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="text-sm mb-1">Capacidad / Specs</label>
                            <input wire:model="capacidad" type="text" class="form-control form-control-sm" placeholder="Ej: 8GB, 500 Watts...">
                            @include('partials.form-help', ['text' => 'Opcional. Ej: 8GB, 256GB SSD, 500W.'])
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label class="text-sm mb-1">Costo</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                <input wire:model="costo" type="number" step="0.01" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-12 form-group mb-0">
                            <label class="text-sm mb-1">Observaciones</label>
                            <textarea wire:model="observacion" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button wire:click="closeFormModal" class="btn btn-secondary btn-sm">Cancelar</button>
                    <button wire:click="store" class="btn {{ $isEditMode ? 'btn-warning' : 'btn-primary' }} btn-sm font-weight-bold"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="store">Guardar</span>
                        <span wire:loading wire:target="store"><i class="fas fa-spinner fa-spin mr-1"></i> Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ======================================================= --}}
    {{-- MODAL 2: ASIGNACIÓN --}}
    {{-- ======================================================= --}}
    @if($showAssignModal)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-2 bg-info">
                    <h6 class="modal-title font-weight-bold text-white">
                        <i class="fas fa-exchange-alt mr-1"></i> Asignar: {{ Str::limit($selected_component->name, 25) }}
                    </h6>
                    <button wire:click="closeAssignModal" class="close text-white p-2">&times;</button>
                </div>
                <div class="modal-body p-3">
                    <label class="text-sm">Buscar Equipo Destino:</label>
                    <div class="input-group input-group-sm mb-2">
                        <input wire:model.live.debounce.300ms="asset_search" type="text" class="form-control" placeholder="Escribe Tag (0492) o Serie...">
                        <div class="input-group-append"><span class="input-group-text"><i class="fas fa-search"></i></span></div>
                    </div>

                    {{-- LISTA DE RESULTADOS --}}
                    <div class="list-group list-group-flush border rounded overflow-auto" style="height: 200px;">
                        
                        {{-- OPCIÓN STOCK --}}
                        <button type="button" wire:click="$set('selected_asset_id', null)" 
                                class="list-group-item list-group-item-action py-2 {{ is_null($selected_asset_id) ? 'active bg-secondary border-secondary' : '' }}">
                            <small class="font-weight-bold"><i class="fas fa-box-open mr-1"></i> ENVIAR A STOCK (Libre)</small>
                            <span class="d-block text-xs opacity-75">Desvincular del equipo actual</span>
                        </button>

                        {{-- RESULTADOS --}}
                        @foreach($asset_results as $asset)
                            <button type="button" wire:click="selectAsset({{ $asset->id }})" 
                                    class="list-group-item list-group-item-action py-2 {{ $selected_asset_id == $asset->id ? 'active' : '' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-sm font-weight-bold"><i class="fas fa-desktop mr-1"></i> {{ $asset->internal_tag }}</span>
                                    @if($selected_asset_id == $asset->id) <i class="fas fa-check text-xs"></i> @endif
                                </div>
                                <small class="d-block text-truncate {{ $selected_asset_id == $asset->id ? 'text-white' : 'text-muted' }}" style="font-size: 0.75rem;">
                                    {{ $asset->name }}
                                </small>
                            </button>
                        @endforeach

                        @if(empty($asset_results) && strlen($asset_search) > 1)
                            <div class="text-center py-3 text-muted">
                                <small>No se encontraron equipos</small>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button wire:click="closeAssignModal" class="btn btn-secondary btn-sm">Cancelar</button>
                    <button wire:click="saveAssignment" class="btn btn-primary btn-sm font-weight-bold"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="saveAssignment">Confirmar</span>
                        <span wire:loading wire:target="saveAssignment"><i class="fas fa-spinner fa-spin mr-1"></i> Procesando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ======================================================= --}}
    {{-- MODAL 3: DETALLE / HISTORIAL --}}
    {{-- ======================================================= --}}
    @if($showDetailModal && $detailComponent)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header py-2 bg-secondary">
                    <h6 class="modal-title font-weight-bold text-white">
                        {{ $detailComponent->name }}
                        @if($detailComponent->status === 'SUSPENDIDO')
                            <span class="badge badge-danger ml-2">BAJA</span>
                        @elseif($detailComponent->asset)
                            <span class="badge badge-primary ml-2">ASIGNADO</span>
                        @else
                            <span class="badge badge-success ml-2">STOCK</span>
                        @endif
                    </h6>
                    <button wire:click="$set('showDetailModal', false)" class="close text-white p-2">&times;</button>
                </div>
                <div class="modal-body p-3">
                    <div class="row">
                        {{-- Columna izquierda: información del componente --}}
                        <div class="col-md-5 border-right">
                            <h6 class="text-muted text-xs mb-2">Información del componente</h6>
                            <dl class="mb-0 text-sm">
                                <dt class="text-muted">Marca / Modelo</dt>
                                <dd>{{ $detailComponent->marca }} {{ $detailComponent->modelo }}</dd>

                                <dt class="text-muted mt-2">Serie</dt>
                                <dd>{{ $detailComponent->serie ?: '-' }}</dd>

                                <dt class="text-muted mt-2">Capacidad</dt>
                                <dd>{{ $detailComponent->capacidad ?: '-' }}</dd>

                                <dt class="text-muted mt-2">Costo</dt>
                                <dd>
                                    @if($detailComponent->costo)
                                        ${{ number_format($detailComponent->costo, 2) }}
                                    @else
                                        -
                                    @endif
                                </dd>

                                <dt class="text-muted mt-2">Observaciones</dt>
                                <dd>{{ $detailComponent->observacion ?: '-' }}</dd>
                            </dl>
                        </div>

                        {{-- Columna derecha: historial de movimientos --}}
                        <div class="col-md-7">
                            <h6 class="text-muted text-xs mb-2">Historial de movimientos</h6>

                            @if(empty($componentHistory) || count($componentHistory) === 0)
                                @include('partials.empty-state', [
                                    'icon' => 'fa-history',
                                    'message' => 'Sin movimientos registrados',
                                    'actionLabel' => null,
                                    'actionWire' => null,
                                ])
                            @else
                                <ul class="timeline timeline-inverse text-sm mb-0">
                                    @foreach($componentHistory as $movement)
                                        <li class="time-label">
                                            <span class="bg-gray">
                                                {{ optional($movement->date)->format('d/m/Y H:i') }}
                                            </span>
                                        </li>

                                        <li>
                                            @php
                                                switch ($movement->type) {
                                                    case 'ASIGNAR':
                                                        $iconClass = 'fa-arrow-right text-success';
                                                        break;
                                                    case 'RETIRAR':
                                                        $iconClass = 'fa-arrow-left text-warning';
                                                        break;
                                                    case 'BAJA':
                                                        $iconClass = 'fa-times-circle text-danger';
                                                        break;
                                                    case 'EXTRACCION':
                                                        $iconClass = 'fa-tools text-info';
                                                        break;
                                                    default:
                                                        $iconClass = 'fa-circle';
                                                        break;
                                                }
                                            @endphp
                                            <i class="fas {{ $iconClass }}"></i>
                                            <div class="timeline-item">
                                                <span class="time">
                                                    <i class="far fa-user mr-1"></i>
                                                    @if($movement->admin)
                                                        {{ $movement->admin->name }}
                                                    @else
                                                        <span class="text-muted">Administrador desconocido</span>
                                                    @endif
                                                </span>

                                                <h6 class="timeline-header mb-1">
                                                    @if($movement->type === 'ASIGNAR')
                                                        Componente asignado
                                                    @elseif($movement->type === 'RETIRAR')
                                                        Componente retirado
                                                    @elseif($movement->type === 'BAJA')
                                                        Componente dado de baja
                                                    @elseif($movement->type === 'EXTRACCION')
                                                        Componente extraído (despiece)
                                                    @else
                                                        Movimiento
                                                    @endif
                                                </h6>

                                                <div class="timeline-body">
                                                    @if(in_array($movement->type, ['ASIGNAR', 'RETIRAR']) && $movement->asset)
                                                        <div class="mb-1">
                                                            <strong>Activo:</strong>
                                                            {{ $movement->asset->internal_tag }} – {{ $movement->asset->name }}
                                                        </div>
                                                    @endif
                                                    @if($movement->type === 'EXTRACCION' && $movement->originAsset)
                                                        <div class="mb-1">
                                                            <strong>Despiezado de:</strong>
                                                            {{ $movement->originAsset->internal_tag }} – {{ $movement->originAsset->name }}
                                                        </div>
                                                    @endif

                                                    @if($movement->notes)
                                                        <em class="d-block text-muted">{{ $movement->notes }}</em>
                                                    @endif
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                    <li>
                                        <i class="fas fa-clock bg-gray"></i>
                                    </li>
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button wire:click="$set('showDetailModal', false)" class="btn btn-secondary btn-sm">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal de confirmación (suspender / reactivar componente) --}}
    @include('partials.confirm-modal')

</div>