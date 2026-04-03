<!-- Modal -->
<div class="modal fade" id="modalObservaciones{{ $service->id }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="" novalidate id="form-observation" action="{{ route('admin.services.update',  $service->id) }}" method="post">
      @method('PUT')
      @csrf
      <div class="modal-content">
        <div class="modal-header bg-dark">
          <h5 class="modal-title" id="exampleModalLabel">Observaciones</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body bg-dark">
          <div class="form-row">
            <div class="col-md-12">
              <label>ID:</label> <span id="id-service">{{ $service->id }}</</span>,
              <label>Servicio:</label> <span id="service">{{ $service->failure->name }}</span>
            </div>
            <input type="hidden" name="id" id="id-observations" value="{{ $service->id }}" class="form-control col-md-12">
            <div class="form-group col-md-12">
              <label for="observations">Observaciones</label>
              <textarea class="form-control" id="observations" name="observations" rows="3" {{ old('observations') }} placeholder="Observaciones" required></textarea>
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
          <button type="submit" name="btn-observacion" class="btn btn-primary">Guardar</button>
        </div>
      </div>
    </form>
  </div>
</div>
