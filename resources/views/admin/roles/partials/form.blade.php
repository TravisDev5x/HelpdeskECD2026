@php
    $permissionIds = old('permissions', isset($role) ? $role->permissions->pluck('id')->all() : []);
@endphp
<div class="form-group">
	<label for="name">Nombre del rol</label>
	<input type="text" name="name" id="name" value="{{ old('name', isset($role) ? $role->name : '') }}" class="form-control" required>
</div>
<hr>
@livewire(
    'admin.roles.permissions-selector',
    ['selected' => $permissionIds],
    key('roles-permissions-' . (isset($role) ? $role->id : 'create') . '-' . md5(json_encode($permissionIds)))
)
<div class="form-group">
    <button type="submit" class="btn btn-sm btn-primary">Guardar</button>

    <button type="button" onclick="window.location='{{ route('admin.roles.index') }}'" class="btn btn-sm btn-danger">Cerrar</button>
</div>
