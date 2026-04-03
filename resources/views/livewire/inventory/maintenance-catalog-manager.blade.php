<div>
    @include('partials.breadcrumb', ['items' => [
        ['text' => 'Inicio', 'url' => route('home')],
        ['text' => 'Inventario V2', 'url' => route('inventory.v2.index')],
        ['text' => 'Catálogos de mantenimiento', 'url' => null],
    ]])

    <div class="row">
        <div class="col-lg-6">
            <div class="card card-outline card-secondary shadow-sm">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <h3 class="card-title text-sm mb-0"><i class="fas fa-building mr-1"></i> Origen del mantenimiento</h3>
                    <button type="button" wire:click="create('origin')" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Nuevo
                    </button>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm table-striped mb-0 text-sm">
                        <thead class="bg-light"><tr><th>Código</th><th>Nombre</th><th class="text-center">Orden</th><th class="text-center">Activo</th><th class="text-right" style="width:100px">Acciones</th></tr></thead>
                        <tbody>
                            @forelse($origins as $row)
                                <tr>
                                    <td class="font-monospace">{{ $row->code }}</td>
                                    <td>{{ $row->name }}</td>
                                    <td class="text-center">{{ $row->sort_order }}</td>
                                    <td class="text-center">{!! $row->is_active ? '<span class="badge badge-success">Sí</span>' : '<span class="badge badge-secondary">No</span>' !!}</td>
                                    <td class="text-right">
                                        <button type="button" class="btn btn-xs btn-warning" wire:click="edit('origin', {{ $row->id }})" title="Editar"><i class="fas fa-edit"></i></button>
                                        <button type="button" class="btn btn-xs btn-danger" wire:click="openConfirmDelete('origin', {{ $row->id }})" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted text-center py-3">Sin registros. Ejecute el seeder o cree el primero.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card card-outline card-secondary shadow-sm">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <h3 class="card-title text-sm mb-0"><i class="fas fa-tasks mr-1"></i> Modalidad del mantenimiento</h3>
                    <button type="button" wire:click="create('modality')" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Nuevo
                    </button>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm table-striped mb-0 text-sm">
                        <thead class="bg-light"><tr><th>Código</th><th>Nombre</th><th class="text-center">Orden</th><th class="text-center">Activo</th><th class="text-right" style="width:100px">Acciones</th></tr></thead>
                        <tbody>
                            @forelse($modalities as $row)
                                <tr>
                                    <td class="font-monospace">{{ $row->code }}</td>
                                    <td>{{ $row->name }}</td>
                                    <td class="text-center">{{ $row->sort_order }}</td>
                                    <td class="text-center">{!! $row->is_active ? '<span class="badge badge-success">Sí</span>' : '<span class="badge badge-secondary">No</span>' !!}</td>
                                    <td class="text-right">
                                        <button type="button" class="btn btn-xs btn-warning" wire:click="edit('modality', {{ $row->id }})" title="Editar"><i class="fas fa-edit"></i></button>
                                        <button type="button" class="btn btn-xs btn-danger" wire:click="openConfirmDelete('modality', {{ $row->id }})" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted text-center py-3">Sin registros.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <p class="text-muted small mt-2 mb-0">
        <i class="fas fa-info-circle mr-1"></i> Los códigos se normalizan a mayúsculas (ej. <code>mi_codigo</code> → <code>MI_CODIGO</code>). No se puede eliminar un valor si hay mantenimientos que lo usan; use “Activo = No” para ocultarlo en formularios.
    </p>

    @if ($isOpen)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5); z-index: 1050;" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header py-2 bg-primary text-white">
                        <h5 class="modal-title text-sm font-weight-bold">
                            {{ $itemId ? 'Editar' : 'Nuevo' }}
                            {{ $editCatalog === 'modality' ? 'modalidad' : 'origen' }}
                        </h5>
                        <button type="button" class="close text-white" wire:click="closeModal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Código *</label>
                            <input type="text" class="form-control form-control-sm" wire:model="code" placeholder="Ej: INTERNO, PREVENTIVO" @if($itemId) title="Puede cambiar el código si ningún proceso externo depende de él" @endif>
                            @include('partials.form-help', ['text' => 'Único. Letras, números y guión bajo; se guarda en MAYÚSCULAS.'])
                            @error('code') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Nombre *</label>
                            <input type="text" class="form-control form-control-sm" wire:model="name" placeholder="Texto para pantallas y reportes">
                            @error('name') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Orden</label>
                            <input type="number" class="form-control form-control-sm" wire:model="sort_order" min="0">
                            @error('sort_order') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="cat-active" wire:model="is_active">
                            <label class="custom-control-label text-sm" for="cat-active">Activo (visible en altas de mantenimiento)</label>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="closeModal">Cancelar</button>
                        <button type="button" class="btn btn-primary btn-sm font-weight-bold" wire:click="store" wire:loading.attr="disabled">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('partials.confirm-modal')
</div>
