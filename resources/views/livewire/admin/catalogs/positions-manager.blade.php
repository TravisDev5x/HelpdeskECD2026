<div>
    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <h3 class="card-title mb-0">Listado de puestos</h3>
                <div class="d-flex align-items-center">
                    @can('create position')
                    <button type="button" class="btn btn-primary btn-sm mr-2" wire:click="create">
                        <i class="fa fa-plus"></i> Crear puesto
                    </button>
                    @endcan
                    <div class="custom-control custom-switch mb-0">
                        <input type="checkbox" class="custom-control-input" id="showInactivePositionsSwitch" wire:model.live="showInactive">
                        <label class="custom-control-label" for="showInactivePositionsSwitch">Ver inactivos</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-5">
                    <input type="text" class="form-control" placeholder="Buscar puesto, área o departamento..." wire:model.live.debounce.300ms="search">
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
                            <th>Nombre</th>
                            <th>Área</th>
                            <th>Departamento</th>
                            <th>Extensión</th>
                            <th style="width: 140px;">Estatus</th>
                            <th style="width: 180px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($positions as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->area }}</td>
                                <td>{{ $item->department?->name ?? 'Sin departamento' }}</td>
                                <td>{{ $item->extension ?: 'N/A' }}</td>
                                <td>
                                    @if ($item->deleted_at)
                                        <span class="badge badge-danger">INACTIVO</span>
                                    @else
                                        <span class="badge badge-success">ACTIVO</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($item->deleted_at)
                                        @can('delete position')
                                        <button type="button" class="btn btn-xs btn-success" wire:click="restore({{ $item->id }})" wire:confirm="¿Activar este puesto?" title="Activar puesto">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        @endcan
                                    @else
                                        @can('update position')
                                        <button type="button" class="btn btn-xs btn-info" wire:click="edit({{ $item->id }})" title="Editar puesto">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        @endcan
                                        @can('delete position')
                                        <button type="button" class="btn btn-xs btn-danger" wire:click="suspend({{ $item->id }})" wire:confirm="¿Suspender este puesto?" title="Suspender puesto">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">No hay puestos para mostrar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $positions->links() }}
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="positionModal" tabindex="-1" role="dialog" aria-labelledby="positionModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="save">
                    <div class="modal-header">
                        <h5 class="modal-title" id="positionModalLabel">{{ $isEditing ? 'Editar puesto' : 'Nuevo puesto' }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" wire:click="cancel">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="positionNameInput">Nombre</label>
                            <input id="positionNameInput" type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" maxlength="255" placeholder="Nombre del puesto">
                            @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="positionAreaInput">Área</label>
                            <input id="positionAreaInput" type="text" class="form-control @error('area') is-invalid @enderror" wire:model="area" maxlength="255" placeholder="Área del puesto">
                            @error('area')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="positionDepartmentInput">Departamento</label>
                            <select id="positionDepartmentInput" class="custom-select @error('departmentId') is-invalid @enderror" wire:model="departmentId">
                                <option value="">Sin departamento</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                            @error('departmentId')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group mb-0">
                            <label for="positionExtensionInput">Extensión</label>
                            <input id="positionExtensionInput" type="number" class="form-control @error('extension') is-invalid @enderror" wire:model="extension" placeholder="Extensión (opcional)">
                            @error('extension')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" wire:click="cancel">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            {{ $isEditing ? 'Actualizar puesto' : 'Guardar puesto' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.addEventListener('open-position-modal', function () {
        $('#positionModal').modal('show');
    });

    window.addEventListener('close-position-modal', function () {
        $('#positionModal').modal('hide');
    });
</script>
@endpush

