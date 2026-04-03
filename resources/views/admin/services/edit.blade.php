{{-- Modal seguimiento: AdminLTE 3 + mismo lenguaje visual que historial / tarjetas del sistema --}}
<div class="modal fade" id="modalSeguimiento" tabindex="-1" aria-labelledby="modalSeguimientoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
    <form class="needs-validation" novalidate id="form-seguimiento" action="{{ route('admin.services.update', 'service', '#update') }}" method="post">
      @method('PUT')
      @csrf
      <div class="modal-content shadow">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title mb-0" id="modalSeguimientoLabel">
            <i class="fas fa-tasks mr-2"></i> Seguimiento de ticket
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          {{-- Metadato usado por JS (Livewire / legado); sin mostrar --}}
          <span id="fecha_seguimiento" class="d-none" aria-hidden="true"></span>

          <div class="callout callout-info bg-light border-left mb-3 py-3 px-3 helpdesk-modal-callout">
            <div class="row">
              <div class="col-sm-6 mb-2 mb-sm-0">
                <div class="text-muted text-uppercase small font-weight-bold mb-1">Ticket</div>
                <div class="h4 mb-0 font-weight-bold text-dark">
                  #<span id="id-service">—</span>
                </div>
              </div>
              <div class="col-sm-6 mb-2 mb-sm-0">
                <div class="text-muted text-uppercase small font-weight-bold mb-1">Tipo de servicio</div>
                <div class="font-weight-bold text-dark text-break" id="service">—</div>
              </div>
            </div>
            <hr class="my-3 border-secondary">
            <div class="text-muted text-uppercase small font-weight-bold mb-1">
              <i class="fas fa-align-left mr-1"></i> Descripción del solicitante
            </div>
            <div id="description" class="small text-break rounded border p-3 helpdesk-modal-description">
              Sin descripción registrada.
            </div>
          </div>

          <input type="hidden" name="id" id="id">

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="status"><i class="fas fa-flag mr-1 text-muted"></i>Estatus</label>
              <select id="status" name="status" class="custom-select" required>
                <option selected disabled value="">Selecciona un estatus...</option>
                <option value="{{ \App\Support\Tickets\TicketStatus::SEGUIMIENTO }}" id="option_seguimiento">{{ \App\Support\Tickets\TicketStatus::label(\App\Support\Tickets\TicketStatus::SEGUIMIENTO) }}</option>
                <option value="{{ \App\Support\Tickets\TicketStatus::TICKET_ERRONEO }}" id="option_ticket_erroneo">{{ \App\Support\Tickets\TicketStatus::label(\App\Support\Tickets\TicketStatus::TICKET_ERRONEO) }}</option>
                <option value="{{ \App\Support\Tickets\TicketStatus::FINALIZADO }}" id="option_finalizado">{{ \App\Support\Tickets\TicketStatus::label(\App\Support\Tickets\TicketStatus::FINALIZADO) }}</option>
              </select>
              <div class="invalid-feedback">
                Por favor selecciona un estatus.
              </div>
            </div>
            <div class="form-group col-md-12">
              <label for="observations"><i class="fas fa-comment-alt mr-1 text-muted"></i>Observaciones</label>
              <textarea class="form-control" id="observations" name="observations" rows="3" placeholder="Describe el seguimiento o acuerdos..." required>{{ old('observations') }}</textarea>
              <div class="invalid-feedback">
                Por favor ingrese las observaciones.
              </div>
            </div>
            <div class="col-md-12" id="validation-errors"></div>
            <div class="form-group col-md-12" id="div_solucion">
              <label for="solution"><i class="fas fa-check-circle mr-1 text-muted"></i>Solución o acuerdo</label>
              <textarea class="form-control" id="solution" name="solution" rows="3" placeholder="Solución definitiva o acuerdo con el usuario">{{ old('solution') }}</textarea>
              <div class="invalid-feedback">
                Por favor ingrese la solución o acuerdo.
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer justify-content-between bg-light border-top">
          <button type="button" class="btn btn-default" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Cancelar
          </button>
          <button type="submit" name="btn-seguimiento" id="btn-seguimiento" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> Guardar cambios
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
