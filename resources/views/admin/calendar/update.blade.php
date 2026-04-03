<!-- Modal -->
<div class="modal fade" id="modalEvent" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="needs-validation" novalidate id="form-seguimiento" action=" {{ route('admin.agenda.update') }} " method="post">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Segimiento</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <div class="col-md-12">
              <label>Fecha de inicio:</label> <span id="date_update"></span><br>
              <label for="date_end_update">Fehca de fin</label>
              <input class="form-control" type="date" name="date_end_update" id="date_end_update">
            </div>
            <div class="col-md-6">
              <label for="time_update">hora</label>
              <input class="form-control" type="time" name="time_update" id="time_update">
            </div>
            <input type="hidden" name="date_post_update" id="date_post_update" class="form-control col-md-12">
            <input type="hidden" name="id_update" id="id_update" class="form-control col-md-12">
            <div class="form-group col-md-6">
              <label for="status_update">Estatus</label>
              <select id="status_update" name="status_update" class="custom-select" required>
                <option selected disabled value="">Selecciona un estatus...</option>
                <option value="Pendiente">Pendiente</option>
                <option value="Proceso">Proceso</option>
                <option value="Finalizado">Finalizado</option>
              </select>
              <div class="invalid-feedback">
                Por favor selecciona un estatus.
              </div>
            </div>
            <div class="form-group col-md-12">
              <label for="actividad_update">Actividad</label>
              <input class="form-control" type="text" name="actividad_update" id="actividad_update">
            </div>
            <div class="form-group col-md-12">
              <label for="descripcion_update">Descripcion</label>
              <textarea class="form-control" id="descripcion_update" name="descripcion_update" rows="3" {{ old('descripcions') }} placeholder="Observaciones" required></textarea>
              <div class="invalid-feedback">
                Por favor ingrese las observaciones.
              </div>
            </div>
            <div class="col-md-12" id="validation-errors">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="submit" name="btn-calendario" class="btn btn-primary">Guardar</button>
        </div>
      </div>
    </form>
  </div>
</div>
