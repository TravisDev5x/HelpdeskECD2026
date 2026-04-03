<div>
    @include('partials.breadcrumb', ['items' => [
        ['text' => 'Inicio', 'url' => route('home')],
        ['text' => 'Inventario V2', 'url' => route('inventory.v2.index')],
        ['text' => 'Pendientes', 'url' => null],
    ]])

    <div class="card card-outline card-warning shadow-sm mb-3">
        <div class="card-header py-2">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h3 class="card-title text-sm mb-0">
                    <i class="fas fa-clipboard-list text-warning mr-1"></i> Pendientes de inventario
                </h3>
                <div class="d-flex align-items-center flex-wrap">
                    <label class="small text-muted mb-0 mr-2">Filas por tabla</label>
                    <select wire:model.live.number="perPage" class="form-control form-control-sm" style="width: 5rem;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
            <div class="mt-2 d-flex flex-wrap justify-content-end">
                <a href="{{ route('inventory.v2.assignments', ['assignee_employment' => 'baja']) }}" class="btn btn-sm btn-outline-danger mr-1 mb-1">
                    <i class="fas fa-user-times mr-1"></i> Asignaciones (solo bajas)
                </a>
                <a href="{{ route('inventory.v2.index') }}" class="btn btn-sm btn-default mb-1">
                    <i class="fas fa-cubes mr-1"></i> Inventario
                </a>
            </div>
        </div>
        <div class="card-body small text-muted">
            <p class="mb-2"><strong>Qué es esta pantalla:</strong> colas operativas e inconsistencias de datos.</p>
            <ul class="mb-0 pl-3">
                <li><strong>Responsable en baja:</strong> devolver o reasignar equipo.</li>
                <li><strong>Robados / perdidos:</strong> según el nombre del estatus en catálogo (p. ej. ROBADO, PERDIDO).</li>
                <li><strong>Mantenimiento:</strong> estatus que contenga “mantenim” o ticket en curso sin fecha de fin.</li>
                <li><strong>Datos faltantes:</strong> empresa, categoría o estatus no asignados.</li>
                <li><strong>Importación:</strong> filas pendientes de match/corrección con reintento inmediato.</li>
                <li><strong>Duplicados:</strong> detección por Serie y Etiqueta interna normalizadas.</li>
                <li><strong>Monitoreo:</strong> @can('read inventory monitor')KPIs y alertas en <a href="{{ route('inventory.v2.monitor') }}">Monitoreo</a>.@else KPIs y alertas en la vista Monitoreo (requiere permiso de monitoreo).@endcan</li>
            </ul>
        </div>
    </div>

    {{-- 1. Asignados a usuario dado de baja --}}
    <div class="card card-outline card-danger shadow-sm mb-3">
        <div class="card-header py-2">
            <span class="font-weight-bold text-sm"><i class="fas fa-user-times mr-1"></i> Equipos asignados a usuario dado de baja</span>
            <span class="badge badge-danger float-right">{{ $countBaja }}</span>
        </div>
        <div class="card-body p-0">
            @if ($assignedToBaja->total() === 0)
                <p class="text-muted small mb-0 p-3">Sin registros. Muy bien.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="bg-light text-xs text-uppercase">
                            <tr>
                                <th>Etiqueta</th>
                                <th>Equipo</th>
                                <th>Responsable (baja)</th>
                                <th>Sede</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($assignedToBaja as $a)
                                <tr>
                                    <td class="text-monospace">{{ $a->internal_tag ?? '—' }}</td>
                                    <td>{{ $a->name }}</td>
                                    <td>
                                        @if ($a->currentUser)
                                            {{ $a->currentUser->name }} {{ $a->currentUser->ap_paterno ?? '' }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $a->sede->sede ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($assignedToBaja->hasPages())
                    <div class="px-3 py-2 border-top">{{ $assignedToBaja->links() }}</div>
                @endif
            @endif
        </div>
    </div>

    {{-- Robados (por nombre de estatus) --}}
    <div class="card card-outline card-dark shadow-sm mb-3">
        <div class="card-header py-2">
            <span class="font-weight-bold text-sm"><i class="fas fa-exclamation-triangle mr-1"></i> Equipos marcados como robados</span>
            <span class="badge badge-dark float-right">{{ $countStolen }}</span>
        </div>
        <div class="card-body p-0">
            @if ($stolenAssets->total() === 0)
                <p class="text-muted small mb-0 p-3">Sin activos con estatus que contenga “robad” (p. ej. ROBADO).
                    @can('manage inventory config')
                        Puede crear el estatus en <a href="{{ route('inventory.config.status') }}">configuración de estatus</a>.
                    @endcan
                </p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="bg-light text-xs text-uppercase">
                            <tr>
                                <th>Etiqueta</th>
                                <th>Equipo</th>
                                <th>Estatus</th>
                                <th>Sede</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stolenAssets as $a)
                                <tr>
                                    <td class="text-monospace">{{ $a->internal_tag ?? '—' }}</td>
                                    <td>{{ $a->name }}</td>
                                    <td><span class="badge badge-{{ $a->status->badge_class ?? 'secondary' }}">{{ $a->status->name ?? '—' }}</span></td>
                                    <td>{{ $a->sede->sede ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($stolenAssets->hasPages())
                    <div class="px-3 py-2 border-top">{{ $stolenAssets->links() }}</div>
                @endif
            @endif
        </div>
    </div>

    {{-- Perdidos --}}
    <div class="card card-outline card-secondary shadow-sm mb-3">
        <div class="card-header py-2">
            <span class="font-weight-bold text-sm"><i class="fas fa-search-location mr-1"></i> Equipos marcados como perdidos</span>
            <span class="badge badge-secondary float-right">{{ $countLost }}</span>
        </div>
        <div class="card-body p-0">
            @if ($lostAssets->total() === 0)
                <p class="text-muted small mb-0 p-3">Sin activos con estatus que contenga “perdid” (p. ej. PERDIDO).</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="bg-light text-xs text-uppercase">
                            <tr>
                                <th>Etiqueta</th>
                                <th>Equipo</th>
                                <th>Estatus</th>
                                <th>Sede</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lostAssets as $a)
                                <tr>
                                    <td class="text-monospace">{{ $a->internal_tag ?? '—' }}</td>
                                    <td>{{ $a->name }}</td>
                                    <td><span class="badge badge-{{ $a->status->badge_class ?? 'secondary' }}">{{ $a->status->name ?? '—' }}</span></td>
                                    <td>{{ $a->sede->sede ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($lostAssets->hasPages())
                    <div class="px-3 py-2 border-top">{{ $lostAssets->links() }}</div>
                @endif
            @endif
        </div>
    </div>

    {{-- Mantenimiento --}}
    <div class="card card-outline card-warning shadow-sm mb-3">
        <div class="card-header py-2">
            <span class="font-weight-bold text-sm"><i class="fas fa-tools mr-1"></i> Equipos en mantenimiento</span>
            <span class="badge badge-warning float-right text-dark">{{ $countMaintenance }}</span>
        </div>
        <div class="card-body p-0">
            @if ($maintenanceAssets->total() === 0)
                <p class="text-muted small mb-0 p-3">Sin activos en estatus de mantenimiento ni tickets abiertos sin fecha de fin.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="bg-light text-xs text-uppercase">
                            <tr>
                                <th>Etiqueta</th>
                                <th>Equipo</th>
                                <th>Estatus</th>
                                <th>Ticket / inicio</th>
                                <th>Sede</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($maintenanceAssets as $a)
                                @php
                                    $openM = $a->maintenances->first();
                                @endphp
                                <tr>
                                    <td class="text-monospace">{{ $a->internal_tag ?? '—' }}</td>
                                    <td>{{ $a->name }}</td>
                                    <td><span class="badge badge-{{ $a->status->badge_class ?? 'secondary' }}">{{ $a->status->name ?? '—' }}</span></td>
                                    <td class="small">
                                        @if ($openM)
                                            {{ $openM->title ?? 'Mantenimiento' }}
                                            @if ($openM->start_date)
                                                <br><span class="text-muted">{{ $openM->start_date->format('d/m/Y') }}</span>
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $a->sede->sede ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($maintenanceAssets->hasPages())
                    <div class="px-3 py-2 border-top">{{ $maintenanceAssets->links() }}</div>
                @endif
            @endif
        </div>
    </div>

    {{-- Sin empresa --}}
    <div class="card card-outline card-secondary shadow-sm mb-3">
        <div class="card-header py-2">
            <span class="font-weight-bold text-sm"><i class="fas fa-building mr-1"></i> Activos sin empresa</span>
            <span class="badge badge-secondary float-right">{{ $countNoCompany }}</span>
        </div>
        <div class="card-body p-0">
            @if ($withoutCompany->total() === 0)
                <p class="text-muted small mb-0 p-3">Sin registros.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="bg-light text-xs text-uppercase">
                            <tr>
                                <th>ID</th>
                                <th>Etiqueta</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($withoutCompany as $a)
                                <tr>
                                    <td>{{ $a->id }}</td>
                                    <td class="text-monospace">{{ $a->internal_tag ?? '—' }}</td>
                                    <td>{{ $a->name }}</td>
                                    <td>{{ $a->category->name ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($withoutCompany->hasPages())
                    <div class="px-3 py-2 border-top">{{ $withoutCompany->links() }}</div>
                @endif
            @endif
        </div>
    </div>

    <div class="card card-outline card-info shadow-sm mb-3">
        <div class="card-header py-2">
            <span class="font-weight-bold text-sm"><i class="fas fa-tags mr-1"></i> Activos sin etiqueta de sede</span>
            <span class="badge badge-info float-right">{{ $countNoLabel }}</span>
        </div>
        <div class="card-body p-0">
            @if ($withoutLabel->total() === 0)
                <p class="text-muted small mb-0 p-3">Sin registros.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="bg-light text-xs text-uppercase">
                            <tr>
                                <th>ID</th>
                                <th>Etiqueta</th>
                                <th>Nombre</th>
                                <th>Sede</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($withoutLabel as $a)
                                <tr>
                                    <td>{{ $a->id }}</td>
                                    <td class="text-monospace">{{ $a->internal_tag ?? '—' }}</td>
                                    <td>{{ $a->name }}</td>
                                    <td>{{ $a->sede->sede ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($withoutLabel->hasPages())
                    <div class="px-3 py-2 border-top">{{ $withoutLabel->links() }}</div>
                @endif
            @endif
        </div>
    </div>

    <div class="card card-outline card-warning shadow-sm mb-3">
        <div class="card-header py-2">
            <span class="font-weight-bold text-sm"><i class="fas fa-exclamation-circle mr-1"></i> Tag interno igual a etiqueta de sede</span>
            <span class="badge badge-warning float-right">{{ $countTagSameAsLabel }}</span>
        </div>
        <div class="card-body p-0">
            @if ($tagSameAsLabel->total() === 0)
                <p class="text-muted small mb-0 p-3">Sin registros.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="bg-light text-xs text-uppercase">
                            <tr>
                                <th>ID</th>
                                <th>Tag interno</th>
                                <th>Etiqueta sede</th>
                                <th>Equipo</th>
                                <th>Sede</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tagSameAsLabel as $a)
                                <tr>
                                    <td>{{ $a->id }}</td>
                                    <td class="text-monospace">{{ $a->internal_tag }}</td>
                                    <td>{{ $a->label->name ?? '—' }}</td>
                                    <td>{{ $a->name }}</td>
                                    <td>{{ $a->sede->sede ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($tagSameAsLabel->hasPages())
                    <div class="px-3 py-2 border-top">{{ $tagSameAsLabel->links() }}</div>
                @endif
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-secondary shadow-sm mb-3">
                <div class="card-header py-2">
                    <span class="font-weight-bold text-sm">Sin categoría</span>
                    <span class="badge badge-secondary float-right">{{ $countNoCategory }}</span>
                </div>
                <div class="card-body p-0">
                    @if ($withoutCategory->total() === 0)
                        <p class="text-muted small mb-0 p-2">Sin registros.</p>
                    @else
                        <ul class="list-group list-group-flush small">
                            @foreach ($withoutCategory as $a)
                                <li class="list-group-item py-1 d-flex justify-content-between">
                                    <span class="text-monospace">{{ $a->internal_tag ?? '#' . $a->id }}</span>
                                    <span class="text-truncate ml-2">{{ $a->name }}</span>
                                </li>
                            @endforeach
                        </ul>
                        @if ($withoutCategory->hasPages())
                            <div class="px-3 py-2 border-top">{{ $withoutCategory->links() }}</div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-secondary shadow-sm mb-3">
                <div class="card-header py-2">
                    <span class="font-weight-bold text-sm">Sin estatus</span>
                    <span class="badge badge-secondary float-right">{{ $countNoStatus }}</span>
                </div>
                <div class="card-body p-0">
                    @if ($withoutStatus->total() === 0)
                        <p class="text-muted small mb-0 p-2">Sin registros.</p>
                    @else
                        <ul class="list-group list-group-flush small">
                            @foreach ($withoutStatus as $a)
                                <li class="list-group-item py-1 d-flex justify-content-between">
                                    <span class="text-monospace">{{ $a->internal_tag ?? '#' . $a->id }}</span>
                                    <span class="text-truncate ml-2">{{ $a->name }}</span>
                                </li>
                            @endforeach
                        </ul>
                        @if ($withoutStatus->hasPages())
                            <div class="px-3 py-2 border-top">{{ $withoutStatus->links() }}</div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-danger shadow-sm mb-3">
                <div class="card-header py-2">
                    <span class="font-weight-bold text-sm"><i class="fas fa-clone mr-1"></i> Duplicados por Serie</span>
                    <span class="badge badge-danger float-right">{{ $countSerialDup }}</span>
                </div>
                <div class="card-body p-0">
                    @if ($serialDuplicates->total() === 0)
                        <p class="text-muted small mb-0 p-2">Sin registros.</p>
                    @else
                        <ul class="list-group list-group-flush small">
                            @foreach ($serialDuplicates as $dup)
                                <li class="list-group-item py-1 d-flex justify-content-between">
                                    <span class="text-monospace">{{ $dup->duplicate_value }}</span>
                                    <span class="badge badge-danger">{{ $dup->total }}</span>
                                </li>
                            @endforeach
                        </ul>
                        @if ($serialDuplicates->hasPages())
                            <div class="px-3 py-2 border-top">{{ $serialDuplicates->links() }}</div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-danger shadow-sm mb-3">
                <div class="card-header py-2">
                    <span class="font-weight-bold text-sm"><i class="fas fa-copy mr-1"></i> Duplicados por Etiqueta interna</span>
                    <span class="badge badge-danger float-right">{{ $countTagDup }}</span>
                </div>
                <div class="card-body p-0">
                    @if ($tagDuplicates->total() === 0)
                        <p class="text-muted small mb-0 p-2">Sin registros.</p>
                    @else
                        <ul class="list-group list-group-flush small">
                            @foreach ($tagDuplicates as $dup)
                                <li class="list-group-item py-1 d-flex justify-content-between">
                                    <span class="text-monospace">{{ $dup->duplicate_value }}</span>
                                    <span class="badge badge-danger">{{ $dup->total }}</span>
                                </li>
                            @endforeach
                        </ul>
                        @if ($tagDuplicates->hasPages())
                            <div class="px-3 py-2 border-top">{{ $tagDuplicates->links() }}</div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary shadow-sm mb-3">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <span class="font-weight-bold text-sm"><i class="fas fa-file-import mr-1"></i> Filas pendientes de importación</span>
            <span class="badge badge-primary">{{ $countPendingImportRows }}</span>
        </div>
        <div class="card-body p-0">
            @if ($pendingImportRows->total() === 0)
                <p class="text-muted small mb-0 p-3">No hay filas pendientes del importador.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="bg-light text-xs text-uppercase">
                            <tr>
                                <th>Lote</th>
                                <th>Fila</th>
                                <th>Serie</th>
                                <th>Acción</th>
                                <th>Detalle</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingImportRows as $row)
                                @php
                                    $parsed = (array) ($row->parsed ?? []);
                                    $errs = (array) ($row->errors ?? []);
                                    $warns = (array) ($row->warnings ?? []);
                                    $detail = implode(' | ', array_merge($errs, $warns));
                                @endphp
                                <tr>
                                    <td>#{{ $row->batch_id }}</td>
                                    <td>{{ $row->row_number }}</td>
                                    <td class="text-monospace">{{ $parsed['serial'] ?? '—' }}</td>
                                    <td>{{ $row->action ?? '—' }}</td>
                                    <td class="small text-muted" title="{{ $detail }}">{{ $detail !== '' ? $detail : 'Pendiente de corrección' }}</td>
                                    <td class="text-right">
                                        <button wire:click="retryImportRow({{ $row->id }})" class="btn btn-xs btn-primary" title="Revalidar y aplicar fila">
                                            <i class="fas fa-sync-alt mr-1"></i>Reintentar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($pendingImportRows->hasPages())
                    <div class="px-3 py-2 border-top">{{ $pendingImportRows->links() }}</div>
                @endif
            @endif
        </div>
    </div>

    <div class="card card-outline card-primary shadow-sm mb-3">
        <div class="card-header py-2">
            <span class="font-weight-bold text-sm"><i class="fas fa-layer-group mr-1"></i> Lotes con revisión pendiente</span>
        </div>
        <div class="card-body p-0">
            @if ($pendingImportBatches->total() === 0)
                <p class="text-muted small mb-0 p-3">No hay lotes pendientes.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="bg-light text-xs text-uppercase">
                            <tr>
                                <th>ID</th>
                                <th>Archivo</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingImportBatches as $batch)
                                <tr>
                                    <td>#{{ $batch->id }}</td>
                                    <td>{{ $batch->file_name ?: '—' }}</td>
                                    <td><span class="badge badge-secondary">{{ $batch->status }}</span></td>
                                    <td>{{ optional($batch->created_at)->format('d/m/Y H:i') }}</td>
                                    <td class="text-right">
                                        <button wire:click="retryImportBatch({{ $batch->id }})" class="btn btn-xs btn-outline-primary" title="Reintentar filas pendientes del lote">
                                            <i class="fas fa-redo mr-1"></i>Reintentar lote
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($pendingImportBatches->hasPages())
                    <div class="px-3 py-2 border-top">{{ $pendingImportBatches->links() }}</div>
                @endif
            @endif
        </div>
    </div>
</div>
