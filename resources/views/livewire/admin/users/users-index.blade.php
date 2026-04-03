<div>
    @if(session('generated_password'))
        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
            <h6 class="alert-heading mb-1"><i class="fas fa-key mr-1"></i> Contraseña temporal para <strong>{{ session('created_user_name') }}</strong></h6>
            <p class="mb-1">
                Contraseña: <code id="sessionPasswordText" class="user-select-all font-weight-bold" style="font-size: 1.1em;">{{ session('generated_password') }}</code>
                <button type="button" class="btn btn-xs btn-outline-dark ml-2" onclick="navigator.clipboard.writeText(document.getElementById('sessionPasswordText').textContent).then(()=>{this.innerHTML='<i class=\'fas fa-check\'></i> Copiada';this.classList.add('btn-success');this.classList.remove('btn-outline-dark');})" title="Copiar al portapapeles">
                    <i class="fas fa-copy"></i> Copiar
                </button>
                <button type="button" class="btn btn-xs btn-outline-secondary ml-1" title="Imprimir credencial" onclick="var w=window.open('','_blank','width=400,height=300');w.document.write('<html><head><title>Credencial temporal</title><style>body{font-family:Arial,sans-serif;padding:30px;text-align:center}h3{margin-bottom:5px}.pass{font-size:1.5em;letter-spacing:2px;background:#fff3cd;padding:8px 16px;border-radius:4px;display:inline-block;margin:10px 0}small{color:#666}@media print{button{display:none}}</style></head><body><h3>HelpDesk — Credencial temporal</h3><p><strong>Usuario:</strong> {{ session('created_user_name') }}</p><p class=pass>{{ session('generated_password') }}</p><p><small>Deberá cambiar esta contraseña en su próximo inicio de sesión.</small></p><br><button onclick=window.print()>Imprimir</button></body></html>');w.document.close();">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </p>
            <small class="text-dark">Copia o imprime esta contraseña ahora. <strong>No se volverá a mostrar.</strong> El usuario deberá cambiarla en su primer inicio de sesión.</small>
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    @if($lastGeneratedPassword)
        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
            <h6 class="alert-heading mb-1"><i class="fas fa-key mr-1"></i> Contraseña temporal para <strong>{{ $lastCreatedUserName }}</strong></h6>
            <p class="mb-1">
                Contraseña: <code id="tempPasswordText" class="user-select-all font-weight-bold" style="font-size: 1.1em;">{{ $lastGeneratedPassword }}</code>
                <button type="button" class="btn btn-xs btn-outline-dark ml-2" onclick="navigator.clipboard.writeText(document.getElementById('tempPasswordText').textContent).then(()=>{this.innerHTML='<i class=\'fas fa-check\'></i> Copiada';this.classList.add('btn-success');this.classList.remove('btn-outline-dark');})" title="Copiar al portapapeles">
                    <i class="fas fa-copy"></i> Copiar
                </button>
                <button type="button" class="btn btn-xs btn-outline-secondary ml-1" title="Imprimir credencial" onclick="var w=window.open('','_blank','width=400,height=300');w.document.write('<html><head><title>Credencial temporal</title><style>body{font-family:Arial,sans-serif;padding:30px;text-align:center}h3{margin-bottom:5px}.pass{font-size:1.5em;letter-spacing:2px;background:#fff3cd;padding:8px 16px;border-radius:4px;display:inline-block;margin:10px 0}small{color:#666}@media print{button{display:none}}</style></head><body><h3>HelpDesk — Credencial temporal</h3><p><strong>Usuario:</strong> {{ $lastCreatedUserName }}</p><p class=pass>{{ $lastGeneratedPassword }}</p><p><small>Deberá cambiar esta contraseña en su próximo inicio de sesión.</small></p><br><button onclick=window.print()>Imprimir</button></body></html>');w.document.close();">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </p>
            <small class="text-dark">Copia o imprime esta contraseña ahora. <strong>No se volverá a mostrar.</strong> El usuario deberá cambiarla en su primer inicio de sesión.</small>
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar" wire:click="$set('lastGeneratedPassword', null)"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h3 class="card-title mb-0">Listado de usuarios</h3>
                    @if($viewMode === 'active' && $onlyPending)
                        <small class="text-muted">Mostrando solo usuarios pendientes de checklist.</small>
                    @elseif($viewMode === 'trashed')
                        <small class="text-muted">Mostrando usuarios dados de baja (papelera).</small>
                    @endif
                </div>
                <div class="d-flex flex-wrap align-items-center">
                    @can('read users')
                    <button type="button" class="btn btn-outline-success btn-sm mr-2 mb-2 mb-md-0" wire:click="exportUsers">
                        <i class="fas fa-file-export"></i> Exportar usuarios
                    </button>
                    @endcan
                    @can('update user')
                    <div class="mr-2 mb-2 mb-md-0">
                        <input type="file" class="form-control form-control-sm @error('rhFile') is-invalid @enderror" wire:model="rhFile" accept=".xlsx,.xls,.csv,.txt">
                        @error('rhFile')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mr-2 mb-2 mb-md-0" wire:click="importRhList" wire:loading.attr="disabled" wire:target="importRhList,rhFile">
                        <i class="fas fa-file-import"></i> Importar RH
                    </button>
                    @endcan
                    @can('create user')
                    <button type="button" class="btn btn-primary btn-sm mb-2 mb-md-0" wire:click="openCreateModal">
                        <i class="fa fa-plus"></i> Crear usuario
                    </button>
                    @endcan
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="mb-3">
                <div class="btn-group btn-group-sm" role="group" aria-label="Vista de usuarios">
                    <button type="button" class="btn {{ $viewMode === 'active' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="setViewMode('active')">
                        Activos ({{ $activeCount }})
                    </button>
                    <button type="button" class="btn {{ $viewMode === 'trashed' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="setViewMode('trashed')">
                        Bajas ({{ $trashedCount }})
                    </button>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" placeholder="Buscar usuario, correo, departamento o puesto..." wire:model.live.debounce.300ms="search">
                </div>
                <div class="col-md-3">
                    <select class="custom-select" wire:model.live="perPage">
                        <option value="10">10 por página</option>
                        <option value="20">20 por página</option>
                        <option value="25">25 por página</option>
                        <option value="50">50 por página</option>
                        <option value="100">100 por página</option>
                    </select>
                </div>
                @can('delete user')
                <div class="col-md-3 text-md-right mt-2 mt-md-0">
                    <div class="d-flex justify-content-md-end align-items-center">
                        <span class="badge badge-light mr-2">Seleccionados: {{ count($selectedIds) }}</span>
                        <button type="button" class="btn btn-outline-secondary btn-sm mr-1" wire:click="clearSelection" @disabled(empty($selectedIds))>
                            Limpiar
                        </button>
                        @if($viewMode === 'active')
                            <button type="button" class="btn btn-outline-danger btn-sm" wire:click="openBulkDeleteModal" @disabled(empty($selectedIds))>
                                <i class="fas fa-user-minus mr-1"></i> Baja masiva
                            </button>
                        @else
                            <button type="button" class="btn btn-outline-success btn-sm" wire:click="restoreSelected" wire:confirm="¿Restaurar los usuarios seleccionados?" @disabled(empty($selectedIds))>
                                <i class="fas fa-trash-restore mr-1"></i> Restaurar
                            </button>
                        @endif
                    </div>
                </div>
                @endcan
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            @can('delete user')
                                <th style="width: 34px;">
                                    <input
                                        type="checkbox"
                                        wire:click="toggleSelectPage(@js($users->pluck('id')->values()->all()))"
                                        @checked(collect($users->pluck('id')->values()->all())->every(fn($id) => in_array($id, $selectedIds)))
                                    >
                                </th>
                            @endcan
                            <th>ID</th>
                            <th>N. empleado</th>
                            <th>Nombre</th>
                            <th>Status</th>
                            <th>Email</th>
                            <th>Extensión</th>
                            <th>Teléfono</th>
                            <th>Departamento</th>
                            <th>Puesto</th>
                            <th>Campaña</th>
                            <th>Área</th>
                            <th>Fecha registro</th>
                            <th>Rol</th>
                            {{-- @can('update user')
                                <th>Checklist</th>
                            @endcan --}}
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            @php
                                $nombreCompleto = trim(implode(' ', array_filter([$user->name, $user->ap_paterno, $user->ap_materno])));
                                $rol = optional($user->roles->first())->name;
                            @endphp
                            <tr>
                                @can('delete user')
                                    <td>
                                        <input type="checkbox" wire:model.live="selectedIds" value="{{ $user->id }}">
                                    </td>
                                @endcan
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->usuario }}</td>
                                <td>{{ $nombreCompleto ?: $user->name }}</td>
                                <td>
                                    @if($user->deleted_at)
                                        <span class="text-danger">Baja</span>
                                    @else
                                        {!! $rol === 'Suspendido' ? '<span class="text-danger">Inactivo</span>' : 'Activo' !!}
                                    @endif
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->position?->extension }}</td>
                                <td>{{ $user->phone }}</td>
                                <td>{{ $user->department?->name }}</td>
                                <td>{{ $user->position?->name }}</td>
                                <td>{{ $user->campaign?->name }}</td>
                                <td>{{ $user->position?->area }}</td>
                                <td>{{ optional($user->created_at)->format('d/m/Y') }}</td>
                                <td>{{ $rol ?? 'N/A' }}</td>
                                {{-- @can('update user')
                                @if($viewMode === 'active')
                                    <td>
                                        @if($user->has_checklist)
                                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-xs btn-info" title="Checklist completo">
                                                <i class="fa fa-check-square"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-xs btn-warning" title="Checklist pendiente">
                                                <i class="fa fa-window-close"></i>
                                            </a>
                                        @endif
                                    </td>
                                @else
                                    <td>
                                        <span class="text-muted">—</span>
                                    </td>
                                @endif
                                @endcan --}}
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-xs btn-outline-secondary" wire:click="openQuickView({{ $user->id }})" data-toggle="tooltip" data-placement="top" title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @can('update user')
                                        @if($viewMode === 'active')
                                            <button type="button" class="btn btn-xs btn-info" wire:click="openEditModal({{ $user->id }})" data-toggle="tooltip" data-placement="top" title="Editar usuario">
                                                <i class="fas fa-pencil-alt"></i>
                                            </button>
                                        @endif
                                        @endcan
                                        @hasanyrole('Admin|Soporte')
                                        @if($viewMode === 'active')
                                            <button type="button" class="btn btn-xs btn-warning" wire:click="resetPassword({{ $user->id }})" wire:confirm="Se generará una nueva contraseña temporal para {{ $user->usuario }}. El usuario deberá cambiarla al iniciar sesión. ¿Continuar?" data-toggle="tooltip" data-placement="top" title="Restablecer contraseña">
                                                <i class="fas fa-key"></i>
                                            </button>
                                        @endif
                                        @endhasanyrole
                                        @can('delete user')
                                            @if($viewMode === 'active')
                                                <button type="button" class="btn btn-xs btn-danger" wire:click="openDeleteModal({{ $user->id }})" data-toggle="tooltip" data-placement="top" title="Dar de baja">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-xs btn-success" wire:click="restoreUser({{ $user->id }})" wire:confirm="¿Restaurar este usuario?" data-toggle="tooltip" data-placement="top" title="Restaurar usuario">
                                                    <i class="fas fa-trash-restore"></i>
                                                </button>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="16" class="text-center text-muted py-3">No hay usuarios para mostrar en esta vista.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    @can('create user')
    <div wire:ignore.self class="modal fade" id="userCreateModal" tabindex="-1" role="dialog" aria-labelledby="userCreateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="createUser">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="userCreateModalLabel">Crear usuario</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" wire:click="cancelCreateUser">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="card card-outline card-primary mb-3">
                            <div class="card-header py-2"><strong>Datos personales</strong></div>
                            <div class="card-body pb-1">
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Nombre</label>
                                        <input type="text" class="form-control @error('createName') is-invalid @enderror" wire:model="createName" placeholder="Ej. Juan">
                                        @error('createName')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Apellido paterno</label>
                                        <input type="text" class="form-control @error('createApPaterno') is-invalid @enderror" wire:model="createApPaterno" placeholder="Ej. Pérez">
                                        @error('createApPaterno')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Apellido materno</label>
                                        <input type="text" class="form-control @error('createApMaterno') is-invalid @enderror" wire:model="createApMaterno" placeholder="Ej. López">
                                        @error('createApMaterno')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>No. empleado</label>
                                        <input type="text" class="form-control @error('createUsuario') is-invalid @enderror" wire:model="createUsuario" placeholder="Ej. ECD00123">
                                        @error('createUsuario')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Teléfono</label>
                                        <input type="text" class="form-control @error('createPhone') is-invalid @enderror" wire:model="createPhone" maxlength="10" placeholder="Ej. 5512345678">
                                        @error('createPhone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Email</label>
                                        <input type="email" class="form-control @error('createEmail') is-invalid @enderror" wire:model="createEmail" placeholder="Ej. juan.perez@ecd.mx">
                                        @error('createEmail')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-outline card-secondary mb-3">
                            <div class="card-header py-2"><strong>Estructura organizacional</strong></div>
                            <div class="card-body pb-1">
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Área</label>
                                        <select class="custom-select @error('createAreaId') is-invalid @enderror" wire:model.live="createAreaId">
                                            <option value="">Seleccione un área...</option>
                                            @foreach($areas as $area)
                                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('createAreaId')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Departamento</label>
                                        <select class="custom-select @error('createDepartmentId') is-invalid @enderror" wire:model.live="createDepartmentId">
                                            <option value="">Seleccione un departamento...</option>
                                            @foreach($departments as $department)
                                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('createDepartmentId')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Puesto</label>
                                        <select class="custom-select @error('createPositionId') is-invalid @enderror" wire:model="createPositionId">
                                            <option value="">Seleccione un puesto...</option>
                                            @foreach($positions as $position)
                                                <option value="{{ $position->id }}">{{ $position->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('createPositionId')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Campaña</label>
                                        <select class="custom-select @error('createCampaignId') is-invalid @enderror" wire:model="createCampaignId">
                                            <option value="">Sin campaña</option>
                                            @foreach($campaigns as $campaign)
                                                <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('createCampaignId')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group col-md-8">
                                        <label>Sedes</label>
                                        <select id="createSedesSelect" class="form-control @error('createSedes') is-invalid @enderror @error('createSedes.*') is-invalid @enderror" wire:model="createSedes" multiple size="5">
                                            @foreach($sedes as $sede)
                                                <option value="{{ $sede->id }}">{{ $sede->sede }}</option>
                                            @endforeach
                                        </select>
                                        @error('createSedes')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                        @error('createSedes.*')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                        <small class="text-muted">Mantén presionada Ctrl para seleccionar varias sedes.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($canSelectRole)
                        <div class="card card-outline card-info mb-0">
                            <div class="card-header py-2"><strong>Acceso</strong></div>
                            <div class="card-body pb-1">
                                <div class="row">
                                    <div class="form-group col-md-6 mb-2">
                                        <label>Rol</label>
                                        <select class="custom-select @error('createRoleId') is-invalid @enderror" wire:model="createRoleId">
                                            <option value="">Seleccione un rol...</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}">{{ $role->name }} ({{ $role->description ?? 'Sin descripción' }})</option>
                                            @endforeach
                                        </select>
                                        @error('createRoleId')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="col-md-6 mb-2 d-flex align-items-end">
                                        <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Se generará una contraseña segura aleatoria.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                            <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Se generará una contraseña segura aleatoria.</small>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" wire:click="cancelCreateUser">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    @can('update user')
    <div wire:ignore.self class="modal fade" id="userEditModal" tabindex="-1" role="dialog" aria-labelledby="userEditModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="updateUser">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="userEditModalLabel">Editar usuario</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" wire:click="cancelEditUser">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="card card-outline card-primary mb-3">
                            <div class="card-header py-2"><strong>Datos personales</strong></div>
                            <div class="card-body pb-1">
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Nombre</label>
                                        <input type="text" class="form-control @error('editName') is-invalid @enderror" wire:model="editName" placeholder="Ej. Juan">
                                        @error('editName')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Apellido paterno</label>
                                        <input type="text" class="form-control @error('editApPaterno') is-invalid @enderror" wire:model="editApPaterno" placeholder="Ej. Pérez">
                                        @error('editApPaterno')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Apellido materno</label>
                                        <input type="text" class="form-control @error('editApMaterno') is-invalid @enderror" wire:model="editApMaterno" placeholder="Ej. López">
                                        @error('editApMaterno')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>No. empleado</label>
                                        <input type="text" class="form-control @error('editUsuario') is-invalid @enderror" wire:model="editUsuario" placeholder="Ej. ECD00123">
                                        @error('editUsuario')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Teléfono</label>
                                        <input type="text" class="form-control @error('editPhone') is-invalid @enderror" wire:model="editPhone" maxlength="10" placeholder="Ej. 5512345678">
                                        @error('editPhone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Email</label>
                                        <input type="email" class="form-control @error('editEmail') is-invalid @enderror" wire:model="editEmail" placeholder="Ej. juan.perez@ecd.mx">
                                        @error('editEmail')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-outline card-secondary mb-3">
                            <div class="card-header py-2"><strong>Estructura organizacional</strong></div>
                            <div class="card-body pb-1">
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Área</label>
                                        <select class="custom-select @error('editAreaId') is-invalid @enderror" wire:model.live="editAreaId">
                                            <option value="">Seleccione un área...</option>
                                            @foreach($areas as $area)
                                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('editAreaId')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Departamento</label>
                                        <select class="custom-select @error('editDepartmentId') is-invalid @enderror" wire:model.live="editDepartmentId">
                                            <option value="">Seleccione un departamento...</option>
                                            @foreach($editDepartments as $department)
                                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('editDepartmentId')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Puesto</label>
                                        <select class="custom-select @error('editPositionId') is-invalid @enderror" wire:model="editPositionId">
                                            <option value="">Seleccione un puesto...</option>
                                            @foreach($editPositions as $position)
                                                <option value="{{ $position->id }}">{{ $position->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('editPositionId')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Campaña</label>
                                        <select class="custom-select @error('editCampaignId') is-invalid @enderror" wire:model="editCampaignId">
                                            <option value="">Sin campaña</option>
                                            @foreach($campaigns as $campaign)
                                                <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('editCampaignId')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group col-md-8">
                                        <label>Sedes</label>
                                        <select id="editSedesSelect" class="form-control @error('editSedes') is-invalid @enderror @error('editSedes.*') is-invalid @enderror" wire:model="editSedes" multiple size="5">
                                            @foreach($sedes as $sede)
                                                <option value="{{ $sede->id }}">{{ $sede->sede }}</option>
                                            @endforeach
                                        </select>
                                        @error('editSedes')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                        @error('editSedes.*')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                        <small class="text-muted">Mantén presionada Ctrl para seleccionar varias sedes.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-outline card-info mb-0">
                            <div class="card-header py-2"><strong>Acceso y seguridad</strong></div>
                            <div class="card-body pb-1">
                                <div class="row">
                                    @if($canSelectRole)
                                    <div class="form-group col-md-6">
                                        <label>Rol</label>
                                        <select class="custom-select @error('editRoleId') is-invalid @enderror" wire:model="editRoleId">
                                            <option value="">Seleccione un rol...</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}">{{ $role->name }} ({{ $role->description ?? 'Sin descripción' }})</option>
                                            @endforeach
                                        </select>
                                        @error('editRoleId')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                    @endif
                                    @hasanyrole('Admin|Soporte')
                                    <div class="form-group col-md-6">
                                        <label>Nueva contraseña (opcional)</label>
                                        <input type="password" class="form-control @error('editPassword') is-invalid @enderror" wire:model="editPassword" placeholder="Mínimo 8 caracteres">
                                        @error('editPassword')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Confirmar contraseña</label>
                                        <input type="password" class="form-control @error('editPasswordConfirmation') is-invalid @enderror" wire:model="editPasswordConfirmation" placeholder="Repite la contraseña">
                                        @error('editPasswordConfirmation')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                    @endhasanyrole
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" wire:click="cancelEditUser">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    <div wire:ignore.self class="modal fade" id="userQuickViewModal" tabindex="-1" role="dialog" aria-labelledby="userQuickViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title" id="userQuickViewModalLabel"><i class="fas fa-user mr-2"></i>Detalle del usuario</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    @if(!empty($quickView))
                        <div class="row no-gutters">
                            <div class="col-md-4 bg-light border-bottom border-md-right border-md-bottom-0 p-4 text-center">
                                <img src="{{ $quickView['avatar_url'] ?? asset('uploads/avatars/default.png') }}" alt="" class="rounded-circle img-fluid border shadow-sm mb-3" style="max-width: 120px; max-height: 120px; object-fit: cover;">
                                <h6 class="font-weight-bold mb-1">{{ $quickView['nombre_completo'] ?? '—' }}</h6>
                                <p class="small text-muted mb-2">{{ $quickView['usuario'] ?? '—' }}</p>
                            </div>
                            <div class="col-md-8 p-4">
                                <dl class="row small mb-0">
                                    <dt class="col-sm-4 text-muted">Correo</dt><dd class="col-sm-8">{{ $quickView['email'] ?? '—' }}</dd>
                                    <dt class="col-sm-4 text-muted">Teléfono</dt><dd class="col-sm-8">{{ $quickView['phone'] ?? '—' }}</dd>
                                    <dt class="col-sm-4 text-muted">Departamento</dt><dd class="col-sm-8">{{ $quickView['department'] ?? '—' }}</dd>
                                    <dt class="col-sm-4 text-muted">Puesto</dt><dd class="col-sm-8">{{ $quickView['position'] ?? '—' }}</dd>
                                    <dt class="col-sm-4 text-muted">Área</dt><dd class="col-sm-8">{{ $quickView['position_area'] ?? '—' }}</dd>
                                    <dt class="col-sm-4 text-muted">Campaña</dt><dd class="col-sm-8">{{ $quickView['campaign'] ?? '—' }}</dd>
                                    <dt class="col-sm-4 text-muted">Sede</dt><dd class="col-sm-8">{{ $quickView['sede'] ?? '—' }}</dd>
                                    <dt class="col-sm-4 text-muted">Alta</dt><dd class="col-sm-8">{{ $quickView['created_at'] ?? '—' }}</dd>
                                    <dt class="col-sm-4 text-muted">Roles</dt>
                                    <dd class="col-sm-8">
                                        @forelse(($quickView['roles'] ?? []) as $roleName)
                                            <span class="badge badge-secondary mr-1 mb-1">{{ $roleName }}</span>
                                        @empty
                                            <span class="text-muted">—</span>
                                        @endforelse
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    @if(!empty($quickView['profile_url']))
                        <a href="{{ $quickView['profile_url'] }}" class="btn btn-outline-primary" target="_blank" rel="noopener">
                            <i class="fas fa-external-link-alt mr-1"></i> Ver ficha completa
                        </a>
                    @endif
                    @can('update user')
                        @if(!empty($selectedUserId) && $viewMode === 'active')
                            <button type="button" class="btn btn-primary" data-dismiss="modal" wire:click="openEditModal({{ $selectedUserId }})">
                                <i class="fas fa-pencil-alt mr-1"></i> Editar
                            </button>
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>

    @can('delete user')
    <div wire:ignore.self class="modal fade" id="userDeleteModal" tabindex="-1" role="dialog" aria-labelledby="userDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form wire:submit.prevent="performDeleteUser">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userDeleteModalLabel">Eliminar usuario</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="fecha_baja">Fecha de baja</label>
                            <input type="date" id="fecha_baja" class="form-control @error('deleteFechaBaja') is-invalid @enderror" wire:model="deleteFechaBaja" required>
                            @error('deleteFechaBaja')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group mb-0">
                            <label for="motivo_baja">Observaciones</label>
                            <textarea id="motivo_baja" class="form-control @error('deleteMotivoBaja') is-invalid @enderror" rows="3" maxlength="255" placeholder="Ej. Baja por rotación interna" wire:model="deleteMotivoBaja" required></textarea>
                            @error('deleteMotivoBaja')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger" wire:loading.attr="disabled" wire:target="performDeleteUser">Eliminar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="userBulkDeleteModal" tabindex="-1" role="dialog" aria-labelledby="userBulkDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form wire:submit.prevent="performBulkDelete">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userBulkDeleteModalLabel">Baja masiva de usuarios</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted">Usuarios seleccionados: <strong>{{ count($selectedIds) }}</strong></p>
                        <div class="form-group">
                            <label for="bulk_fecha_baja">Fecha de baja</label>
                            <input type="date" id="bulk_fecha_baja" class="form-control @error('bulkFechaBaja') is-invalid @enderror" wire:model="bulkFechaBaja" required>
                            @error('bulkFechaBaja')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group mb-0">
                            <label for="bulk_motivo_baja">Observaciones</label>
                            <textarea id="bulk_motivo_baja" class="form-control @error('bulkMotivoBaja') is-invalid @enderror" rows="3" maxlength="255" placeholder="Ej. Fin de proyecto o cambio de operación" wire:model="bulkMotivoBaja" required></textarea>
                            @error('bulkMotivoBaja')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger" onclick="return confirm('¿Confirmas la baja masiva?')">Dar de baja</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endcan
</div>

@push('scripts')
<script>
    window.addEventListener('open-user-quick-view', function () {
        $('#userQuickViewModal').modal('show');
    });

    window.addEventListener('open-user-delete-modal', function () {
        $('#userDeleteModal').modal('show');
    });

    window.addEventListener('close-user-delete-modal', function () {
        $('#userDeleteModal').modal('hide');
    });

    window.addEventListener('open-user-bulk-delete-modal', function () {
        $('#userBulkDeleteModal').modal('show');
    });

    window.addEventListener('close-user-bulk-delete-modal', function () {
        $('#userBulkDeleteModal').modal('hide');
    });

    window.addEventListener('open-user-create-modal', function () {
        $('#userCreateModal').modal('show');
    });

    window.addEventListener('close-user-create-modal', function () {
        $('#userCreateModal').modal('hide');
    });

    window.addEventListener('open-user-edit-modal', function () {
        $('#userEditModal').modal('show');
    });

    window.addEventListener('close-user-edit-modal', function () {
        $('#userEditModal').modal('hide');
    });

    function initTooltips() {
        $('[data-toggle="tooltip"]').tooltip({ trigger: 'hover' });
    }
    $(initTooltips);
    document.addEventListener('livewire:init', function () {
        Livewire.hook('morph.updated', () => {
            $('.tooltip').remove();
            initTooltips();
        });
    });
</script>
@endpush

@push('styles')
<style>
    #createSedesSelect {
        min-height: 140px;
    }

    #editSedesSelect {
        min-height: 140px;
    }

    #createSedesSelect option,
    #editSedesSelect option {
        padding: 4px 8px;
    }

    #createSedesSelect option:checked,
    #editSedesSelect option:checked {
        background: #007bff linear-gradient(0deg, #007bff 0%, #007bff 100%);
        color: #fff;
    }

    body.dark-mode #createSedesSelect,
    body.dark-mode #editSedesSelect {
        background-color: #343a40 !important;
        color: #fff !important;
        border-color: #6c757d !important;
    }

    body.dark-mode #createSedesSelect option,
    body.dark-mode #editSedesSelect option {
        background-color: #343a40;
        color: #fff;
    }

    body.dark-mode #createSedesSelect option:checked,
    body.dark-mode #editSedesSelect option:checked {
        background: #17a2b8 linear-gradient(0deg, #17a2b8 0%, #17a2b8 100%);
        color: #fff;
    }
</style>
@endpush

