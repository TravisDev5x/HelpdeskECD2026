<div class="form-group">
	<label for="name">Nombre del permiso</label>
	<input type="text" name="name" id="name" value="{{ old('name', isset($permission) ? $permission->name : '') }}" class="form-control" placeholder="create user" required>
</div>
<div class="form-group">
    <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
</div>
