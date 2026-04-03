@extends('admin.layout')

@section('title', '| Crear nuevo producto')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Nuevo Producto') }}</div>
                <div class="card-body">
                    <form action="{{ route('admin.products.store') }}" method="post" class="needs-validation" novalidate>
                        <div class="form-row">
                            @csrf
                            <!-- Otros campos del formulario aquí -->
                            <div class="form-group col-md-6">
                                <label for="serie">Serie</label>
                                <input type="text" name="serie"
                                    class="form-control @error('serie') is-invalid @enderror" value="{{ old('serie') }}"
                                    required>
                                @error('serie')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="name">Producto</label>
                                <select class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                    name="name" id="name" required>
                                    <option value="" selected disabled>Selecciona un producto...</option>
                                    @foreach ($productos as $producto)
                                        <option value="{{ $producto->contenido }}"
                                            {{ old('name') == $producto->contenido ? 'selected' : '' }}>
                                            {{ $producto->contenido }}</option>
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
                                    class="form-control @error('etiqueta') is-invalid @enderror"
                                    value="{{ old('etiqueta') }}" required>
                                @error('etiqueta')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="marca">Marca</label>
                                <input type="text" name="marca"
                                    class="form-control @error('marca') is-invalid @enderror" value="{{ old('marca') }}"
                                    required>
                                @error('marca')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="modelo">Modelo</label>
                                <input type="text" name="modelo"
                                    class="form-control @error('modelo') is-invalid @enderror" value="{{ old('modelo') }}"
                                    required>
                                @error('modelo')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            @unlessrole('Mantenimiento|Operaciones')
                                <div class="form-group col-md-6">
                                    <label for="medio">Medio</label>
                                    <select class="custom-select @error('medio') is-invalid @enderror" name="medio"
                                        id="medio" required>
                                        <option value="" disabled {{ old('medio') ? '' : 'selected' }}>Seleccione un
                                            medio...</option>
                                        <option value="Extraíble" {{ old('medio') == 'Extraíble' ? 'selected' : '' }}>Extraíble
                                        </option>
                                        <option value="No extraíble" {{ old('medio') == 'No extraíble' ? 'selected' : '' }}>No
                                            extraíble</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="ip">IP</label>
                                    <input type="text" name="ip" class="form-control @error('ip') is-invalid @enderror"
                                        value="{{ old('ip') }}">
                                    @error('ip')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="mac">MAC</label>
                                    <input type="text" name="mac" class="form-control @error('mac') is-invalid @enderror"
                                        value="{{ old('mac') }}">
                                    @error('mac')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            @endunlessrole
                            <div class="form-group col-md-6">
                                <label>Fecha de ingreso</label>
                                <div>
                                    <input type="date" value="{{ old('fecha_ingreso') }}" name="fecha_ingreso"
                                        class="form-control" id="datepicker1" required />
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Tiempo de Antigüedad</label>
                                <div class="input-group date" id="antiguedad" data-target-input="nearest">
                                    <input type="text" class="form-control" id="tiempo_antiguedad" readonly />
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="company_id">Empresa</label>
                                <select class="custom-select @error('company_id') is-invalid @enderror" name="company_id"
                                    id="company_id" required>
                                    <option value="" selected disabled>Seleccione una empresa...</option>
                                    @foreach ($companies as $key => $value)
                                        <option value="{{ $key }}"
                                            {{ old('company_id') == $key ? 'selected' : '' }}>{{ $value }}</option>
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
                                    <option value="" disabled {{ old('status') ? '' : 'selected' }}>Seleccione un
                                        status...</option>
                                    <option value="OPERABLE" {{ old('status') == 'OPERABLE' ? 'selected' : '' }}>OPERABLE
                                    </option>
                                    <option value="INOPERABLE" {{ old('status') == 'INOPERABLE' ? 'selected' : '' }}>
                                        INOPERABLE</option>
                                    <option value="STOCK" {{ old('status') == 'STOCK' ? 'selected' : '' }}>STOCK</option>
                                    <option value="ROBADO" {{ old('status') == 'ROBADO' ? 'selected' : '' }}>ROBADO
                                    </option>
                                    <option value="RECICLADO" {{ old('status') == 'RECICLADO' ? 'selected' : '' }}>RECICLADO
                                    </option>
                                    <option value="EN_REPARACION"
                                        {{ old('status') == 'EN_REPARACION' ? 'selected' : '' }}>EN REPARACION</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="costo">Costo</label>
                                <input type="number" name="costo"
                                    class="form-control @error('costo') is-invalid @enderror"
                                    value="{{ old('costo') }}" required>
                                @error('costo')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="sede_id">Sede</label>
                                <select name="sede_id" id="sede_id" class="form-control">
                                    <option value="" selected disabled>Selecciona una sede...</option>
                                    @foreach ($sedes as $sede)
                                        <option value="{{ $sede->id }}"
                                            {{ old('sede_id') == $sede->id ? 'selected' : '' }}>
                                            {{ $sede->sede }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('sede_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6" id="ubicacion-container"
                                style="display: {{ old('sede_id') ? 'block' : 'none' }};">
                                <label for="ubicacion_id">Ubicación</label>
                                <select name="ubicacion_id" id="ubicacion_id" class="form-control">
                                    <option value="" disabled>Selecciona una ubicación...</option>
                                    @foreach ($ubicaciones as $ubicacion)
                                        <option value="{{ $ubicacion->id }}"
                                            {{ old('ubicacion_id') == $ubicacion->id ? 'selected' : '' }}>
                                            {{ $ubicacion->ubicacion }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('ubicacion_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-12">
                                <label for="observacion">Observación</label>
                                <textarea class="form-control @error('observacion') is-invalid @enderror" name="observacion" id="observacion"
                                    rows="1">{{ old('observacion') }}</textarea>
                            </div>
                            <div class="form-group col-md-6">
                                <button type="submit" class="btn btn-primary btn-block">Guardar producto</button>
                            </div>
                            <div class="form-group col-md-6">
                                <a href="{{ route('admin.products.index') }}"
                                    class="btn btn-danger btn-block">Cancelar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/datepiker/css/bootstrap-datepicker.min.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('adminlte/plugins/datepiker/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/moment/moment.min.js') }}"></script>

    <script>
        moment.defineLocale('es', null);

        function calcularAntiguedad() {
            var fechaIngreso = $('#datepicker1').val();
            if (fechaIngreso) {
                var fechaActual = moment();
                var fechaIngresoMoment = moment(fechaIngreso, 'YYYY/MM/DD');

                var duration = moment.duration(fechaActual.diff(fechaIngresoMoment));
                var years = duration.years();
                var months = duration.months();
                var days = duration.days();

                var tiempoAntiguedad = years + ' años, ' + months + ' meses, ' + days + ' días';
                $('#tiempo_antiguedad').val(tiempoAntiguedad);
            }
        }

        $(document).ready(function() {
            calcularAntiguedad();

            $('#datepicker1').change(function() {
                calcularAntiguedad();
            });

            function updateUbicaciones() {
                var sedeId = $('#sede_id').val();

                if (sedeId) {
                    var url = '{{ route('get_ubicaciones_sedes', ':sedeId') }}';
                    url = url.replace(':sedeId', sedeId);

                    $('#ubicacion-container').show();

                    $.ajax({
                        url: url,
                        method: 'GET',
                        success: function(data) {
                            var $ubicacionSelect = $('#ubicacion_id');
                            $ubicacionSelect.empty();
                            $ubicacionSelect.append(
                                '<option value="" selected disabled>Selecciona una ubicación...</option>'
                            );
                            $.each(data, function(index, ubicacion) {

                                $ubicacionSelect.append('<option value="' + ubicacion.id +
                                    '">' + ubicacion.ubicacion + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            $('#ubicacion-container').hide();
                            var $ubicacionSelect = $('#ubicacion_id');
                            $ubicacionSelect.empty();
                            $ubicacionSelect.append(
                                '<option value="" selected disabled>Selecciona una ubicación...</option>'
                            );
                            alert('No se encontraron ubicaciones relacionadas a esta sede');
                        }
                    });
                } else {
                    $('#ubicacion-container').hide();
                    $('#ubicacion_id').empty().append(
                        '<option value="" selected disabled>Selecciona una ubicación...</option>'
                    );
                }
            }

            $('#sede_id').change(function() {
                updateUbicaciones();
            });
        });
    </script>
@endpush
