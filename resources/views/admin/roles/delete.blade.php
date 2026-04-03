<form action="{{ route('admin.roles.destroy', $role->id) }}" method="post">
  @method('DELETE')
  @csrf
  <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de querer eliminar este role?')">
    Eliminar
  </button>
</form>
