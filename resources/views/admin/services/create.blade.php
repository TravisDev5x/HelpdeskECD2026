@extends('admin.layout')

@section('title', '| Crear nuevo ticket')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Nuevo Ticket</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                        <li class="breadcrumb-item active">Crear Ticket</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card card-primary card-outline shadow-lg">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold">
                                <i class="fas fa-edit mr-2"></i> Detalles de la Solicitud
                            </h3>
                        </div>

                        <div class="card-body">
                            {{-- ALERTA INFORMATIVA --}}
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="fas fa-info-circle mr-2"></i> Por favor, llene todos los campos marcados como requeridos para agilizar la atención.
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <form action="{{ route('admin.services.store') }}" method="post" class="needs-validation" novalidate>
                                @csrf
                                <div class="form-row">
                                    
                                    {{-- CAMPO: AREA --}}
                                    <div class="form-group col-md-6">
                                        <label for="area_id" class="font-weight-normal">
                                            <i class="fas fa-sitemap text-primary mr-1"></i> Área de Servicio <span class="text-danger">*</span>
                                        </label>
                                        <select class="custom-select form-control-lg @error('area_id') is-invalid @enderror" 
                                                name="area_id" id="area_id" required>
                                            <option value="" selected disabled>Seleccione el área responsable...</option>
                                            @foreach ($areas as $key => $value)
                                                @if ($value == 'Mantenimiento' || $value == 'Limpieza' || $value == 'Recepción')
                                                    @role('Mantenimiento_tickets|Mantenimiento|Recursos Humanos|Soporte|Auditor|Metricas|Telecomunicaciones|Finanzas|Admin|Proyectos|Control')
                                                        <option value="{{ $key }}">{{ $value }}</option>
                                                    @endrole
                                                @else
                                                    <option value="{{ $key }}">{{ $value }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Ej: Soporte, Mantenimiento, Infraestructura.</small>
                                        @error('area_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- CAMPO: TIPO DE SOLICITUD (Carga via AJAX) --}}
                                    <div class="form-group col-md-6">
                                        <label for="failure_id" class="font-weight-normal">
                                            <i class="fas fa-tools text-primary mr-1"></i> Tipo de Solicitud / Falla <span class="text-danger">*</span>
                                        </label>
                                        <select class="custom-select form-control-lg @error('failure_id') is-invalid @enderror" 
                                                name="failure_id" id="failure_id" required disabled>
                                            <option value="" selected disabled>Primero seleccione un área...</option>
                                        </select>
                                        <small class="form-text text-muted">Ej: Falla de internet, Falla en Teclado. </small>
                                        @error('failure_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- CAMPO: SEDE --}}
                                    <div class="form-group col-md-6">
                                        <label for="sede_id" class="font-weight-normal">
                                            <i class="fas fa-building text-primary mr-1"></i> Sede / Ubicación <span class="text-danger">*</span>
                                        </label>
                                        <select class="custom-select form-control-lg @error('sede_id') is-invalid @enderror" 
                                                name="sede_id" id="sede_id" required>
                                            <option value="" selected disabled>Seleccione dónde se requiere el servicio...</option>
                                            @foreach ($sedes as $sede)
                                                <option value="{{ $sede->id }}">{{ $sede->sede }}</option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Ej: Tlalpan, Toledo.</small>
                                        @error('sede_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- CAMPO: DESCRIPCIÓN --}}
                                    <div class="form-group col-md-12 mt-3">
                                        <label for="description" class="font-weight-normal">
                                            <i class="fas fa-align-left text-primary mr-1"></i> Descripción Detallada <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  name="description" id="description" 
                                                  rows="5" placeholder="Describa el problema lo más detallado posible..." required>{{ old('description') }}</textarea>
                                        <small class="form-text text-muted">
                                            Ej: "La impresora de recepción muestra error de papel atascado aunque ya revisé la bandeja."
                                        </small>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div> {{-- Fin form-row --}}

                                <div class="row mt-4">
                                    <div class="col-md-6 offset-md-3">
                                        <button type="submit" class="btn btn-primary btn-block btn-lg shadow-sm font-weight-bold">
                                            <i class="fas fa-paper-plane mr-2"></i> Enviar Ticket
                                        </button>
                                        <a href="{{ url()->previous() }}" class="btn btn-link btn-block text-muted">Cancelar</a>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <style>
        /* --- ESTILOS MEJORADOS PARA MODO OSCURO --- */
        
        /* 1. Fondo de tarjeta en dark mode */
        body.dark-mode .card {
            background-color: #343a40;
            color: #ffffff;
        }
        
        /* 2. Inputs y Selects */
        body.dark-mode .form-control,
        body.dark-mode .custom-select {
            background-color: #3f474e;
            color: #ffffff;
            border: 1px solid #6c757d;
        }
        body.dark-mode .form-control:focus,
        body.dark-mode .custom-select:focus {
            background-color: #454d55;
            border-color: #80bdff;
            color: #fff;
        }
        
        /* 3. Textos auxiliares */
        body.dark-mode .text-muted {
            color: #ced4da !important;
        }
        body.dark-mode .form-text {
            color: #adb5bd !important;
            font-style: italic;
        }

        /* 4. Título Principal */
        body.dark-mode h1.text-dark {
            color: #ffffff !important;
        }

        /* 5. Placeholder color */
        body.dark-mode ::placeholder {
            color: #adb5bd !important;
            opacity: 0.7;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('js/sistema/service-create.js') }}"></script>
    <script>
        // Pequeño script visual para habilitar el select de Fallas cuando se elige Área
        // (Solo visual, la lógica real debe estar en service-create.js)
        $('#area_id').change(function() {
            if($(this).val()) {
                $('#failure_id').prop('disabled', false);
            }
        });
    </script>
@endpush