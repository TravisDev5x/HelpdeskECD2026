<form action="{{ route('admin.permissions.destroy', $permission->id) }}" method="post">
  @method('DELETE')
  @csrf
  <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de querer eliminar este permiso?')">
    Eliminar
  </button>
</form>
