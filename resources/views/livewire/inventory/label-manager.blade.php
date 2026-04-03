<div>
    @include('partials.breadcrumb', ['items' => [
        ['text' => 'Inicio', 'url' => route('home')],
        ['text' => 'Inventario V2', 'url' => route('inventory.v2.index')],
        ['text' => 'Etiquetas por sede', 'url' => null],
    ]])
    {{-- Mensajes éxito/error se muestran en layout.blade.php --}}

    <div class="card card-secondary card-outline">
        <div class="card-header py-2">
            <h3 class="card-title"><i class="fas fa-tags mr-1"></i> Catálogo de Etiquetas por Sede</h3>
            <div class="card-tools">
                <button wire:click="create" class="btn btn-primary btn-sm" title="Crear nueva etiqueta de sede">
                    <i class="fas fa-plus"></i> Nueva Etiqueta
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Etiqueta</th>
                        <th>Sede</th>
                        <th class="text-center">Activa</th>
                        <th class="text-center">Activos</th>
                        <th style="width: 150px;" class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($labels as $row)
                        <tr>
                            <td class="font-weight-bold">{{ $row->name }}</td>
                            <td>{{ $row->sede->sede ?? 'Sin sede' }}</td>
                            <td class="text-center">{!! $row->is_active ? '<span class="badge badge-success">Sí</span>' : '<span class="badge badge-secondary">No</span>' !!}</td>
                            <td class="text-center"><span class="badge badge-light border">{{ $row->assets()->count() }}</span></td>
                            <td class="text-right">
                                <button wire:click="edit({{ $row->id }})" class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="openConfirmDelete({{ $row->id }})" class="btn btn-sm btn-danger" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-0">
                                @include('partials.empty-state', [
                                    'icon' => 'fa-tags',
                                    'message' => 'No hay etiquetas por sede registradas.',
                                    'actionLabel' => 'Nueva Etiqueta',
                                    'actionWire' => 'create',
                                ])
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($isOpen)
        <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">{{ $label_id ? 'Editar etiqueta' : 'Crear etiqueta' }}</h5>
                        <button wire:click="closeModal" class="close text-white"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit="store">
                            <div class="form-group">
                                <label>Sede</label>
                                <select wire:model="sede_id" class="form-control">
                                    <option value="">Seleccione una sede...</option>
                                    @foreach ($sedes as $sede)
                                        <option value="{{ $sede->id }}">{{ $sede->sede }}</option>
                                    @endforeach
                                </select>
                                @error('sede_id')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Etiqueta</label>
                                <input type="text" wire:model="name" class="form-control" placeholder="Ej: MTY-CORP">
                                @include('partials.form-help', ['text' => 'Se vincula automáticamente al activo según la sede seleccionada.'])
                                @error('name')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group mb-0">
                                <div class="custom-control custom-switch">
                                    <input id="label-active-switch" type="checkbox" class="custom-control-input" wire:model="is_active">
                                    <label class="custom-control-label" for="label-active-switch">Etiqueta activa</label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button wire:click="closeModal" class="btn btn-secondary">Cancelar</button>
                        <button wire:click="store" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('partials.confirm-modal')
</div>
