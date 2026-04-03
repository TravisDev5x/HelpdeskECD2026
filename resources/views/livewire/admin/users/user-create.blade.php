<div>
    <div class="mb-3">
        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Volver al listado</a>
    </div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>{{ __('Nuevo usuario') }}</span>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="save">
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
                                <label>Teléfono <span class="text-muted font-weight-normal">(opcional, único)</span></label>
                                <input type="text" class="form-control @error('createPhone') is-invalid @enderror" wire:model="createPhone" maxlength="10" placeholder="Ej. 5512345678">
                                @error('createPhone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label>Correo electrónico</label>
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
                                <label>Área <span class="text-muted font-weight-normal">(opcional)</span></label>
                                <select class="custom-select @error('createAreaId') is-invalid @enderror" wire:model="createAreaId">
                                    <option value="">Sin área</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                                    @endforeach
                                </select>
                                @error('createAreaId')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label>Departamento <span class="text-muted font-weight-normal">(opcional)</span></label>
                                <select class="custom-select @error('createDepartmentId') is-invalid @enderror" wire:model="createDepartmentId">
                                    <option value="">Sin departamento</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                @error('createDepartmentId')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label>Puesto <span class="text-muted font-weight-normal">(opcional)</span></label>
                                <select class="custom-select @error('createPositionId') is-invalid @enderror" wire:model="createPositionId">
                                    <option value="">Sin puesto</option>
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
                                <select class="form-control @error('createSedes') is-invalid @enderror @error('createSedes.*') is-invalid @enderror" wire:model="createSedes" multiple size="5">
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
                <div class="card card-outline card-info mb-3">
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
                                <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Se generará una contraseña segura aleatoria. Se mostrará una sola vez después de guardar.</small>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                    <p class="text-muted small mb-3"><i class="fas fa-info-circle mr-1"></i>Se generará una contraseña segura aleatoria. Se mostrará una sola vez después de guardar.</p>
                @endif

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">Guardar usuario</span>
                        <span wire:loading wire:target="save">Guardando…</span>
                    </button>
                    <button type="button" class="btn btn-secondary" wire:click="cancel">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
