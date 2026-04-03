<div class="row ">
    <div class="form-group col-md-4">
        <label for="actividad" class="">Nombre Actividad: </label>
        <input type="text" class="text form-control" id="actividad" name="actividad" required value="{{ $bitacora->actividad ?? '' }}" {{Route::current()->getName() == 'admin.bitacora.show' ? 'disabled' : ''}}>
    </div>
    <div class="form-group col-md-4 ">
        <label for="duracion" class="">Duración (Horas): </label>
        <input type="number" class=" form-control" id="duracion" name="duracion" min='0' step="0.1" required value="{{ $bitacora->duracion ?? '' }}" {{Route::current()->getName() == 'admin.bitacora.show' ? 'disabled' : ''}}>
    </div>
    <div class="form-group col-md-4 ">
        <label for="fecha" class=" mr-3">Fecha: </label>
        <input type="date" class="date form-control" id="fecha" name="fecha" required value="{{ $bitacora->fecha ?? '' }}" {{Route::current()->getName() == 'admin.bitacora.show' ? 'disabled' : ''}}>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-12 my-3">
        <label for="descripcion">Descripcion: </label>
        <textarea rows="4" class="form-control" id="descripcion" name="descripcion" {{Route::current()->getName() == 'admin.bitacora.show' ? 'disabled' : ''}}>{{ $bitacora->descripcion ?? '' }}</textarea>
    </div>
</div>
<div class="form-group col-md-6">
    <input type="submit" value="Guardar" class="btn btn-sm btn-primary" {{Route::current()->getName() == 'admin.bitacora.show' ? 'hidden' : ''}}>
    
    
</div>
