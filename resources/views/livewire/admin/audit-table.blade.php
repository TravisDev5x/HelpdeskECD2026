<div>
    @push('styles')
    <style>
        /* =========================================================
           1. ESTILOS BASE (MODO CLARO - POR DEFECTO)
           ========================================================= */
        
        /* Tabla estilo Excel */
        .table-excel {
            width: 100%;
            font-size: 0.85rem;
            border-collapse: collapse !important;
            background-color: #ffffff;
            color: #212529;
        }
        
        .table-excel th, 
        .table-excel td {
            padding: 5px 8px !important;
            vertical-align: middle !important;
            border: 1px solid #dee2e6 !important;
        }

        .table-excel thead th {
            background-color: #f4f6f9;
            border-bottom: 2px solid #ced4da !important;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: #495057;
        }

        /* Clases personalizadas de fondo */
        .custom-bg { background-color: #ffffff; color: #212529; }
        .custom-header-bg { background-color: #f8f9fa; color: #212529; }

        /* Utilidades */
        .avatar-sm { width: 24px; height: 24px; object-fit: cover; border-radius: 50%; border: 1px solid #adb5bd; }
        .narrative-text { line-height: 1.2; font-size: 0.9em; }

        /* =========================================================
           2. ESTILOS MODO OSCURO (DETECTADO AUTOMÁTICAMENTE)
           ========================================================= */
        
        body.dark-mode .table-excel {
            background-color: #343a40 !important;
            color: #e9ecef !important;
            border-color: #4b545c !important;
        }
        
        body.dark-mode .table-excel th, 
        body.dark-mode .table-excel td {
            border-color: #56606a !important;
            background-color: #343a40 !important;
            color: #e9ecef !important;
        }

        body.dark-mode .table-excel thead th {
            background-color: #3f474e !important;
            color: #ffffff !important;
            border-bottom-color: #6c757d !important;
        }

        body.dark-mode .custom-bg,
        body.dark-mode .card, 
        body.dark-mode .modal-content {
            background-color: #343a40 !important;
            color: #ffffff !important;
        }
        
        body.dark-mode .custom-header-bg {
            background-color: #3f474e !important;
            color: #ffffff !important;
            border-bottom: 1px solid #4b545c;
        }

        body.dark-mode .text-muted { color: #ced4da !important; }
        body.dark-mode .close { color: #fff; opacity: 0.8; }
        
        body.dark-mode .form-control {
            background-color: #343a40;
            border-color: #6c757d;
            color: #fff;
        }
        body.dark-mode .form-control::placeholder { color: #adb5bd; }
        
        body.dark-mode .table-hover tbody tr:hover {
            background-color: rgba(255,255,255,0.05) !important;
        }
    </style>
    @endpush

    {{-- KPI CARDS (4 COLUMNAS) --}}
    <div class="row mb-3">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success shadow-sm elevation-1">
                <div class="inner">
                    <h3>{{ $stats['created'] }}</h3>
                    <p>Altas / Creaciones</p>
                </div>
                <div class="icon"><i class="fas fa-user-plus"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger shadow-sm elevation-1">
                <div class="inner">
                    <h3>{{ $stats['deleted'] }}</h3>
                    <p>Bajas / Eliminaciones</p>
                </div>
                <div class="icon"><i class="fas fa-user-times"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning shadow-sm elevation-1">
                <div class="inner text-white">
                    <h3>{{ $stats['restored'] }}</h3>
                    <p>Restauraciones</p>
                </div>
                <div class="icon"><i class="fas fa-trash-restore"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info shadow-sm elevation-1">
                <div class="inner">
                    <h3>{{ $stats['updated'] }}</h3>
                    <p>Modificaciones</p>
                </div>
                <div class="icon"><i class="fas fa-edit"></i></div>
            </div>
        </div>
    </div>

    {{-- TABLA PRINCIPAL --}}
    <div class="card card-primary card-outline shadow-sm custom-bg">
        <div class="card-header py-2">
            <h3 class="card-title text-sm mt-1 font-weight-bold">
                <i class="fas fa-list mr-1"></i> Detalle de Eventos
                <span wire:loading class="ml-2 text-primary"><i class="fas fa-sync fa-spin"></i></span>
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool btn-xs" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
        </div>

        <div class="card-body p-2">
            {{-- Filtros --}}
            <div class="row g-2 mb-3 align-items-end">
                <div class="col-md-1 col-3">
                    <label class="mb-1 text-xs text-muted font-weight-bold">Registros</label>
                    <select wire:model.live="perPage" class="form-control form-control-sm text-xs">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="col-md-2 col-4">
                    <label class="mb-1 text-xs text-muted font-weight-bold">Desde</label>
                    <input type="date" wire:model.live="dateFrom" class="form-control form-control-sm text-xs">
                </div>
                <div class="col-md-2 col-4">
                    <label class="mb-1 text-xs text-muted font-weight-bold">Hasta</label>
                    <input type="date" wire:model.live="dateTo" class="form-control form-control-sm text-xs">
                </div>
                <div class="col-md-4">
                    <label class="mb-1 text-xs text-muted font-weight-bold">Buscador</label>
                    <div class="input-group input-group-sm">
                        <input wire:model.live.debounce.500ms="search" type="text" class="form-control text-xs" placeholder="Buscar usuario, acción, IP...">
                        <div class="input-group-append"><span class="input-group-text"><i class="fas fa-search"></i></span></div>
                    </div>
                </div>
                <div class="col-md-3 text-right pt-4">
                    <span class="badge badge-secondary font-weight-normal">Total: {{ $logs->total() }}</span>
                </div>
            </div>

            {{-- Tabla --}}
            <div class="table-responsive">
                <table class="table table-hover table-striped table-excel mb-0 w-100">
                    <thead>
                        <tr>
                            <th style="width: 100px;" class="text-center">Fecha</th>
                            <th style="width: 25%;">Responsable</th>
                            <th>Descripción del Evento</th>
                            <th style="width: 50px;" class="text-center">Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            @php
                                $human = $this->humanizeAction($log);
                                $subjectName = $this->getSubjectDisplayName($log);
                                $avatar = ($log->causer && $log->causer->avatar) ? asset('uploads/avatars/'.$log->causer->avatar) : asset('img/default-profile.png'); 
                            @endphp
                            <tr>
                                <td class="text-center text-nowrap">
                                    <span class="d-block font-weight-bold">{{ $log->created_at->format('d/m/Y') }}</span>
                                    <span class="text-muted text-xs font-monospace">{{ $log->created_at->format('H:i:s') }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $avatar }}" class="avatar-sm mr-2">
                                        <div style="line-height: 1.1;">
                                            <span class="d-block font-weight-bold text-truncate" style="max-width: 180px;">{{ $log->causer->name ?? 'Sistema' }}</span>
                                            <small class="text-muted text-xs">{{ $log->causer->email ?? 'Automático' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-start">
                                        {{-- Para restaurados usamos clase custom si es purple, o warning bootstrap --}}
                                        <span class="badge badge-{{ $human['class'] == 'purple' ? 'warning' : $human['class'] }} mr-2 mt-0" style="min-width: 25px; height: 20px; padding: 3px;">
                                            <i class="{{ $human['icon'] }}"></i>
                                        </span>
                                        <div class="narrative-text w-100">
                                            <span class="font-weight-bold text-{{ $human['class'] == 'purple' ? 'warning' : $human['class'] }}">{{ $human['badge'] }}:</span> 
                                            {{ $human['message'] ?? '' }}
                                            @if(!empty($human['detail_label']) && !empty($human['detail_value']))
                                                {{ $human['detail_label'] }}: <strong>{{ $human['detail_value'] }}</strong>
                                            @endif
                                            <div class="text-muted text-xs mt-1 border-top pt-1" style="border-color: rgba(0,0,0,0.1);">
                                                <i class="fas fa-database mr-1"></i> {{ $subjectName }} 
                                                <span class="float-right"><i class="fas fa-laptop mr-1"></i> {{ $log->properties['ip'] ?? 'IP N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button wire:click="showDetails({{ $log->id }})" class="btn btn-xs btn-outline-secondary border-0" title="Ver Detalle">
                                        <i class="fas fa-search-plus"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-5 text-muted small">No se encontraron registros coincidentes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer py-2 custom-bg">
            <div class="d-flex justify-content-end pagination-sm">{{ $logs->links() }}</div>
        </div>
    </div>

    {{-- MODAL --}}
    <div class="modal fade" id="auditModal" tabindex="-1" role="dialog" wire:ignore.self>
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            @if($selectedLog)
                @php
                    $desc = $selectedLog['description'] ?? '';
                    $isDelete = ($desc === 'User Deleted' || $desc === 'deleted') || empty($selectedLog['after']);
                    $isCreate = ($desc === 'User Created' || $desc === 'created') || (empty($selectedLog['before']) && !$isDelete);

                    $modalColor = $isDelete ? 'danger' : ($isCreate ? 'success' : 'primary');
                    $modalTitle = $isDelete ? 'Expediente Eliminado' : ($isCreate ? 'Nuevo Ingreso' : 'Modificación');
                    $modalIcon  = $isDelete ? 'fa-user-times' : ($isCreate ? 'fa-user-plus' : 'fa-pen-square');

                    $dataToShow = [];
                    if ($isDelete) $dataToShow = !empty($selectedLog['before']) ? $selectedLog['before'] : $selectedLog['after'];
                    elseif ($isCreate) $dataToShow = $selectedLog['after'];
                @endphp

                <div class="modal-content shadow-lg custom-bg">
                    <div class="modal-header bg-{{ $modalColor }} text-white py-2">
                        <h6 class="modal-title font-weight-bold"><i class="fas {{ $modalIcon }} mr-2"></i> {{ $modalTitle }} <span class="badge badge-light text-dark ml-2">ID: {{ $selectedLog['id'] }}</span></h6>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body custom-header-bg p-3">
                        <div class="card shadow-sm mb-3 border-{{ $modalColor }} custom-bg" style="border-left: 4px solid {{ $isDelete ? '#dc3545' : ($isCreate ? '#28a745' : '#007bff') }};">
                            <div class="card-body p-2">
                                <div class="row text-sm">
                                    <div class="col-6">
                                        <small class="text-muted text-uppercase font-weight-bold d-block">Realizado por:</small>
                                        <span class="font-weight-bold">{{ $selectedLog['causer'] }}</span>
                                    </div>
                                    <div class="col-6 text-right">
                                        <small class="text-muted text-uppercase font-weight-bold d-block">Fecha y Hora:</small>
                                        <span>{{ $selectedLog['date'] }}</span>
                                    </div>
                                    <div class="col-12 mt-2 pt-2 border-top">
                                        <small class="text-muted mr-2">IP Origen:</small> <span class="font-monospace">{{ $selectedLog['ip'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-outline card-{{ $modalColor }} mb-0 shadow-sm custom-bg">
                            <div class="card-header custom-header-bg py-1">
                                <strong class="text-{{ $modalColor }} text-xs text-uppercase">Datos del Registro</strong>
                            </div>
                            <div class="card-body p-0 table-responsive" style="max-height: 400px;">
                                @if($isDelete || $isCreate)
                                    <table class="table table-sm table-striped table-excel mb-0 text-sm">
                                        @foreach($dataToShow as $k => $v)
                                            <tr>
                                                <td class="w-25 font-weight-bold text-muted">{{ $k }}</td>
                                                <td>{{ $v }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @else
                                    <table class="table table-sm table-bordered table-excel mb-0 text-sm">
                                        <thead class="text-center">
                                            <tr>
                                                <th>Campo Modificado</th>
                                                <th class="text-danger">Valor Anterior</th>
                                                <th class="text-success">Valor Nuevo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($selectedLog['after'] as $k => $v)
                                                @php $old = $selectedLog['before'][$k] ?? '-'; @endphp
                                                @if($old != $v)
                                                    <tr>
                                                        <td class="font-weight-bold text-muted">{{ $k }}</td>
                                                        <td class="text-danger">{{ $old }}</td>
                                                        <td class="text-success font-weight-bold">{{ $v }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer py-1 custom-bg">
                        <button type="button" class="btn btn-xs btn-secondary px-3" data-dismiss="modal">Cerrar Ventana</button>
                    </div>
                </div>
            @endif
        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('open-modal', function () {
                $('#auditModal').modal('show');
            });
        });
    </script>
    @endpush
</div>