<div>
    <div class="mb-3">
        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Volver al listado</a>
    </div>
    <div class="card">
        <div class="card-header">Datos personales</div>
        <div class="card-body">
            <form wire:submit.prevent="save">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="editName">Nombre</label>
                        <input id="editName" type="text" wire:model="editName" class="form-control @error('editName') is-invalid @enderror">
                        @error('editName')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label for="editApPaterno">Apellido Paterno</label>
                        <input id="editApPaterno" type="text" wire:model="editApPaterno" class="form-control @error('editApPaterno') is-invalid @enderror">
                        @error('editApPaterno')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label for="editApMaterno">Apellido Materno</label>
                        <input id="editApMaterno" type="text" wire:model="editApMaterno" class="form-control @error('editApMaterno') is-invalid @enderror">
                        @error('editApMaterno')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label for="editUsuario">No. empleado</label>
                        <input id="editUsuario" type="text" wire:model="editUsuario" class="form-control @error('editUsuario') is-invalid @enderror" required>
                        @error('editUsuario')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label for="editPhone">Teléfono</label>
                        <input id="editPhone" type="text" wire:model="editPhone" maxlength="10" class="form-control @error('editPhone') is-invalid @enderror" placeholder="10 dígitos">
                        @error('editPhone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label for="editEmail">Email</label>
                        <input id="editEmail" type="email" wire:model="editEmail" class="form-control @error('editEmail') is-invalid @enderror">
                        @error('editEmail')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label for="editAreaId">Área</label>
                        <select id="editAreaId" class="custom-select @error('editAreaId') is-invalid @enderror" wire:model.live="editAreaId" required>
                            <option value="" disabled>Seleccione un área...</option>
                            @foreach ($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                            @endforeach
                        </select>
                        @error('editAreaId')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label for="editDepartmentId">Departamento</label>
                        <select id="editDepartmentId" class="custom-select @error('editDepartmentId') is-invalid @enderror" wire:model.live="editDepartmentId" required>
                            <option value="" disabled>Seleccione un departamento...</option>
                            @foreach ($editDepartments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                        @error('editDepartmentId')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label for="editPositionId">Puesto</label>
                        <select id="editPositionId" class="custom-select @error('editPositionId') is-invalid @enderror" wire:model="editPositionId" required>
                            <option value="" disabled>Seleccione un puesto...</option>
                            @foreach ($editPositions as $position)
                                <option value="{{ $position->id }}">{{ $position->name }}</option>
                            @endforeach
                        </select>
                        @error('editPositionId')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label for="editCampaignId">Campaña</label>
                        <select id="editCampaignId" class="custom-select @error('editCampaignId') is-invalid @enderror" wire:model="editCampaignId">
                            <option value="">Sin campaña</option>
                            @foreach ($campaigns as $campaign)
                                <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                            @endforeach
                        </select>
                        @error('editCampaignId')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label for="editSedes">Sedes</label>
                        <select id="editSedes" class="form-control @error('editSedes') is-invalid @enderror @error('editSedes.*') is-invalid @enderror" wire:model="editSedes" multiple size="5">
                            @foreach($sedes as $sede)
                                <option value="{{ $sede->id }}">{{ $sede->sede }}</option>
                            @endforeach
                        </select>
                        @error('editSedes')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        @error('editSedes.*')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        <small class="text-muted">Mantén presionada Ctrl para seleccionar varias sedes.</small>
                    </div>
                    @if($canSelectRole)
                    <div class="form-group col-md-6">
                        <label for="editRoleId">Rol</label>
                        <select id="editRoleId" class="custom-select @error('editRoleId') is-invalid @enderror" wire:model="editRoleId">
                            <option value="" disabled>Seleccione un rol...</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }} ({{ $role->description ?? 'Sin descripción' }})</option>
                            @endforeach
                        </select>
                        @error('editRoleId')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    @endif
                    @hasanyrole('Admin|Soporte')
                    <div class="form-group col-md-6">
                        <label for="editPassword">Contraseña</label>
                        <input id="editPassword" type="password" wire:model="editPassword" class="form-control @error('editPassword') is-invalid @enderror" placeholder="Dejar en blanco para no cambiar">
                        <span class="text-muted small">Dejar en blanco para no cambiar la contraseña</span>
                        @error('editPassword')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label for="editPasswordConfirmation">Repite la contraseña</label>
                        <input id="editPasswordConfirmation" type="password" wire:model="editPasswordConfirmation" class="form-control @error('editPasswordConfirmation') is-invalid @enderror">
                        @error('editPasswordConfirmation')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    @endhasanyrole

                    @can('check activos')
                        @if($user->certification == 1)
                            <div class="form-group col-12">
                                <label class="d-block">Activos / checklist</label>
                                <div class="row">
                                    @foreach($assets as $asset)
                                        <div class="form-group col-6 col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="asset-{{ $asset->id }}" wire:model.live="assetCheckbox.{{ $asset->id }}">
                                                <label class="form-check-label" for="asset-{{ $asset->id }}">{{ $asset->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endcan

                    <div class="form-group col-md-4">
                        <button type="submit" class="btn btn-primary btn-block" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">Actualizar usuario</span>
                            <span wire:loading wire:target="save">Guardando…</span>
                        </button>
                    </div>
                    <div class="form-group col-md-4">
                        <button type="button" class="btn btn-danger btn-block" wire:click="cancel">Cancelar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
