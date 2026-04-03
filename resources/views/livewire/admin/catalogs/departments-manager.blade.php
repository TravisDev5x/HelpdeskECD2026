<div>
    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <h3 class="card-title mb-0">Listado de departamentos</h3>
                <div class="d-flex align-items-center">
                    @can('create department')
                    <button type="button" class="btn btn-primary btn-sm mr-2" wire:click="create">
                        <i class="fa fa-plus"></i> Crear departamento
                    </button>
                    @endcan
                    <div class="custom-control custom-switch mb-0">
                        <input type="checkbox" class="custom-control-input" id="showInactiveDepartmentsSwitch" wire:model.live="showInactive">
                        <label class="custom-control-label" for="showInactiveDepartmentsSwitch">Ver inactivos</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-5">
                    <input type="text" class="form-control" placeholder="Buscar departamento o área..." wire:model.live.debounce.300ms="search">
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
                            <th>Departamento</th>
                            <th>Área</th>
                            <th style="width: 140px;">Estatus</th>
                            <th style="width: 180px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($departments as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->area?->name ?? 'Sin área' }}</td>
                                <td>
                                    @if ($item->deleted_at)
                                        <span class="badge badge-danger">INACTIVO</span>
                                    @else
                                        <span class="badge badge-success">ACTIVO</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($item->deleted_at)
                                        @can('delete department')
                                        <button type="button" class="btn btn-xs btn-success" wire:click="restore({{ $item->id }})" wire:confirm="¿Activar este departamento?" title="Activar departamento">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        @endcan
                                    @else
                                        @can('update department')
                                        <button type="button" class="btn btn-xs btn-info" wire:click="edit({{ $item->id }})" title="Editar departamento">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        @endcan
                                        @can('delete department')
                                        <button type="button" class="btn btn-xs btn-danger" wire:click="suspend({{ $item->id }})" wire:confirm="¿Suspender este departamento?" title="Suspender departamento">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No hay departamentos para mostrar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $departments->links() }}
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="departmentModal" tabindex="-1" role="dialog" aria-labelledby="departmentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="save">
                    <div class="modal-header">
                        <h5 class="modal-title" id="departmentModalLabel">{{ $isEditing ? 'Editar departamento' : 'Nuevo departamento' }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" wire:click="cancel">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="departmentNameInput">Nombre</label>
                            <input id="departmentNameInput" type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" maxlength="255" placeholder="Nombre del departamento">
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-0">
                            <label for="departmentAreaInput">Área</label>
                            <select id="departmentAreaInput" class="custom-select @error('areaId') is-invalid @enderror" wire:model="areaId">
                                <option value="">Sin área</option>
                                @foreach ($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                @endforeach
                            </select>
                            @error('areaId')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" wire:click="cancel">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            {{ $isEditing ? 'Actualizar departamento' : 'Guardar departamento' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.addEventListener('open-department-modal', function () {
        $('#departmentModal').modal('show');
    });

    window.addEventListener('close-department-modal', function () {
        $('#departmentModal').modal('hide');
    });
</script>
@endpush

