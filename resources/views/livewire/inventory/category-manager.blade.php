<div>
    @include('partials.breadcrumb', ['items' => [
        ['text' => 'Inicio', 'url' => route('home')],
        ['text' => 'Inventario V2', 'url' => route('inventory.v2.index')],
        ['text' => 'Categorías', 'url' => null],
    ]])
    {{-- Mensajes éxito/error se muestran en layout.blade.php --}}

    <div class="card card-secondary card-outline">
        <div class="card-header py-2">
            <h3 class="card-title"><i class="fas fa-tags mr-1"></i> Catálogo de Categorías</h3>
            <div class="card-tools">
                <button wire:click="create" class="btn btn-primary btn-sm" title="Crear nueva categoría de activos">
                    <i class="fas fa-plus"></i> Nueva Categoría
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nombre de la Categoría</th>
                        <th class="text-center">Activos Registrados</th>
                        <th style="width: 150px" class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $cat)
                        <tr>
                            <td>
                                <i class="fas fa-folder text-warning mr-2"></i> {{ $cat->name }}
                            </td>
                            <td class="text-center">
                                <span class="badge badge-light border">{{ $cat->assets()->count() }}</span>
                            </td>
                            <td class="text-right">
                                <button wire:click="edit({{ $cat->id }})" class="btn btn-sm btn-warning"
                                    title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="openConfirmDelete({{ $cat->id }})"
                                    class="btn btn-sm btn-danger" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="p-0">
                                @include('partials.empty-state', [
                                    'icon' => 'fa-folder-open',
                                    'message' => 'No hay categorías registradas. Cree una para organizar los activos.',
                                    'actionLabel' => 'Nueva Categoría',
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
    @if ($isOpen)
        <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            {{ $category_id ? 'Editar Categoría' : 'Crear Categoría' }}
                        </h5>
                        <button wire:click="closeModal" class="close text-white"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit="store">
                            <div class="form-group">
                                <label>Nombre de la Categoría</label>
                                <input type="text" wire:model="name" class="form-control"
                                    placeholder="Ej: Laptops, Monitores, Impresoras">
                                @include('partials.form-help', ['text' => 'Use un nombre corto y claro para filtrar activos.'])
                                @error('name')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button wire:click="closeModal" class="btn btn-secondary">Cancelar</button>
                        <button wire:click="store" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar
                        </button>



                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('partials.confirm-modal')
</div>
