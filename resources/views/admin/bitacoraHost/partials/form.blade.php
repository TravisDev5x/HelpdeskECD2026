<div class="row ">
    <div class="form-group col-md-4">
        <label for="host" class="">Host: </label>
        <input type="text" class="text form-control" id="host" name="host" required value="{{ $bitacoraHost->host ?? '' }}" {{Route::current()->getName() == 'admin.bitacoraHost.show' ? 'disabled' : ''}}>
    </div>
    <div class="form-group col-md-4 ">
        <label for="ip" class="">IP: </label>
        <input type="text" class=" form-control" id="ip" name="ip"  required value="{{ $bitacoraHost->ip ?? '' }}" {{Route::current()->getName() == 'admin.bitacoraHost.show' ? 'disabled' : ''}}>
    </div>
    <div class="form-group col-md-4 ">
        <label for="bd" class=" mr-3">BD: </label>
        <input type="text" class="date form-control" id="bd" name="bd" required value="{{ $bitacoraHost->bd ?? '' }}" {{Route::current()->getName() == 'admin.bitacoraHost.show' ? 'disabled' : ''}}>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-12 my-3">
        <label for="descripcion">Descripcion: </label>
        <textarea rows="4" class="form-control" id="descripcion" name="descripcion" {{Route::current()->getName() == 'admin.bitacoraHost.show' ? 'disabled' : ''}}>{{ $bitacoraHost->descripcion ?? '' }}</textarea>
    </div>
</div>
<div class="form-group col-md-6">
    <input type="submit" value="Guardar" class="btn btn-sm btn-primary" {{Route::current()->getName() == 'admin.bitacoraHost.show' ? 'hidden' : ''}}>
    
    
</div>
