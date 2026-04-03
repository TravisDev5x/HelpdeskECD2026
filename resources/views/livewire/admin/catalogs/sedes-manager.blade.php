<div>
    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <h3 class="card-title mb-0">Listado de sedes</h3>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-primary btn-sm mr-2" wire:click="create">
                        <i class="fa fa-plus"></i> Nueva sede
                    </button>
                    <div class="custom-control custom-switch mb-0">
                        <input type="checkbox" class="custom-control-input" id="showInactiveSwitch" wire:model.live="showInactive">
                        <label class="custom-control-label" for="showInactiveSwitch">Ver inactivas</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-5">
                    <input
                        type="text"
                        class="form-control"
                        placeholder="Buscar sede..."
                        wire:model.live.debounce.300ms="search"
                    >
                </div>
                <div class="col-md-3">
                    <select class="custom-select" wire:model.live="perPage">
                        <option value="10">10 por página</option>
                        <option value="15">15 por página</option>
                        <option value="25">25 por página</option>
                        <option value="50">50 por página</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>Sede</th>
                            <th style="width: 140px;">Estatus</th>
                            <th style="width: 180px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sedes as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->sede }}</td>
                                <td>
                                    @if ($item->deleted_at)
                                        <span class="badge badge-danger">INACTIVA</span>
                                    @else
                                        <span class="badge badge-success">ACTIVA</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($item->deleted_at)
                                        <button
                                            type="button"
                                            class="btn btn-xs btn-success"
                                            wire:click="restore({{ $item->id }})"
                                            wire:confirm="¿Restaurar esta sede?"
                                            title="Activar sede"
                                        >
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-xs btn-info" wire:click="edit({{ $item->id }})" title="Editar sede">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-xs btn-danger"
                                            wire:click="suspend({{ $item->id }})"
                                            wire:confirm="¿Suspender esta sede?"
                                            title="Suspender sede"
                                        >
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No hay sedes para mostrar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $sedes->links() }}
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="sedeModal" tabindex="-1" role="dialog" aria-labelledby="sedeModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="save">
                    <div class="modal-header">
                        <h5 class="modal-title" id="sedeModalLabel">{{ $isEditing ? 'Editar sede' : 'Nueva sede' }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" wire:click="cancel">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-0">
                            <label for="sedeInput">Sede</label>
                            <input
                                id="sedeInput"
                                type="text"
                                class="form-control @error('sede') is-invalid @enderror"
                                wire:model="sede"
                                maxlength="150"
                                placeholder="Nombre de sede"
                            >
                            @error('sede')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" wire:click="cancel">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            {{ $isEditing ? 'Actualizar sede' : 'Guardar sede' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.addEventListener('open-sede-modal', function () {
        $('#sedeModal').modal('show');
    });

    window.addEventListener('close-sede-modal', function () {
        $('#sedeModal').modal('hide');
    });
</script>
@endpush

