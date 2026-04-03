@extends('admin.layout')

@section('title', '| Editar producto')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">{{ __('Editar Producto') }}
            </div>
            <div class="card-body">
                <form action="{{ route('admin.products.update', $product) }}" method="post" class="needs-validation"
                    novalidate>
                    <div class="form-row">
                        @csrf
                        @method('PUT')
                        <div class="form-group col-md-6">
                            <label for="serie">Serie</label>
                            <input type="text" name="serie" class="form-control @error('serie') is-invalid @enderror"
                                value="{{ old('serie', $product->serie) }}" required>
                            @error('serie')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="name">Producto</label>
                            <select name="name" id="name" class="form-control">
                                <option value="" selected disabled>Selecciona un producto...</option>
                                @foreach ($productos as $producto)
                                <option value="{{ $producto->contenido }}" @if (old('name', $product->name) ==
                                    $producto->contenido) selected @endif>
                                    {{ $producto->contenido }}
                                </option>
                                @endforeach
                            </select>
                            @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="etiqueta">Etiqueta</label>
                            <input type="text" name="etiqueta"
                                class="form-control  @error('etiqueta') is-invalid @enderror"
                                value="{{ old('etiqueta', $product->etiqueta) }}" required>
                            @error('etiqueta')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="marca">Marca</label>
                            <input type="text" name="marca" class="form-control  @error('marca') is-invalid @enderror"
                                value="{{ old('marca', $product->marca) }}">
                            @error('marca')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="modelo">Modelo</label>
                            <input type="text" name="modelo" class="form-control  @error('modelo') is-invalid @enderror"
                                value="{{ old('modelo', $product->modelo) }}">
                            @error('modelo')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="medio">Medio</label>
                            <select class="custom-select" name="medio" id="medio">
                                <option value="" selected disabled>Seleccione un medio...</option>
                                <option {{ old('medio', $product->medio) == 'Extraíble' ? 'selected' : '' }}
                                    value="Extraíble">Extraíble</option>
                                <option {{ old('medio', $product->medio) == 'No extraíble' ? 'selected' : '' }}
                                    value="No extraíble">No extraíble</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="ip">IP</label>
                            <input type="text" name="ip" class="form-control @error('ip') is-invalid @enderror"
                                value="{{ old('ip', $product->ip) }}">
                            @error('ip')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="mac">MAC</label>
                            <input type="text" name="mac" class="form-control @error('mac') is-invalid @enderror"
                                value="{{ old('mac', $product->mac) }}">
                            @error('mac')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="fecha_ingreso">Fecha de ingreso</label>
                            <input type="date" id="fecha_ingreso" name="fecha_ingreso"
                                value="{{ old('fecha_ingreso', $product->fecha_ingreso ? $product->fecha_ingreso->format('Y-m-d') : null) }}"
                                class="form-control @error('fecha_ingreso') is-invalid @enderror" />
                            @error('fecha_ingreso')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label>Tiempo de Antiguedad</label>
                            <div class="input-group date" id="antiguedad" data-target-input="nearest">
                                <input type="text" class="form-control" id="tiempo_antiguedad" readonly />
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="company_id">Empresa</label>
                            <select class="custom-select @error('company_id') is-invalid @enderror" name="company_id"
                                id="company_id" required>
                                <option value="" selected disabled>Seleccione una empresa...</option>
                                @foreach ($companies as $company)
                                <option value="{{ $company->id }}" {{ old('company_id', $product->company_id) ==
                                    $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}</option>
                                @endforeach
                            </select>
                            @error('company_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="status">Status</label>
                            <select class="custom-select @error('status') is-invalid @enderror" name="status"
                                id="status" required>
                                <option value="" selected disabled>Seleccione un status...</option>
                                <option {{ old('status', $product->status) == 'OPERABLE' ? 'selected' : '' }}
                                    value="OPERABLE">OPERABLE</option>
                                <option {{ old('status', $product->status) == 'INOPERABLE' ? 'selected' : '' }}
                                    value="INOPERABLE">INOPERABLE</option>
                                <option {{ old('status', $product->status) == 'STOCK' ? 'selected' : '' }}
                                    value="STOCK">STOCK</option>
                                <option value="ROBADO" {{ old('status', $product->status) == 'ROBADO' ? 'selected' : ''
                                    }}>
                                    ROBADO</option>
                                <option value="RECICLADO" {{ old('status', $product->status) == 'RECICLADO' ? 'selected' : ''
                                    }}>
                                    RECICLADO</option>
                                <option value="EN_REPARACION" {{ old('status', $product->status) == 'EN_REPARACION' ?
                                    'selected' : '' }}>
                                    EN REPARACION</option>
                                <option value="NO_ENTREGADO" {{ old('status', $product->status) == 'NO_ENTREGADO' ? 'selected' : '' }}>
                                    NO ENTREGADO</option>
                                <option value="ABSOLETO" {{ old('status', $product->status) == 'ABSOLETO' ? 'selected' : '' }}>
                                    ABSOLETO</option>
                            </select>
                            @error('status')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="costo">Costo</label>
                            <input type="text" name="costo" class="form-control"
                                value="{{ old('costo', $product->costo) }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="sede_id">Sede</label>
                            <select name="sede_id" id="sede_id" class="form-control">
                                <option selected disabled>Selecciona una sede...</option>
                                @foreach ($sedes as $sede)
                                <option value="{{ $sede->id }}" {{ old('sede_id', $product->sede_id) == $sede->id ?
                                    'selected' : '' }}>
                                    {{ $sede->sede }}</option>
                                @endforeach
                            </select>
                            @error('sede_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group col-md-6" id="ubicacion-container">
                            <label for="ubicacion_id">Ubicación</label>
                            <select name="ubicacion_id" id="ubicacion_id" class="form-control">
                                <option value="" selected disabled>Selecciona una ubicación...</option>
                            </select>
                            @error('ubicacion_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group col-md-12">
                            <label for="observacion">Observación</label>
                            <textarea class="form-control @error('observacion') is-invalid @enderror" name="observacion"
                                id="observacion" rows="1">{{ old('observacion', $product->observacion) }}</textarea>
                        </div>
                        <div class="form-group col-md-6">
                            <button type="submit" class="btn btn-primary btn-block">Guardar producto</button>
                        </div>
                        <div class="form-group col-md-6">
                            <a href="{{ route('admin.products.index') }}" class="btn btn-danger btn-block">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@push('styles')
<!-- date picker -->
<link rel="stylesheet" href="{{ asset('adminlte/plugins/datepiker/css/bootstrap-datepicker.min.css') }}">
@endpush
@push('scripts')
<!-- date picker -->
<script src="{{ asset('adminlte/plugins/datepiker/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/moment/moment.min.js') }}"></script>
{{-- <script src="{{ asset('js/sistema/product-create.js') }}"></script> --}}

<script>
    moment.defineLocale('es', null);

    function calcularAntiguedad() {
        var fechaIngreso = $('#fecha_ingreso').val(); // Cambia el selector
        if (fechaIngreso) {
            var fechaActual = moment();
            var fechaIngresoMoment = moment(fechaIngreso, 'YYYY-MM-DD'); // Asegúrate del formato correcto

            var duration = moment.duration(fechaActual.diff(fechaIngresoMoment));
            var years = duration.years();
            var months = duration.months();
            var days = duration.days();

            var tiempoAntiguedad = years + ' años, ' + months + ' meses, ' + days + ' días';
            $('#tiempo_antiguedad').val(tiempoAntiguedad);
        }
    }

    $(document).ready(function() {
        calcularAntiguedad(); // Calcula la antigüedad al cargar la página

        $('#fecha_ingreso').change(function() {
            calcularAntiguedad(); // Actualiza la antigüedad al cambiar la fecha
        });

        function updateUbicaciones() {
            var sedeId = $('#sede_id').val();

            if (sedeId) {
                var url = '{{ route('get_ubicaciones_sedes', ':sedeId') }}';
                url = url.replace(':sedeId', sedeId);

                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function(data) {
                        var $ubicacionSelect = $('#ubicacion_id');
                        $ubicacionSelect.empty();
                        $ubicacionSelect.append('<option value="" selected disabled>Selecciona una ubicación...</option>');

                        if (data.length > 0) {
                            $.each(data, function(index, ubicacion) {
                                $ubicacionSelect.append('<option value="' + ubicacion.id + '">' + ubicacion.ubicacion + '</option>');
                            });
                            $('#ubicacion-container').show(); // Muestra el campo si hay ubicaciones

                            // Seleccionar la ubicación del producto, si existe
                            var ubicacionId = '{{ old("ubicacion_id", $product->ubicacion_id) }}';
                            if (ubicacionId) {
                                $ubicacionSelect.val(ubicacionId);
                            }
                        } else {
                            $('#ubicacion-container').hide(); // Oculta el campo si no hay ubicaciones
                        }
                    },
                    error: function(xhr, status, error) {
                        var $ubicacionSelect = $('#ubicacion_id');
                        $ubicacionSelect.empty().append('<option value="" selected disabled>Selecciona una ubicación...</option>');
                        $('#ubicacion-container').hide(); // Oculta el campo si hay error
                        alert('No se encontraron ubicaciones relacionadas a esta sede');
                    }
                });
            } else {
                $('#ubicacion_id').empty().append('<option value="" selected disabled>Selecciona una ubicación...</option>');
                $('#ubicacion-container').hide(); // Oculta el campo si no se selecciona una sede
            }
        }

        $('#sede_id').change(function() {
            updateUbicaciones();
        });

        // Llamar a la función al cargar la página para pre-cargar las ubicaciones
        updateUbicaciones();
    });
</script>
@endpush