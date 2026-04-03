<div>
    @include('partials.breadcrumb', ['items' => [
        ['text' => 'Inicio', 'url' => route('home')],
        ['text' => 'Inventario V2', 'url' => auth()->user()->can('read inventory') ? route('inventory.v2.index') : null],
        ['text' => 'Historial de asignaciones', 'url' => null],
    ]])

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header py-2 d-flex flex-wrap align-items-center justify-content-between">
            <h3 class="card-title text-sm mb-0">
                <i class="fas fa-history mr-1 text-primary"></i> Historial de asignaciones y devoluciones
            </h3>
            <div class="d-flex flex-wrap gap-1 align-items-center">
                <a href="{{ route('inventory.v2.assignment-history.export', $exportQuery) }}" class="btn btn-outline-secondary btn-sm" title="Descargar CSV (UTF-8)">
                    <i class="fas fa-file-csv mr-1"></i> Exportar CSV
                </a>
            </div>
        </div>
        <div class="card-body pb-2">
            <p class="text-muted small mb-3">
                Registros tomados de la bitácora <code>inv_movements</code>. Los lotes masivos comparten el mismo UUID de lote.
            </p>

            <div class="row">
                <div class="col-md-4 mb-2">
                    <label class="text-xs font-weight-bold mb-0">Buscar activo</label>
                    <input type="text" class="form-control form-control-sm" wire:model.live.debounce.400ms="search" placeholder="Etiqueta, nombre o serie">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="text-xs font-weight-bold mb-0">Tipo</label>
                    <select class="form-control form-control-sm" wire:model.live="typeScope">
                        <option value="assignments">Asignaciones / devoluciones</option>
                        <option value="all">Todos los movimientos</option>
                        <option value="CHECKOUT">CHECKOUT</option>
                        <option value="CHECKIN">CHECKIN</option>
                        <option value="TRASLADO">TRASLADO</option>
                        <option value="BAJA">BAJA</option>
                        <option value="DESPIECE">DESPIECE</option>
                        <option value="AUDIT">AUDIT</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="text-xs font-weight-bold mb-0">Responsable (afectado)</label>
                    <select class="form-control form-control-sm" wire:model.live="filterUserId">
                        <option value="">—</option>
                        @foreach($usersForFilter as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} {{ $u->ap_paterno }} {{ $u->ap_materno }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="text-xs font-weight-bold mb-0">Registró (admin)</label>
                    <select class="form-control form-control-sm" wire:model.live="filterAdminId">
                        <option value="">—</option>
                        @foreach($usersForFilter as $adm)
                            <option value="{{ $adm->id }}">{{ $adm->name }} {{ $adm->ap_paterno }} {{ $adm->ap_materno }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="text-xs font-weight-bold mb-0">Lote (UUID)</label>
                    <input type="text" class="form-control form-control-sm" wire:model.live.debounce.400ms="filterBatchUuid" placeholder="opcional">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="text-xs font-weight-bold mb-0">Desde</label>
                    <input type="date" class="form-control form-control-sm" wire:model.live="dateFrom">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="text-xs font-weight-bold mb-0">Hasta</label>
                    <input type="date" class="form-control form-control-sm" wire:model.live="dateTo">
                </div>
                <div class="col-md-2 mb-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="resetFiltros">
                        <i class="fas fa-undo-alt mr-1"></i> Restablecer
                    </button>
                </div>
            </div>

            <div class="table-responsive mt-2">
                <table class="table table-sm table-bordered table-striped mb-0">
                    <thead class="thead-light text-xs">
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Activo</th>
                            <th>Responsable (nuevo)</th>
                            <th>Antes (previo)</th>
                            <th>Registró</th>
                            <th>Motivo</th>
                            <th>Lote</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs">
                        @forelse($movements as $mov)
                            <tr>
                                <td>{{ $mov->date?->format('d/m/Y H:i') }}</td>
                                <td><span class="badge badge-secondary">{{ $mov->type }}</span></td>
                                <td>
                                    @if($mov->asset)
                                        <strong>{{ $mov->asset->internal_tag ?: '#'.$mov->asset_id }}</strong>
                                        <span class="text-muted">· {{ \Illuminate\Support\Str::limit($mov->asset->name, 40) }}</span>
                                    @else
                                        #{{ $mov->asset_id }}
                                    @endif
                                </td>
                                <td>
                                    @if($mov->user)
                                        {{ $mov->user->name }} {{ $mov->user->ap_paterno }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if($mov->previousUser)
                                        {{ $mov->previousUser->name }} {{ $mov->previousUser->ap_paterno }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $mov->admin?->name ?? '—' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($mov->reason ?? $mov->notes ?? '—', 48) }}</td>
                                <td>
                                    @if($mov->batch_uuid)
                                        <code class="small" title="{{ $mov->batch_uuid }}">{{ \Illuminate\Support\Str::limit($mov->batch_uuid, 10, '…') }}</code>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Sin registros con los filtros actuales.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-2">
                {{ $movements->links() }}
            </div>
        </div>
    </div>
</div>
