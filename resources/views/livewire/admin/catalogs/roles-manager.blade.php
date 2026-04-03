<div>
    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <h3 class="card-title mb-0">Listado de roles</h3>
                @can('create role')
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> Crear rol
                </a>
                @endcan
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-5">
                    <input type="text" class="form-control" placeholder="Buscar rol o descripción..." wire:model.live.debounce.300ms="search">
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
                            <th>Descripción</th>
                            <th style="width: 130px;">Permisos</th>
                            <th style="width: 190px;" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $role)
                            <tr>
                                <td>{{ $role->id }}</td>
                                <td>{{ $role->name }}</td>
                                <td>{{ $role->description ?: 'Sin descripción' }}</td>
                                <td>{{ $role->permissions_count }}</td>
                                <td class="text-center">
                                    @can('update role')
                                    <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-xs btn-info" title="Editar rol">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    @endcan
                                    @can('delete role')
                                    <button
                                        type="button"
                                        class="btn btn-xs btn-danger"
                                        wire:click="deleteRole({{ $role->id }})"
                                        wire:confirm="¿Estás seguro de querer eliminar este rol?"
                                        title="Eliminar rol"
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No hay roles para mostrar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $roles->links() }}
            </div>
        </div>
    </div>
</div>

