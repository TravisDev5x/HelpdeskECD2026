<!-- Modal -->
<div class="modal fade" id="activocritico{{ $service->id }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    @role('General')
      <form class=""  id="form-activocritico" action="{{ route('admin.service_validation', $service->id) }}" method="post">

    @else
      <form class=""  id="form-activocritico" action="{{ route('admin.services.update', $service->id) }}" method="post">
    
    @endrole  
      @method('PUT')
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="activocritico">Segimiento</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <div class="col-md-12">
              <label>ID:</label> <span id="id-service">{{ $service->id }}</span>,
              <label>Servicio:</label> <span id="service">{{ $service->failure->name }}</span>
            </div>
            <input type="hidden" name="id" id="id" value="{{ $service->id }}" class="form-control col-md-12">
            @role('General')
                <div class="form-group col-md-6">
                <label for="validation">Acpetar el cambio del activo critico</label>
                <select id="validation" name="validation" class="custom-select" required>
                  <option selected disabled value="">Selecciona una opcion...</option>
                  <option value="{{ \App\Support\Tickets\TicketStatus::SEGUIMIENTO }}">Si</option>
                  <option value="{{ \App\Support\Tickets\TicketStatus::FINALIZADO }}">No</option>
                </select>
                <div class="invalid-feedback">
                  Por favor selecciona un estatus.
                </div>
              </div>
            @else
              <div class="form-group col-md-6">
                <label for="status">Estatus</label>
                <select id="status" name="status" class="custom-select" required>
                  <option selected disabled value="">Selecciona un estatus...</option>
                  <option value="{{ \App\Support\Tickets\TicketStatus::SEGUIMIENTO }}">{{ \App\Support\Tickets\TicketStatus::label(\App\Support\Tickets\TicketStatus::SEGUIMIENTO) }}</option>
                  <option value="{{ \App\Support\Tickets\TicketStatus::FINALIZADO }}">{{ \App\Support\Tickets\TicketStatus::label(\App\Support\Tickets\TicketStatus::FINALIZADO) }}</option>
                </select>
                <div class="invalid-feedback">
                  Por favor selecciona un estatus.
                </div>
              </div>

            @endrole
            @unlessrole('General')

              <div class="form-group col-md-12">
                <label for="solution">Observaciones y riesgos</label>
                <textarea class="form-control" id="solution" name="solution" rows="3" {{ old('solution') }} placeholder="Observaciones" required></textarea>
                <div class="invalid-feedback">
                  Por favor ingrese las observaciones.
                </div>
              </div>  

            @endrole


            <div class="col-md-12" id="validation-errors">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="submit" name="btn-activocritico" class="btn btn-primary">Guardar</button>
        </div>
      </div>
    </form>
  </div>
</div>
