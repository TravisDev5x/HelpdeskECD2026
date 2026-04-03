<div>
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
        <h5 class="mb-0">Permisos del rol</h5>
        <span class="badge badge-info">Seleccionados: {{ $selectedCount }}</span>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <input type="text" class="form-control" placeholder="Buscar por nombre técnico, módulo o descripción…" wire:model.live.debounce.250ms="search">
        </div>
        <div class="col-md-6 text-md-right mt-2 mt-md-0">
            <button type="button" class="btn btn-outline-primary btn-sm" wire:click="selectVisible">
                Seleccionar visibles
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="clearAll">
                Limpiar selección
            </button>
        </div>
    </div>

    @if($selectedCount > 0)
        <div class="mb-3 p-2 border rounded bg-light">
            <div class="small text-muted mb-2">Permisos seleccionados</div>
            <div class="d-flex flex-wrap">
                @foreach($selectedPermissions as $permission)
                    <span class="badge badge-primary mr-1 mb-1" title="{{ $permission['description'] ? $permission['name'] . ' — ' . $permission['description'] : $permission['name'] }}">
                        {{ $permission['display_name'] }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    @forelse($groupedPermissions as $group => $permissions)
        <div class="card card-outline card-primary mb-3">
            <div class="card-header py-2">
                <strong>{{ $group }}</strong>
            </div>
            <div class="card-body py-2">
                <div class="row">
                    @foreach($permissions as $permission)
                        <div class="col-md-4 col-sm-6">
                            <div class="custom-control custom-checkbox mb-2">
                                <input
                                    type="checkbox"
                                    class="custom-control-input"
                                    id="permission_{{ $permission['id'] }}"
                                    value="{{ $permission['id'] }}"
                                    name="permissions[]"
                                    wire:model.live="selected"
                                >
                                <label class="custom-control-label" for="permission_{{ $permission['id'] }}" @if(!empty($permission['description'])) title="{{ $permission['description'] }}" @endif>
                                    <span class="d-block">{{ $permission['display_name'] }}</span>
                                    <small class="text-muted font-weight-normal">{{ $permission['name'] }}</small>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-light border mb-0">
            No se encontraron permisos con ese criterio.
        </div>
    @endforelse
</div>

