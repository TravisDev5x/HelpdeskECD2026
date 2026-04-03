<div>
    {{-- MENSAJES --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card card-secondary card-outline">
        <div class="card-header py-2">
            <h3 class="card-title"><i class="fas fa-flag mr-1"></i> Catálogo de Estatus</h3>
            <div class="card-tools">
                <button wire:click="create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Nuevo Estatus
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nombre del Estatus</th>
                        <th>Vista Previa (Badge)</th>
                        <th>¿Es Asignable?</th>
                        <th style="width: 150px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($statuses as $status)
                    <tr>
                        <td>{{ $status->name }}</td>
                        <td>
                            <span class="badge badge-{{ $status->badge_class }} p-2">
                                {{ $status->name }}
                            </span>
                        </td>
                        <td>
                            @if($status->assignable)
                                <span class="badge badge-success"><i class="fas fa-check"></i> Sí</span>
                            @else
                                <span class="badge badge-secondary"><i class="fas fa-times"></i> No</span>
                            @endif
                        </td>
                        <td>
                            <button wire:click="edit({{ $status->id }})" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button wire:click="openConfirmDelete({{ $status->id }})"
                                    class="btn btn-sm btn-danger" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-0">
                            @include('partials.empty-state', [
                                'icon' => 'fa-flag',
                                'message' => 'No hay estatus configurados. Cree al menos uno para usar en activos.',
                                'actionLabel' => 'Nuevo Estatus',
                                'actionWire' => 'create',
                            ])
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL CREAR / EDITAR --}}
    @if($isOpen)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        {{ $status_id ? 'Editar Estatus' : 'Crear Estatus' }}
                    </h5>
                    <button wire:click="closeModal" class="close text-white"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label>Nombre del Estatus</label>
                            <input type="text" wire:model="name" class="form-control" placeholder="Ej: EN REPARACIÓN">
                            @include('partials.form-help', ['text' => 'Texto que verá el usuario en el listado de activos.'])
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Color (Etiqueta)</label>
                            <select wire:model.live="badge_class" class="form-control">
                                <option value="secondary">Gris (Secondary)</option>
                                <option value="primary">Azul (Primary)</option>
                                <option value="success">Verde (Success)</option>
                                <option value="danger">Rojo (Danger)</option>
                                <option value="warning">Amarillo (Warning)</option>
                                <option value="info">Cyan (Info)</option>
                                <option value="dark">Negro (Dark)</option>
                            </select>
                            <small class="form-text text-muted help-text">Vista previa: <span class="badge badge-{{ $badge_class }}">EJEMPLO</span></small>
                        </div>

                        <div class="form-group">
                            <label>Comportamiento</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="assignableSwitch" wire:model.live="assignable">
                                <label class="custom-control-label" for="assignableSwitch">
                                    ¿Permite asignar a usuarios?
                                </label>
                            </div>
                            <small class="text-muted">
                                Si está marcado, podrás prestar equipos con este estatus. Si no (ej. "ROBADO"), el sistema bloqueará la asignación.
                            </small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button wire:click="closeModal" class="btn btn-secondary">Cancelar</button>
                    <button wire:click="store" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @include('partials.confirm-modal')
</div>