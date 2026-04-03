<div class="modal fade" id="audit-details-modal" tabindex="-1" role="dialog" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow-lg rounded-lg">

            {{-- HEADER --}}
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title d-flex align-items-center mb-0">
                    <i class="fas fa-search text-info mr-2"></i>
                    Detalles de Auditoría
                    <span class="badge badge-secondary ml-2">
                        #{{ $selectedLog['id'] ?? '...' }}
                    </span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            {{-- BODY --}}
            <div class="modal-body bg-light">

                @if($selectedLog)

                    {{-- ERROR --}}
                    @if(isset($selectedLog['error']) && $selectedLog['error'] === true)
                        <div class="alert alert-danger shadow-sm mb-0">
                            <h5 class="mb-1">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Error
                            </h5>
                            {{ $selectedLog['message'] }}
                        </div>

                    @else

                        {{-- RESUMEN --}}
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body py-3">
                                <div class="row text-sm align-items-center">

                                    <div class="col-md-6 border-right">
                                        <small class="text-muted text-uppercase d-block">
                                            Responsable
                                        </small>
                                        <div class="font-weight-bold text-primary">
                                            {{ $selectedLog['causer_name'] }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $selectedLog['causer_email'] }}
                                        </small>
                                    </div>

                                    <div class="col-md-6 pl-md-4 mt-3 mt-md-0">
                                        <small class="text-muted text-uppercase d-block">
                                            Contexto
                                        </small>
                                        <div class="font-weight-bold">
                                            {{ $selectedLog['created_at'] }}
                                        </div>
                                        <span class="badge badge-dark mt-1">
                                            IP: {{ $selectedLog['ip'] }}
                                        </span>
                                    </div>

                                </div>
                            </div>
                        </div>

                        {{-- DIFERENCIAS --}}
                        <div class="row">

                            {{-- ANTES --}}
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="card card-danger card-outline h-100 shadow-sm">
                                    <div class="card-header bg-transparent border-bottom">
                                        <h3 class="card-title text-danger font-weight-bold mb-0">
                                            <i class="fas fa-arrow-left mr-1"></i>
                                            Valor Anterior
                                        </h3>
                                    </div>

                                    <div class="card-body p-0 table-responsive">
                                        <table class="table table-sm table-striped mb-0 text-sm">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Campo</th>
                                                    <th>Contenido</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($selectedLog['before'] as $key => $value)
                                                    <tr>
                                                        <td class="font-weight-bold text-secondary">
                                                            {{ $key }}
                                                        </td>
                                                        <td>
                                                            @if(is_array($value))
                                                                <span class="badge badge-info">Array</span>
                                                            @elseif(is_null($value) || $value === '')
                                                                <span class="text-muted small">— Vacío —</span>
                                                            @elseif(is_bool($value))
                                                                {{ $value ? 'Sí' : 'No' }}
                                                            @else
                                                                {{ $value }}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="2" class="text-center text-muted p-3">
                                                            Sin datos previos
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- DESPUÉS --}}
                            <div class="col-md-6">
                                <div class="card card-success card-outline h-100 shadow-sm">
                                    <div class="card-header bg-transparent border-bottom">
                                        <h3 class="card-title text-success font-weight-bold mb-0">
                                            <i class="fas fa-arrow-right mr-1"></i>
                                            Valor Nuevo
                                        </h3>
                                    </div>

                                    <div class="card-body p-0 table-responsive">
                                        <table class="table table-sm table-striped mb-0 text-sm">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Campo</th>
                                                    <th>Contenido</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($selectedLog['after'] as $key => $value)
                                                    <tr>
                                                        <td class="font-weight-bold text-secondary">
                                                            {{ $key }}
                                                        </td>
                                                        <td>
                                                            @if(is_array($value))
                                                                <span class="badge badge-info">Array</span>
                                                            @elseif(is_null($value) || $value === '')
                                                                <span class="text-muted small">— Vacío —</span>
                                                            @elseif(is_bool($value))
                                                                {{ $value ? 'Sí' : 'No' }}
                                                            @else
                                                                <span class="font-weight-bold text-dark">
                                                                    {{ $value }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="2" class="text-center text-muted p-3">
                                                            Sin cambios registrados
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @endif

                @else
                    <div class="d-flex justify-content-center py-5">
                        <div class="spinner-border text-primary"></div>
                    </div>
                @endif

            </div>

            {{-- FOOTER --}}
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-outline-secondary px-4" data-dismiss="modal">
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>
