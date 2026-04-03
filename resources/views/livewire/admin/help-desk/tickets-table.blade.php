<div class="helpdesk-tickets-lw-root">
<div class="row helpdesk-tickets-livewire">
    @if(!$this->anyTicketModalOpen())
        {{-- Poll solo sin modal abierto: evita perder foco / re-dibujar el formulario del ticket. --}}
        <span class="helpdesk-lw-poll-hold sr-only" wire:poll.15s wire:key="helpdesk-tickets-poll" aria-hidden="true"></span>
    @endif
    <div class="col-12">
        <div class="card card-primary card-outline shadow-sm helpdesk-tickets-card">
            <div class="card-header py-2">
                <h3 class="card-title text-sm">
                    <i class="fas fa-list-alt mr-1"></i> Tickets a Realizar
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" wire:click="$refresh" title="Refrescar tabla" @disabled($this->anyTicketModalOpen())>
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>

            <div class="card-body py-2 px-3">
                <div class="mb-3">
                    <div class="input-group input-group-sm" style="max-width: 320px;">
                        <input type="search" class="form-control" placeholder="Buscar (ID, texto, solicitante...)" wire:model.live.debounce.400ms="search" autocomplete="off">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                </div>

                @if(auth()->user()->hasAnyRole('Admin|Soporte|Telecomunicaciones|Mantenimiento|Metricas') || auth()->user()->hasRole('super-admin|Admin'))
                    <div class="callout callout-info bg-light py-2 px-3 mb-3" id="filters-container">
                        <div class="row align-items-center">
                            <div class="col-auto mb-2 mb-md-0">
                                <strong><i class="fas fa-filter"></i> Filtros:</strong>
                            </div>
                            @hasanyrole('Admin|Soporte|Telecomunicaciones|Mantenimiento|Metricas')
                                <div class="col-md-4 col-sm-6 mb-2 mb-sm-0">
                                    <select class="form-control form-control-sm" wire:model.live.debounce.350ms="filtroSede">
                                        <option value="">Todas las sedes</option>
                                        @foreach ($sedes as $sede)
                                            <option value="{{ $sede->id }}">{{ $sede->sede }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endhasanyrole

                            @role('super-admin|Admin')
                                <div class="col-md-4 col-sm-6">
                                    <select class="form-control form-control-sm" wire:model.live.debounce.350ms="filtroArea">
                                        <option value="">Todas las áreas</option>
                                        @foreach ($areas as $area)
                                            <option value="{{ $area->name }}">{{ $area->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endrole
                        </div>
                    </div>
                @endif

                <div class="d-flex flex-wrap align-items-center justify-content-between mb-2 helpdesk-tickets-toolbar">
                    <div class="d-flex align-items-center mb-2 mb-sm-0">
                        <label class="mb-0 mr-2 small text-muted text-nowrap" for="helpdesk-per-page">Registros por página</label>
                        <select id="helpdesk-per-page" class="custom-select custom-select-sm helpdesk-per-page-select" wire:model.live="perPage">
                            @foreach ([10, 15, 25, 50] as $n)
                                <option value="{{ $n }}">{{ $n }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @can('validacion_ticket')
                    <div class="alert alert-light border helpdesk-status-legend py-2 px-3 mb-3 small text-muted">
                        <strong class="text-dark"><i class="fas fa-palette mr-1"></i>Estatus:</strong>
                        texto = valor en base de datos.
                        <span class="d-block d-md-inline d-md-ml-2 mt-1 mt-md-0">
                            <strong class="text-dark">Colores:</strong>
                            en filas con <span class="text-dark font-weight-bold">indicador de tiempo</span> (punto), el color del estatus coincide con ese SLA
                            (&lt;15&nbsp;min verde, &lt;30&nbsp;min ámbar, &lt;24&nbsp;h rojo, después gris oscuro).
                            En el resto, el color sigue el estatus
                            (<span class="badge badge-danger">Pendiente</span>,
                            <span class="badge badge-warning text-dark">Seguimiento</span> / <span class="badge badge-warning text-dark">En proceso</span>,
                            <span class="badge badge-success">Abierto</span>,
                            <span class="badge badge-secondary">Cerrado</span> u otros).
                        </span>
                    </div>
                @endcan

                <div class="table-responsive helpdesk-tickets-table-wrap border rounded shadow-sm">
                    <table class="table table-bordered table-striped table-hover table-sm mb-0 w-100 helpdesk-tickets-table">
                        <thead class="thead-dark text-nowrap">
                            <tr>
                                <th scope="col" class="text-center align-middle helpdesk-th-sla" title="Antigüedad desde la creación del ticket (referencia visual). Los colores coinciden con el badge de estatus cuando aplica.">
                                    <i class="fas fa-traffic-light" aria-hidden="true"></i><span class="sr-only"> SLA</span>
                                </th>
                                <th scope="col" class="text-center align-middle helpdesk-th-id">ID</th>
                                <th scope="col" class="align-middle helpdesk-th-sol">Solicitante</th>
                                <th scope="col" class="align-middle helpdesk-th-falla">Servicio</th>
                                <th scope="col" class="text-center align-middle helpdesk-th-estatus">Estatus</th>
                                <th scope="col" class="align-middle helpdesk-th-fecha">Fecha</th>
                                <th scope="col" class="align-middle">Atiende</th>
                                <th scope="col" class="align-middle d-none d-xl-table-cell">Puesto</th>
                                <th scope="col" class="align-middle d-none d-lg-table-cell">Campaña</th>
                                <th scope="col" class="align-middle d-none d-xl-table-cell">Depto.</th>
                                <th scope="col" class="align-middle helpdesk-col-desc">Descripción</th>
                                <th scope="col" class="align-middle d-none d-md-table-cell helpdesk-col-text">Seguim.</th>
                                <th scope="col" class="align-middle d-none d-md-table-cell helpdesk-col-text">Observ.</th>
                                <th scope="col" class="text-center align-middle helpdesk-th-acciones">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tickets as $ticket)
                                @php
                                    $userHasActivosRole = count(array_intersect($authRoleNames, ['Admin', 'Soporte', 'General'])) > 0;
                                    $validation = (int) ($ticket->validation ?? 0);
                                    $canUpdate = auth()->user()->can('update', $ticket);
                                    $createdAt = $ticket->created_at ? \Carbon\Carbon::parse($ticket->created_at) : null;
                                    $minutesElapsed = $createdAt ? $createdAt->diffInMinutes(now()) : null;
                                    $isOwner = $ticket->user && (int) $ticket->user->id === (int) auth()->id();
                                    $activoCriticoPendienteValidacion = $ticket->failure
                                        && (int) $ticket->failure->id === 31
                                        && $validation !== 1
                                        && $userHasActivosRole;
                                    $showSlaForBadge = $ticket->user
                                        && ! $isOwner
                                        && ! $activoCriticoPendienteValidacion;
                                    $statusBadgeColor = \App\Support\Tickets\HelpdeskTicketStatusBadge::resolveColor(
                                        $ticket->status,
                                        $minutesElapsed,
                                        $showSlaForBadge
                                    );
                                    $statusBadgeClasses = \App\Support\Tickets\HelpdeskTicketStatusBadge::badgeClasses($statusBadgeColor);
                                    $slaBtnColor = \App\Support\Tickets\HelpdeskTicketStatusBadge::colorForSlaMinutes($minutesElapsed);
                                    if ($slaBtnColor === null) {
                                        $slaBtnClass = 'btn-dark';
                                        $btnTitle = 'Sin fecha';
                                    } else {
                                        $slaBtnClass = $slaBtnColor === 'dark' ? 'btn-dark' : 'btn-'.$slaBtnColor;
                                        if ($slaBtnColor === 'warning') {
                                            $slaBtnClass .= ' text-dark';
                                        }
                                        if ($minutesElapsed < 15) {
                                            $btnTitle = 'Nuevo';
                                        } elseif ($minutesElapsed < 30) {
                                            $btnTitle = 'Advertencia';
                                        } elseif ($minutesElapsed < 1440) {
                                            $btnTitle = 'Retrasado';
                                        } else {
                                            $btnTitle = 'Muy retrasado';
                                        }
                                    }
                                    $slaDurationLabel = '—';
                                    if ($minutesElapsed !== null) {
                                        if ($minutesElapsed < 60) {
                                            $slaDurationLabel = $minutesElapsed.' min';
                                        } elseif ($minutesElapsed < 1440) {
                                            $h = intdiv($minutesElapsed, 60);
                                            $m = $minutesElapsed % 60;
                                            $slaDurationLabel = $m > 0 ? $h.' h '.$m.' min' : $h.' h';
                                        } else {
                                            $d = intdiv($minutesElapsed, 1440);
                                            $slaDurationLabel = $d.' día'.($d !== 1 ? 's' : '');
                                        }
                                    }
                                @endphp
                                <tr wire:key="ticket-row-{{ $ticket->id }}">
                                    <td class="text-center align-middle helpdesk-td-sla">
                                        <div class="helpdesk-sla-cell">
                                            @can('validacion_ticket')
                                                @if($activoCriticoPendienteValidacion)
                                                    <span class="text-muted small helpdesk-sla-na" title="Activo crítico pendiente: use el botón de acciones">—</span>
                                                @elseif($ticket->user && (int) $ticket->user->id !== (int) auth()->id())
                                                    <span class="btn btn-sm {{ $slaBtnClass }} helpdesk-sla-indicator border-0 shadow-sm helpdesk-sla-pill helpdesk-sla-dot" role="img" data-toggle="tooltip" data-placement="top" title="Tiempo desde creación: {{ $slaDurationLabel }} · {{ $btnTitle }}" aria-label="Antigüedad del ticket: {{ $slaDurationLabel }}, {{ $btnTitle }}"></span>
                                                @elseif($ticket->user)
                                                    <span class="badge badge-secondary helpdesk-sla-propio-badge" title="Eres el solicitante de este ticket">Propio</span>
                                                @else
                                                    <span class="text-muted small helpdesk-sla-na" title="Sin usuario solicitante">—</span>
                                                @endif
                                            @else
                                                <span class="text-muted small helpdesk-sla-na" title="Sin permiso de validación de tickets">—</span>
                                            @endcan
                                        </div>
                                    </td>
                                    <td class="text-center font-weight-bold helpdesk-td-id text-nowrap">{{ $ticket->id }}</td>
                                    <td class="text-break">
                                        @if($ticket->user)
                                            {{ trim(implode(' ', array_filter([$ticket->user->name, $ticket->user->ap_paterno, $ticket->user->ap_materno]))) }}
                                        @else
                                            Sin Usuario
                                        @endif
                                    </td>
                                    <td class="text-break">{{ $ticket->failure->name ?? 'Sin falla' }}</td>
                                    <td class="text-center align-middle helpdesk-td-estatus">
                                        @php $stLabel = trim((string) ($ticket->status ?? '')); @endphp
                                        <span class="{{ $statusBadgeClasses }} helpdesk-estatus-badge-only" title="{{ $stLabel !== '' ? $stLabel : 'Sin estatus' }}">{{ $stLabel !== '' ? $stLabel : '—' }}</span>
                                    </td>
                                    <td class="small text-muted helpdesk-td-fecha">
                                        @if($ticket->created_at)
                                            <time datetime="{{ $ticket->created_at->toIso8601String() }}" title="{{ $ticket->created_at->format('Y-m-d H:i:s') }}">{{ $ticket->created_at->format('Y-m-d H:i') }}</time>
                                        @endif
                                    </td>
                                    <td class="text-break">
                                        @if($ticket->responsable)
                                            {{ trim(implode(' ', array_filter([$ticket->responsable->name, $ticket->responsable->ap_paterno, $ticket->responsable->ap_materno]))) }}
                                        @else
                                            <span class="text-muted font-italic text-sm">Sin responsable</span>
                                        @endif
                                    </td>
                                    <td class="text-break d-none d-xl-table-cell">{{ $ticket->user?->position?->name ?? '—' }}</td>
                                    <td class="text-break d-none d-lg-table-cell">{{ $ticket->user?->campaign?->name ?? 'Sin campaña' }}</td>
                                    <td class="text-break d-none d-xl-table-cell">{{ $ticket->user?->department?->name ?? '—' }}</td>
                                    <td class="helpdesk-col-desc">
                                        @php $desc = $ticket->description ?? ''; @endphp
                                        @if(strlen($desc) > 50)
                                            <span title="{{ e($desc) }}">{{ \Illuminate\Support\Str::limit($desc, 50) }}</span>
                                        @else
                                            {{ $desc }}
                                        @endif
                                    </td>
                                    <td class="d-none d-md-table-cell helpdesk-col-text">
                                        @php $sol = $ticket->solution ?? ''; @endphp
                                        @if(strlen($sol) > 40)
                                            <span class="d-inline-block text-break" title="{{ e($sol) }}">{{ \Illuminate\Support\Str::limit($sol, 40) }}</span>
                                        @else
                                            {{ $sol !== '' ? $sol : '—' }}
                                        @endif
                                    </td>
                                    <td class="d-none d-md-table-cell helpdesk-col-text">
                                        @php $obs = $ticket->observations ?? ''; @endphp
                                        @if(strlen($obs) > 40)
                                            <span class="d-inline-block text-break" title="{{ e($obs) }}">{{ \Illuminate\Support\Str::limit($obs, 40) }}</span>
                                        @else
                                            {{ $obs !== '' ? $obs : '—' }}
                                        @endif
                                    </td>
                                    <td class="text-center align-middle helpdesk-td-acciones">
                                        <div class="d-flex flex-wrap justify-content-center align-items-center helpdesk-acciones-inner">
                                            @can('validacion_ticket')
                                                @if($ticket->failure && (int) $ticket->failure->id === 31 && $validation !== 1 && $userHasActivosRole)
                                                    <button type="button" class="btn btn-info btn-xs m-1" data-placement="top" title="Dar seguimiento al activo crítico" wire:click="openActivoCriticoModal({{ $ticket->id }})">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                    </button>
                                                @elseif($canUpdate)
                                                    <button type="button" wire:click="openSeguimientoModal({{ $ticket->id }})" class="btn btn-info btn-sm helpdesk-btn-seguimiento m-1" data-toggle="tooltip" data-placement="left" title="Registrar seguimiento, cambiar estatus o cerrar el ticket #{{ $ticket->id }}" aria-label="Dar seguimiento al ticket {{ $ticket->id }}">
                                                        <i class="fas fa-edit fa-fw" aria-hidden="true"></i><span class="d-none d-lg-inline helpdesk-btn-seguimiento-text">Seguimiento</span>
                                                    </button>
                                                @endif
                                            @endcan
                                            @can('view', $ticket)
                                                <button type="button" class="btn btn-info btn-xs m-1" title="Ver historial" wire:click="openHistorialModal({{ $ticket->id }})"><i class="fas fa-file-alt"></i></button>
                                                <button type="button" class="btn btn-secondary btn-xs m-1" title="Notas del ticket (internas y visibles al solicitante)" wire:click="openNotasModal({{ $ticket->id }})"><i class="fas fa-sticky-note"></i></button>
                                            @endcan
                                            @can('escalate', $ticket)
                                                <button type="button" class="btn btn-warning btn-xs m-1" title="Escalar a otra área de servicio" wire:click="openEscalarModal({{ $ticket->id }})"><i class="fas fa-random"></i></button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14" class="text-center text-muted py-4">No hay tickets que coincidan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="helpdesk-tickets-pagination pt-3 mt-2 border-top">
                    @if ($tickets->hasPages())
                        {{ $tickets->links() }}
                    @elseif ($tickets->total() > 0)
                        <p class="small text-muted mb-0">
                            Mostrando {{ $tickets->total() }} {{ $tickets->total() === 1 ? 'resultado' : 'resultados' }}.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($showHistorialModal)
        <div class="modal-backdrop fade show helpdesk-lw-historial-backdrop"></div>
        <div
            class="modal fade show d-block helpdesk-lw-historial-root"
            tabindex="-1"
            role="dialog"
            aria-modal="true"
            aria-labelledby="helpdeskHistorialLabel"
            wire:click.self="closeHistorialModal"
            wire:keydown.escape="closeHistorialModal"
            wire:key="helpdesk-historial-{{ $historialServiceId }}"
        >
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content shadow">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title mb-0" id="helpdeskHistorialLabel">
                            <i class="fas fa-history mr-2"></i> Historial del ticket
                            @if($historialServiceId)
                                <span class="badge badge-light text-dark ml-2">#{{ $historialServiceId }}</span>
                            @endif
                        </h5>
                        <button type="button" class="close text-white" wire:click="closeHistorialModal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pt-2">
                        <p class="text-muted small mb-2">
                            <i class="fas fa-stream mr-1"></i> Movimientos registrados (solo lectura).
                        </p>
                        <div class="table-responsive rounded border">
                            <table class="table table-hover table-sm table-striped mb-0 w-100">
                                <thead class="thead-dark text-nowrap">
                                    <tr>
                                        <th scope="col">Realiza</th>
                                        <th scope="col">Observación</th>
                                        <th scope="col">Solución</th>
                                        <th scope="col" class="helpdesk-historial-th-date">Creado</th>
                                        <th scope="col" class="helpdesk-historial-th-date">Solucionado</th>
                                        <th scope="col">Satisfacción</th>
                                        <th scope="col" class="helpdesk-historial-th-date">Fecha cliente</th>
                                        <th scope="col">Estatus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($historialRows as $row)
                                        <tr class="{{ ($row['event_type'] ?? null) === \App\Support\Tickets\HistoricalServiceEventType::ESCALATION ? 'table-warning' : '' }}">
                                            <td class="text-break">{{ $row['nombre_r'] }}</td>
                                            <td class="text-break">
                                                @if(($row['event_type'] ?? null) === \App\Support\Tickets\HistoricalServiceEventType::ESCALATION)
                                                    <div class="mb-2 p-2 border-left border-warning rounded bg-white shadow-sm">
                                                        <span class="badge badge-warning text-dark"><i class="fas fa-random mr-1"></i> Escalado entre áreas</span>
                                                        <div class="small mt-2 text-dark"><strong>De:</strong> {{ $row['escalation_from'] }} <span class="text-muted">→</span> <strong>A:</strong> {{ $row['escalation_to'] }}</div>
                                                        @if(($row['escalation_reason'] ?? '') !== '')
                                                            <div class="small mt-2"><strong>Motivo:</strong> {{ $row['escalation_reason'] }}</div>
                                                        @endif
                                                    </div>
                                                    <details class="small text-muted">
                                                        <summary class="cursor-pointer">Ver registro técnico completo</summary>
                                                        <div class="mt-2 text-break text-body">{{ $row['observations'] }}</div>
                                                    </details>
                                                @else
                                                    {{ $row['observations'] }}
                                                @endif
                                            </td>
                                            <td class="text-break">{{ $row['solution'] }}</td>
                                            <td class="small helpdesk-historial-td-date">{{ $row['fecha'] }}</td>
                                            <td class="small helpdesk-historial-td-date">{{ $row['fecha_fin'] }}</td>
                                            <td class="text-break">{{ $row['comentario_cliente'] }}</td>
                                            <td class="small helpdesk-historial-td-date">{{ $row['fecha_relanzar'] }}</td>
                                            <td class="helpdesk-historial-td-status">
                                                @php $st = $row['status']; @endphp
                                                <span class="badge badge-{{ $st === 'Cerrado' ? 'success' : 'primary' }}">{{ $st }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">Sin registros en el historial.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top d-flex flex-wrap align-items-center justify-content-end">
                        <span class="text-muted small mr-auto d-none d-md-inline"><i class="fas fa-mouse-pointer mr-1"></i> Desplázate horizontalmente en pantallas pequeñas.</span>
                        <button type="button" class="btn btn-default" wire:click="closeHistorialModal">
                            <i class="fas fa-times mr-1"></i> Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showEscalarModal)
        <div class="modal-backdrop fade show helpdesk-lw-escalar-backdrop"></div>
        <div
            class="modal fade show d-block helpdesk-lw-escalar-root"
            tabindex="-1"
            role="dialog"
            aria-modal="true"
            aria-labelledby="helpdeskEscalarLabel"
            wire:click.self="closeEscalarModal"
            wire:keydown.escape="closeEscalarModal"
            wire:key="helpdesk-escalar-{{ $escalarServiceId }}"
        >
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content shadow">
                    <form wire:submit.prevent="saveEscalacion">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title mb-0 text-dark" id="helpdeskEscalarLabel">
                                <i class="fas fa-random mr-2"></i> Escalar ticket
                                @if($escalarServiceId)
                                    <span class="badge badge-dark ml-2">#{{ $escalarServiceId }}</span>
                                @endif
                            </h5>
                            <button type="button" class="close" wire:click="closeEscalarModal" aria-label="Cerrar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small mb-3">
                                <i class="fas fa-info-circle mr-1"></i> El ticket pasará al área y tipo de falla que elijas. Si cambia el área, se quitará al responsable actual para que el nuevo equipo asigne.
                            </p>
                            <div class="callout bg-light mb-3 helpdesk-modal-callout">
                                <div class="row">
                                    <div class="col-md-6 mb-2 mb-md-0">
                                        <div class="text-muted text-uppercase small font-weight-bold mb-1">Área / servicio actual</div>
                                        <div class="small text-dark"><strong>{{ $escalarCurrentAreaLabel }}</strong> — <span class="text-break">{{ $escalarCurrentFailureLabel }}</span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="helpdesk-escalar-area" class="font-weight-bold"><i class="fas fa-sitemap mr-1 text-muted"></i>Área destino <span class="text-danger">*</span></label>
                                <select id="helpdesk-escalar-area" class="custom-select @error('escalarAreaId') is-invalid @enderror" wire:model.live="escalarAreaId">
                                    <option value="">Seleccione el área responsable…</option>
                                    @foreach ($areas as $area)
                                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                                    @endforeach
                                </select>
                                @error('escalarAreaId')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="helpdesk-escalar-failure" class="font-weight-bold"><i class="fas fa-tools mr-1 text-muted"></i>Tipo de falla (destino) <span class="text-danger">*</span></label>
                                <select id="helpdesk-escalar-failure" class="custom-select @error('escalarNewFailureId') is-invalid @enderror" wire:model.lazy="escalarNewFailureId"@if(!$escalarAreaId) disabled @endif>
                                    <option value="">{{ $escalarAreaId ? 'Seleccione el tipo de falla…' : 'Primero elija un área…' }}</option>
                                    @foreach ($escalarFailures as $f)
                                        <option value="{{ $f->id }}">{{ $f->name }}</option>
                                    @endforeach
                                </select>
                                @error('escalarNewFailureId')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                @if($escalarAreaId && $escalarFailures->isEmpty())
                                    <p class="small text-muted mb-0 mt-2"><i class="fas fa-exclamation-triangle mr-1"></i>No hay otros tipos de falla en esta área (o solo existe el actual). Elige otra área o revisa el catálogo de fallas.</p>
                                @endif
                            </div>
                            <div class="form-group mb-0">
                                <label for="helpdesk-escalar-reason" class="font-weight-bold"><i class="fas fa-comment-dots mr-1 text-muted"></i>Motivo del escalado <span class="text-danger">*</span></label>
                                <textarea id="helpdesk-escalar-reason" class="form-control @error('escalarReason') is-invalid @enderror" wire:model.lazy="escalarReason" rows="4" placeholder="Ej.: Requiere cableado / se necesita acceso a infraestructura…" maxlength="2000"></textarea>
                                @error('escalarReason')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Máximo 2000 caracteres. Quedará registrado en el historial del ticket.</small>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between bg-light border-top">
                            <button type="button" class="btn btn-default" wire:click="closeEscalarModal">
                                <i class="fas fa-times mr-1"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-warning" wire:loading.attr="disabled" wire:target="saveEscalacion" @if(!$escalarAreaId || $escalarFailures->isEmpty()) disabled @endif>
                                <span wire:loading.remove wire:target="saveEscalacion"><i class="fas fa-check mr-1"></i> Escalar ticket</span>
                                <span wire:loading wire:target="saveEscalacion"><i class="fas fa-circle-notch fa-spin mr-1"></i> Guardando…</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($showObservacionesModal)
        <div class="modal-backdrop fade show helpdesk-lw-obs-backdrop"></div>
        <div
            class="modal fade show d-block helpdesk-lw-obs-root"
            tabindex="-1"
            role="dialog"
            aria-modal="true"
            aria-labelledby="helpdeskObsLabel"
            wire:click.self="closeObservacionesModal"
            wire:keydown.escape="closeObservacionesModal"
            wire:key="helpdesk-obs-{{ $observacionesServiceId }}"
        >
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content shadow">
                    <form wire:submit.prevent="saveObservaciones">
                        <div class="modal-header bg-secondary text-white">
                            <h5 class="modal-title mb-0" id="helpdeskObsLabel">
                                <i class="fas fa-edit mr-2"></i> Agregar observaciones
                                @if($observacionesServiceId)
                                    <span class="badge badge-light text-dark ml-2">#{{ $observacionesServiceId }}</span>
                                @endif
                            </h5>
                            <button type="button" class="close text-white" wire:click="closeObservacionesModal" aria-label="Cerrar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="callout bg-light mb-3 helpdesk-modal-callout">
                                <div class="text-muted text-uppercase small font-weight-bold mb-1">Servicio</div>
                                <div class="font-weight-bold text-dark text-break">{{ $observacionesFailureName }}</div>
                            </div>
                            <div class="form-group mb-0">
                                <label for="helpdesk-obs-textarea" class="font-weight-bold"><i class="fas fa-comment-alt mr-1 text-muted"></i>Observaciones</label>
                                <textarea
                                    id="helpdesk-obs-textarea"
                                    class="form-control @error('observacionesText') is-invalid @enderror"
                                    wire:model.lazy="observacionesText"
                                    rows="5"
                                    placeholder="Edita o amplía las observaciones del ticket..."
                                    required
                                ></textarea>
                                @error('observacionesText')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between bg-light border-top">
                            <button type="button" class="btn btn-default" wire:click="closeObservacionesModal">
                                <i class="fas fa-times mr-1"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="saveObservaciones">
                                <span wire:loading.remove wire:target="saveObservaciones"><i class="fas fa-save mr-1"></i> Guardar</span>
                                <span wire:loading wire:target="saveObservaciones"><i class="fas fa-circle-notch fa-spin mr-1"></i> Guardando…</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($showNotasModal)
        <div class="modal-backdrop fade show helpdesk-lw-notas-backdrop"></div>
        <div
            class="modal fade show d-block helpdesk-lw-notas-root"
            tabindex="-1"
            role="dialog"
            aria-modal="true"
            aria-labelledby="helpdeskNotasLabel"
            wire:click.self="closeNotasModal"
            wire:keydown.escape="closeNotasModal"
            wire:key="helpdesk-notas-{{ $notasServiceId }}"
        >
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content shadow">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title mb-0" id="helpdeskNotasLabel">
                            <i class="fas fa-sticky-note mr-2"></i> Notas del ticket
                            @if($notasServiceId)
                                <span class="badge badge-light text-dark ml-2">#{{ $notasServiceId }}</span>
                            @endif
                        </h5>
                        <button type="button" class="close text-white" wire:click="closeNotasModal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pt-2">
                        <div class="callout bg-light mb-3 helpdesk-modal-callout">
                            <div class="text-muted text-uppercase small font-weight-bold mb-1">Servicio</div>
                            <div class="font-weight-bold text-dark text-break">{{ $notasFailureName }}</div>
                        </div>
                        <p class="text-muted small mb-2">
                            @if($notasModalService && auth()->user()->can('update', $notasModalService))
                                Las notas <strong>internas</strong> solo las ve el personal con permiso de actualizar el ticket. Las <strong>visibles al solicitante</strong> también las verá quien abrió el ticket.
                            @else
                                Solo ves las notas marcadas como visibles para el solicitante.
                            @endif
                        </p>
                        <div class="border rounded mb-3" style="max-height: 220px; overflow-y: auto;">
                            <ul class="list-group list-group-flush mb-0">
                                @forelse ($notasRows as $row)
                                    <li class="list-group-item py-2">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-1">
                                            <span class="small font-weight-bold text-dark">{{ $row['author_label'] }}</span>
                                            <span class="small text-muted">{{ $row['created_at'] }}</span>
                                        </div>
                                        <div class="mb-1">
                                            @if($row['visibility'] === \App\Models\ServiceTicketNote::VIS_INTERNAL)
                                                <span class="badge badge-secondary">Interna</span>
                                            @else
                                                <span class="badge badge-info">Visible solicitante</span>
                                            @endif
                                            @if(!empty($row['notify_support']))
                                                <span class="badge badge-warning text-dark"><i class="fas fa-bell mr-1"></i>Alerta soporte</span>
                                            @endif
                                        </div>
                                        <div class="small text-break">{!! nl2br(e($row['body'])) !!}</div>
                                    </li>
                                @empty
                                    <li class="list-group-item text-muted small py-3 text-center">Aún no hay notas en este ticket.</li>
                                @endforelse
                            </ul>
                        </div>

                        @if($notasModalService && auth()->user()->can('update', $notasModalService))
                            <form wire:submit.prevent="saveNotaStaff" class="border-top pt-3">
                                <h6 class="font-weight-bold mb-2"><i class="fas fa-user-shield mr-1 text-muted"></i> Agregar nota (equipo)</h6>
                                <div class="form-group">
                                    <label for="helpdesk-nota-staff-vis" class="font-weight-bold small">Visibilidad</label>
                                    <select id="helpdesk-nota-staff-vis" class="custom-select form-control-sm @error('notaStaffVisibility') is-invalid @enderror" wire:model="notaStaffVisibility">
                                        <option value="{{ \App\Models\ServiceTicketNote::VIS_INTERNAL }}">Solo equipo (interna)</option>
                                        <option value="{{ \App\Models\ServiceTicketNote::VIS_REQUESTER_VISIBLE }}">Visible para el solicitante</option>
                                    </select>
                                    @error('notaStaffVisibility')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group mb-2">
                                    <label for="helpdesk-nota-staff-body" class="font-weight-bold small">Texto</label>
                                    <textarea id="helpdesk-nota-staff-body" class="form-control form-control-sm @error('notaStaffBody') is-invalid @enderror" wire:model.lazy="notaStaffBody" rows="3" placeholder="Escribe la nota…"></textarea>
                                    @error('notaStaffBody')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm" wire:loading.attr="disabled" wire:target="saveNotaStaff">
                                    <span wire:loading.remove wire:target="saveNotaStaff"><i class="fas fa-save mr-1"></i> Guardar nota</span>
                                    <span wire:loading wire:target="saveNotaStaff"><i class="fas fa-circle-notch fa-spin mr-1"></i>…</span>
                                </button>
                            </form>
                        @elseif($notasModalService && \App\Support\Tickets\TicketRequesterNote::userMayAddNote(auth()->user(), $notasModalService))
                            <form wire:submit.prevent="saveNotaSolicitante" class="border-top pt-3">
                                <h6 class="font-weight-bold mb-2"><i class="fas fa-comment mr-1 text-muted"></i> Tu mensaje sobre el ticket</h6>
                                <div class="form-group mb-2">
                                    <textarea id="helpdesk-nota-sol-body" class="form-control form-control-sm @error('notaSolicitanteBody') is-invalid @enderror" wire:model.lazy="notaSolicitanteBody" rows="4" placeholder="Información adicional, aclaraciones…"></textarea>
                                    @error('notaSolicitanteBody')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="helpdesk-nota-sol-notify" wire:model="notaSolicitanteNotify">
                                    <label class="custom-control-label small" for="helpdesk-nota-sol-notify">Enviar alerta al personal con permiso de notificación (mesa de ayuda: Soporte, Infraestructura, Telecomunicaciones, Mantenimiento)</label>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm" wire:loading.attr="disabled" wire:target="saveNotaSolicitante">
                                    <span wire:loading.remove wire:target="saveNotaSolicitante"><i class="fas fa-paper-plane mr-1"></i> Enviar</span>
                                    <span wire:loading wire:target="saveNotaSolicitante"><i class="fas fa-circle-notch fa-spin mr-1"></i>…</span>
                                </button>
                            </form>
                        @endif
                    </div>
                    <div class="modal-footer bg-light border-top">
                        <button type="button" class="btn btn-default" wire:click="closeNotasModal">
                            <i class="fas fa-times mr-1"></i> Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showSeguimientoModal)
        <div class="modal-backdrop fade show helpdesk-lw-seg-backdrop"></div>
        <div
            class="modal fade show d-block helpdesk-lw-seg-root"
            tabindex="-1"
            role="dialog"
            aria-modal="true"
            aria-labelledby="helpdeskSeguimientoLabel"
            wire:click.self="closeSeguimientoModal"
            wire:keydown.escape="closeSeguimientoModal"
            wire:key="helpdesk-seg-{{ $seguimientoServiceId }}"
        >
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content shadow">
                    <form wire:submit.prevent="saveSeguimiento">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title mb-0" id="helpdeskSeguimientoLabel">
                                <i class="fas fa-tasks mr-2"></i> Seguimiento de ticket
                                @if($seguimientoServiceId)
                                    <span class="badge badge-light text-dark ml-2">#{{ $seguimientoServiceId }}</span>
                                @endif
                            </h5>
                            <button type="button" class="close text-white" wire:click="closeSeguimientoModal" aria-label="Cerrar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="callout callout-info bg-light border-left mb-3 py-3 px-3 helpdesk-modal-callout">
                                <div class="row">
                                    <div class="col-sm-6 mb-2 mb-sm-0">
                                        <div class="text-muted text-uppercase small font-weight-bold mb-1">Tipo de servicio</div>
                                        <div class="font-weight-bold text-dark text-break">{{ $seguimientoFailureName }}</div>
                                    </div>
                                </div>
                                <hr class="my-3 border-secondary">
                                <div class="text-muted text-uppercase small font-weight-bold mb-1">
                                    <i class="fas fa-align-left mr-1"></i> Descripción del solicitante
                                </div>
                                <div class="small text-break rounded border p-3 helpdesk-modal-description">{!! nl2br(e($seguimientoDescription)) !!}</div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="helpdesk-seg-status" class="font-weight-bold"><i class="fas fa-flag mr-1 text-muted"></i>Estatus</label>
                                    <select
                                        id="helpdesk-seg-status"
                                        class="custom-select @error('seguimientoStatus') is-invalid @enderror"
                                        wire:model.lazy="seguimientoStatus"
                                        required
                                    >
                                        <option value="">Selecciona un estatus…</option>
                                        @if($seguimientoPhaseComplete)
                                            <option value="{{ \App\Support\Tickets\TicketStatus::FINALIZADO }}">{{ \App\Support\Tickets\TicketStatus::label(\App\Support\Tickets\TicketStatus::FINALIZADO) }}</option>
                                        @else
                                            <option value="{{ \App\Support\Tickets\TicketStatus::SEGUIMIENTO }}">{{ \App\Support\Tickets\TicketStatus::label(\App\Support\Tickets\TicketStatus::SEGUIMIENTO) }}</option>
                                            <option value="{{ \App\Support\Tickets\TicketStatus::TICKET_ERRONEO }}">{{ \App\Support\Tickets\TicketStatus::label(\App\Support\Tickets\TicketStatus::TICKET_ERRONEO) }}</option>
                                        @endif
                                    </select>
                                    @error('seguimientoStatus')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="helpdesk-seg-obs" class="font-weight-bold"><i class="fas fa-comment-alt mr-1 text-muted"></i>Observaciones</label>
                                    <textarea
                                        id="helpdesk-seg-obs"
                                        class="form-control @error('seguimientoObservations') is-invalid @enderror"
                                        wire:model.lazy="seguimientoObservations"
                                        rows="3"
                                        placeholder="Describe el seguimiento o acuerdos..."
                                        required
                                    ></textarea>
                                    @error('seguimientoObservations')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-12 @if(! $seguimientoPhaseComplete) d-none @endif" wire:key="helpdesk-seg-sol-wrap-{{ $seguimientoPhaseComplete ? '1' : '0' }}">
                                    <label for="helpdesk-seg-sol" class="font-weight-bold"><i class="fas fa-check-circle mr-1 text-muted"></i>Solución o acuerdo</label>
                                    <textarea
                                        id="helpdesk-seg-sol"
                                        class="form-control @error('seguimientoSolution') is-invalid @enderror"
                                        wire:model.lazy="seguimientoSolution"
                                        rows="3"
                                        placeholder="Solución definitiva o acuerdo con el usuario"
                                        @if($seguimientoPhaseComplete) required @endif
                                    ></textarea>
                                    @error('seguimientoSolution')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @if(! $seguimientoPhaseComplete)
                                <p class="small text-muted mb-0">
                                    <i class="fas fa-info-circle mr-1"></i> Tras registrar el primer seguimiento podrás cerrar el ticket indicando la solución.
                                </p>
                            @endif
                        </div>
                        <div class="modal-footer justify-content-between bg-light border-top">
                            <button type="button" class="btn btn-default" wire:click="closeSeguimientoModal">
                                <i class="fas fa-times mr-1"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="saveSeguimiento">
                                <span wire:loading.remove wire:target="saveSeguimiento"><i class="fas fa-save mr-1"></i> Guardar cambios</span>
                                <span wire:loading wire:target="saveSeguimiento"><i class="fas fa-circle-notch fa-spin mr-1"></i> Guardando…</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($showActivoCriticoModal)
        <div class="modal-backdrop fade show helpdesk-lw-ac-backdrop"></div>
        <div
            class="modal fade show d-block helpdesk-lw-ac-root"
            tabindex="-1"
            role="dialog"
            aria-modal="true"
            aria-labelledby="helpdeskActivoCriticoLabel"
            wire:click.self="closeActivoCriticoModal"
            wire:keydown.escape="closeActivoCriticoModal"
            wire:key="helpdesk-ac-{{ $activoCriticoServiceId }}"
        >
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content shadow">
                    <form wire:submit.prevent="saveActivoCritico">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title mb-0 text-dark" id="helpdeskActivoCriticoLabel">
                                <i class="fas fa-exclamation-triangle mr-2"></i> Activo crítico — seguimiento
                                @if($activoCriticoServiceId)
                                    <span class="badge badge-dark ml-2">#{{ $activoCriticoServiceId }}</span>
                                @endif
                            </h5>
                            <button type="button" class="close" wire:click="closeActivoCriticoModal" aria-label="Cerrar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="callout callout-warning bg-light border-left mb-3 py-3 px-3 helpdesk-modal-callout">
                                <div class="d-flex flex-wrap align-items-start justify-content-between">
                                    <div class="mb-2 mb-md-0 pr-md-3">
                                        <div class="text-muted text-uppercase small font-weight-bold mb-1">Servicio</div>
                                        <div class="font-weight-bold text-dark text-break mb-0">{{ $activoCriticoFailureName }}</div>
                                    </div>
                                </div>
                                <p class="small text-muted mb-0 mt-3">
                                    <i class="fas fa-info-circle mr-1"></i> Completa la validación o el estatus según tu rol. Los cambios quedan registrados en el historial del ticket.
                                </p>
                            </div>

                            <div class="form-row">
                                @if($helpdeskIsGeneralUser)
                                    <div class="form-group col-md-12 col-lg-6">
                                        <label for="helpdesk-ac-general" class="font-weight-bold"><i class="fas fa-check-double mr-1 text-muted"></i>Aceptar el cambio del activo crítico</label>
                                        <select
                                            id="helpdesk-ac-general"
                                            class="custom-select @error('activoCriticoGeneralChoice') is-invalid @enderror"
                                            wire:model.lazy="activoCriticoGeneralChoice"
                                            required
                                        >
                                            <option value="">Selecciona una opción…</option>
                                            <option value="{{ \App\Support\Tickets\TicketStatus::SEGUIMIENTO }}">Sí</option>
                                            <option value="{{ \App\Support\Tickets\TicketStatus::FINALIZADO }}">No</option>
                                        </select>
                                        @error('activoCriticoGeneralChoice')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @else
                                    <div class="form-group col-md-12 col-lg-6">
                                        <label for="helpdesk-ac-status" class="font-weight-bold"><i class="fas fa-flag mr-1 text-muted"></i>Estatus</label>
                                        <select
                                            id="helpdesk-ac-status"
                                            class="custom-select @error('activoCriticoStaffStatus') is-invalid @enderror"
                                            wire:model.lazy="activoCriticoStaffStatus"
                                            required
                                        >
                                            <option value="">Selecciona un estatus…</option>
                                            <option value="{{ \App\Support\Tickets\TicketStatus::SEGUIMIENTO }}">{{ \App\Support\Tickets\TicketStatus::label(\App\Support\Tickets\TicketStatus::SEGUIMIENTO) }}</option>
                                            <option value="{{ \App\Support\Tickets\TicketStatus::FINALIZADO }}">{{ \App\Support\Tickets\TicketStatus::label(\App\Support\Tickets\TicketStatus::FINALIZADO) }}</option>
                                        </select>
                                        @error('activoCriticoStaffStatus')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for="helpdesk-ac-riesgos" class="font-weight-bold"><i class="fas fa-shield-alt mr-1 text-muted"></i>Observaciones y riesgos</label>
                                        <textarea
                                            id="helpdesk-ac-riesgos"
                                            class="form-control @error('activoCriticoRiesgos') is-invalid @enderror"
                                            wire:model.lazy="activoCriticoRiesgos"
                                            rows="4"
                                            placeholder="Documenta riesgos, impacto y acuerdos…"
                                            required
                                        ></textarea>
                                        @error('activoCriticoRiesgos')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between bg-light border-top">
                            <button type="button" class="btn btn-default" wire:click="closeActivoCriticoModal">
                                <i class="fas fa-times mr-1"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-warning text-dark" wire:loading.attr="disabled" wire:target="saveActivoCritico">
                                <span wire:loading.remove wire:target="saveActivoCritico"><i class="fas fa-save mr-1"></i> Guardar</span>
                                <span wire:loading wire:target="saveActivoCritico"><i class="fas fa-circle-notch fa-spin mr-1"></i> Guardando…</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .helpdesk-tickets-livewire .helpdesk-tickets-card .card-title {
        font-size: 1.05rem;
    }
    .helpdesk-tickets-livewire .helpdesk-tickets-table-wrap {
        max-height: min(58vh, 32rem);
        overflow: auto;
        -webkit-overflow-scrolling: touch;
        container-type: inline-size;
        container-name: helpdesk-tickets;
    }
    @media (min-width: 1200px) {
        .helpdesk-tickets-livewire .helpdesk-tickets-table-wrap {
            max-height: min(62vh, 38rem);
        }
    }
    .helpdesk-tickets-livewire .helpdesk-tickets-table {
        table-layout: fixed;
        font-size: 0.8125rem;
    }
    .helpdesk-tickets-livewire .helpdesk-tickets-table thead th {
        position: sticky;
        top: 0;
        z-index: 5;
        font-size: 0.74rem;
        letter-spacing: 0.02em;
        box-shadow: 0 1px 0 rgba(0, 0, 0, 0.08);
        line-height: 1.2;
    }
    .helpdesk-tickets-livewire .helpdesk-tickets-table td,
    .helpdesk-tickets-livewire .helpdesk-tickets-table th {
        vertical-align: middle !important;
        padding-top: 0.35rem;
        padding-bottom: 0.35rem;
        padding-left: 0.35rem;
        padding-right: 0.35rem;
    }
    .helpdesk-tickets-livewire .helpdesk-th-sla,
    .helpdesk-tickets-livewire .helpdesk-td-sla {
        width: 4.25rem;
        min-width: 4.25rem;
        max-width: 5.5rem;
        text-align: center !important;
        vertical-align: middle !important;
        padding-left: 0.2rem;
        padding-right: 0.2rem;
    }
    .helpdesk-tickets-livewire .helpdesk-sla-cell {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 1.75rem;
    }
    .helpdesk-tickets-livewire .helpdesk-sla-dot {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
        line-height: 1 !important;
        flex-shrink: 0;
    }
    .helpdesk-tickets-livewire .helpdesk-sla-propio-badge {
        font-size: 0.65rem;
        font-weight: 600;
        padding: 0.2em 0.35em;
        white-space: nowrap;
        line-height: 1.2;
    }
    .helpdesk-tickets-livewire .helpdesk-sla-na {
        line-height: 1;
    }
    .helpdesk-tickets-livewire .helpdesk-th-id,
    .helpdesk-tickets-livewire .helpdesk-td-id {
        width: 6rem;
        min-width: 6rem;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }
    .helpdesk-tickets-livewire .helpdesk-th-estatus,
    .helpdesk-tickets-livewire .helpdesk-td-estatus {
        width: auto;
        min-width: 4.25rem;
        max-width: 9rem;
        white-space: normal;
        overflow-wrap: break-word;
        word-break: normal;
        vertical-align: middle !important;
        text-align: center !important;
    }
    .helpdesk-tickets-livewire .helpdesk-estatus-badge-only {
        display: inline-block;
        max-width: 100%;
        white-space: normal;
        word-break: break-word;
        line-height: 1.2;
        padding: 0.28em 0.45em;
        text-align: center;
        vertical-align: middle;
        box-sizing: border-box;
    }
    .helpdesk-tickets-livewire .helpdesk-th-acciones,
    .helpdesk-tickets-livewire .helpdesk-td-acciones {
        width: 14%;
        min-width: 11.5rem;
        white-space: normal;
    }
    .helpdesk-tickets-livewire .helpdesk-acciones-inner {
        margin: -0.125rem;
    }
    /* Botón principal: área mínima ~44×44px (accesibilidad táctil), más visible */
    .helpdesk-tickets-livewire .helpdesk-btn-seguimiento {
        min-height: 2.75rem;
        min-width: 2.75rem;
        padding: 0.45rem 0.7rem;
        font-weight: 600;
        font-size: 0.875rem;
        line-height: 1.2;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.3rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
        transition: transform 0.08s ease, box-shadow 0.08s ease;
    }
    .helpdesk-tickets-livewire .helpdesk-btn-seguimiento:hover {
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }
    .helpdesk-tickets-livewire .helpdesk-btn-seguimiento:active {
        transform: scale(0.98);
    }
    .helpdesk-tickets-livewire .helpdesk-btn-seguimiento:focus {
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.45), 0 1px 3px rgba(0, 0, 0, 0.12);
    }
    .helpdesk-tickets-livewire .helpdesk-btn-seguimiento .fa-fw {
        font-size: 1.05rem;
    }
    @media (min-width: 992px) {
        .helpdesk-tickets-livewire .helpdesk-btn-seguimiento {
            min-width: auto;
            padding-left: 0.9rem;
            padding-right: 0.9rem;
        }
        .helpdesk-tickets-livewire .helpdesk-btn-seguimiento-text {
            margin-left: 0.35rem;
        }
    }
    /* Área de tabla estrecha: botón más compacto para que la grilla no “hinchar” la fila */
    @container helpdesk-tickets (max-width: 1180px) {
        .helpdesk-tickets-livewire .helpdesk-btn-seguimiento {
            min-height: 2.15rem;
            min-width: 2.15rem;
            padding: 0.3rem 0.5rem;
            font-size: 0.8125rem;
            border-radius: 0.25rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
        }
        .helpdesk-tickets-livewire .helpdesk-btn-seguimiento .fa-fw {
            font-size: 0.92rem;
        }
        .helpdesk-tickets-livewire .helpdesk-btn-seguimiento-text {
            display: none !important;
        }
        .helpdesk-tickets-livewire .helpdesk-th-acciones,
        .helpdesk-tickets-livewire .helpdesk-td-acciones {
            min-width: 8.75rem;
        }
    }
    @container helpdesk-tickets (max-width: 900px) {
        .helpdesk-tickets-livewire .helpdesk-btn-seguimiento {
            min-height: 1.9rem;
            min-width: 1.9rem;
            padding: 0.22rem 0.38rem;
            font-size: 0.76rem;
        }
        .helpdesk-tickets-livewire .helpdesk-btn-seguimiento .fa-fw {
            font-size: 0.85rem;
        }
        .helpdesk-tickets-livewire .helpdesk-btn-seguimiento:focus {
            box-shadow: 0 0 0 0.15rem rgba(23, 162, 184, 0.4), 0 1px 2px rgba(0, 0, 0, 0.08);
        }
        .helpdesk-tickets-livewire .helpdesk-th-acciones,
        .helpdesk-tickets-livewire .helpdesk-td-acciones {
            min-width: 7.75rem;
        }
    }
    @media (max-width: 1366px) {
        .helpdesk-tickets-livewire .helpdesk-btn-seguimiento {
            min-height: 2.15rem;
            min-width: 2.15rem;
            padding: 0.3rem 0.5rem;
            font-size: 0.8125rem;
            border-radius: 0.25rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
        }
        .helpdesk-tickets-livewire .helpdesk-btn-seguimiento .fa-fw {
            font-size: 0.92rem;
        }
        .helpdesk-tickets-livewire .helpdesk-btn-seguimiento-text {
            display: none !important;
        }
        .helpdesk-tickets-livewire .helpdesk-th-acciones,
        .helpdesk-tickets-livewire .helpdesk-td-acciones {
            min-width: 8.75rem;
        }
    }
    @media (max-width: 991.98px) {
        .helpdesk-tickets-livewire .helpdesk-btn-seguimiento {
            min-height: 1.9rem;
            min-width: 1.9rem;
            padding: 0.22rem 0.38rem;
            font-size: 0.76rem;
        }
        .helpdesk-tickets-livewire .helpdesk-btn-seguimiento .fa-fw {
            font-size: 0.85rem;
        }
        .helpdesk-tickets-livewire .helpdesk-th-acciones,
        .helpdesk-tickets-livewire .helpdesk-td-acciones {
            min-width: 7.75rem;
        }
    }
    .helpdesk-tickets-livewire .helpdesk-th-sol {
        width: 11%;
    }
    .helpdesk-tickets-livewire .helpdesk-th-falla {
        width: 11%;
    }
    /* Fecha: sin nowrap — con table-layout:fixed el texto forzado a una línea desborda y tapa la columna siguiente */
    .helpdesk-tickets-livewire .helpdesk-th-fecha {
        white-space: normal;
        width: 7rem;
        min-width: 6.5rem;
        max-width: 9rem;
    }
    .helpdesk-tickets-livewire .helpdesk-td-fecha {
        white-space: normal;
        overflow-wrap: break-word;
        word-break: normal;
        line-height: 1.25;
        font-variant-numeric: tabular-nums;
        width: 7rem;
        min-width: 6.5rem;
        max-width: 9rem;
    }
    .helpdesk-tickets-livewire .helpdesk-td-fecha time {
        white-space: normal;
    }
    .helpdesk-tickets-livewire .helpdesk-lw-historial-root .helpdesk-historial-th-date {
        white-space: normal;
        min-width: 5.5rem;
    }
    .helpdesk-tickets-livewire .helpdesk-lw-historial-root .helpdesk-historial-td-date {
        white-space: normal;
        min-width: 5.25rem;
        line-height: 1.25;
        font-variant-numeric: tabular-nums;
        overflow-wrap: break-word;
        word-break: normal;
    }
    .helpdesk-tickets-livewire .helpdesk-lw-historial-root .helpdesk-historial-td-status {
        white-space: normal;
        min-width: 4rem;
        max-width: 10rem;
        text-align: center;
        vertical-align: middle !important;
    }
    .helpdesk-tickets-livewire .helpdesk-col-desc,
    .helpdesk-tickets-livewire .helpdesk-col-text {
        max-width: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .helpdesk-tickets-livewire .helpdesk-col-desc:hover,
    .helpdesk-tickets-livewire .helpdesk-col-text:hover {
        white-space: normal;
        overflow: visible;
        word-break: break-word;
    }
    .helpdesk-tickets-livewire .helpdesk-sla-indicator {
        width: 1.75rem;
        height: 1.75rem;
        padding: 0;
        border-radius: 0.25rem;
    }
    .helpdesk-tickets-livewire .helpdesk-sla-pill {
        cursor: help;
        pointer-events: auto;
    }
    .helpdesk-tickets-livewire .helpdesk-per-page-select {
        width: 4.75rem;
        min-width: 4.75rem;
    }
    /* Livewire pagination usa .fw-semibold (Bootstrap 5); en AdminLTE 3 / BS4 no existe esa clase */
    .helpdesk-tickets-livewire nav .fw-semibold {
        font-weight: 600;
    }
    .helpdesk-tickets-livewire .helpdesk-lw-historial-backdrop {
        z-index: 1059;
    }
    .helpdesk-tickets-livewire .helpdesk-lw-historial-root {
        z-index: 1060;
        overflow-y: auto;
    }
    .helpdesk-tickets-livewire .helpdesk-lw-obs-backdrop {
        z-index: 1061;
    }
    .helpdesk-tickets-livewire .helpdesk-lw-obs-root {
        z-index: 1062;
        overflow-y: auto;
    }
    .helpdesk-tickets-livewire .helpdesk-lw-seg-backdrop {
        z-index: 1063;
    }
    .helpdesk-tickets-livewire .helpdesk-lw-seg-root {
        z-index: 1064;
        overflow-y: auto;
    }
    .helpdesk-tickets-livewire .helpdesk-lw-ac-backdrop {
        z-index: 1065;
    }
    .helpdesk-tickets-livewire .helpdesk-lw-ac-root {
        z-index: 1066;
        overflow-y: auto;
    }

    body.dark-mode .helpdesk-tickets-livewire .helpdesk-status-legend {
        background-color: #3f474e !important;
        border-color: #59616a !important;
        color: #e9ecef;
    }

    body.dark-mode .helpdesk-tickets-livewire .helpdesk-status-legend .text-dark {
        color: #f8f9fa !important;
    }
</style>
</div>
