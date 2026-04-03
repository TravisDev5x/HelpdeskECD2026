@extends('admin.layout')

@section('title', '| Crear nueva incidencia')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Nueva Incidencia') }}
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.incidents.store') }}" method="post" class="needs-validation" novalidate>
                        <div class="form-row">
                            @csrf
                            <input type="hidden" name="tipo" value="Incidente" name="tipo" id="tipo">
                            <div class="form-group col-md-6">
                                <label>Fecha de inhabilitación</label>
                                <div class="input-group date" id="reservationdatetime" data-target-input="nearest">
                                    <input type="text" name="disqualification_date"
                                        class="form-control datetimepicker-input @error('disqualification_date') is-invalid @enderror"
                                        required data-target="#reservationdatetime">
                                    <div class="input-group-append" data-target="#reservationdatetime"
                                        data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                                @error('disqualification_date')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="sistema">Sistema</label>
                                <select name="sistema" id="sistema" class="form-control" required>
                                    <option value="" selected disabled>Selecciona un sistema...</option>
                                    @foreach ($sistemas as $sistema)
                                        <option value="{{ $sistema->contenido }}"> {{ $sistema->contenido }}</option>
                                    @endforeach
                                </select>
                                @error('sistema')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="responsable">Responsable</label>
                                <select name="responsable" id="responsable" class="form-control">
                                    <option value="" selected disabled>Selecciona un responsable...</option>
                                    @foreach($areas as $area)
                                    <option value="{{ $area->name }}">{{ $area->name }}</option>
                                    @endforeach
                                </select>
                                @error('responsable')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="criticidad">Nivel de criticidad</label>
                                <select id="criticidad" name="criticidad" class="form-control">
                                    {{-- <option selected disabled>Choose...</option> --}}
                                    <option selected value="1">Baja</option>
                                    <option value="2">Media</option>
                                    <option value="3">Alta</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="causa">Causa</label>
                                <textarea class="form-control @error('causa') is-invalid @enderror" name="causa" id="causa" rows="2"
                                    {{ old('causa') }} required></textarea>
                                @error('causa')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="acciones">Solución</label>
                                <textarea class="form-control" name="acciones" id="acciones" rows="2">{{ old('acciones') }}</textarea>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="observations">Análisis</label>
                                <textarea class="form-control @error('observations') is-invalid @enderror" name="observations" id="observations"
                                    rows="2" {{ old('observations') }}></textarea>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="notas">Notas de prevensión </label>
                                <textarea class="form-control @error('notas') is-invalid @enderror" name="notas" id="notas" rows="2">{{ old('notas') }}</textarea>
                                @error('notas')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label>Fecha de habilitación</label>
                                <div class="input-group date" id="reservationdatetime1" data-target-input="nearest">
                                    <input type="text" name="enablement_date" class="form-control datetimepicker-input"
                                        data-target="#reservationdatetime1">
                                    <div class="input-group-append" data-target="#reservationdatetime1"
                                        data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-3 mx-auto">
                                <label for="">&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">Guardar incidencia</button>
                            </div>
                            <div class="form-group col-3 mx-auto">
                                <label for="">&nbsp;</label>
                                <a href="{{ route('admin.incidents.index') }}"
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
    <!-- date picker -->
    <link rel="stylesheet"
        href="{{ asset('adminlte/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <style media="screen">
        .dropdown-menu {
            z-index: 10000 !important;
        }
    </style>
@endpush
@push('scripts')
    <script src="{{ asset('adminlte/plugins/moment/moment.min.js') }}"></script>
    <!-- date picker -->
    <script src="{{ asset('adminlte/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/moment/locales.js') }}"></script>
    <script src="{{ asset('js/sistema/asset-create.js') }}"></script>
    <script>
        moment.defineLocale('es-do', null);
        //Date picker
        $('#reservationdatetime').datetimepicker({
            icons: {
                time: 'far fa-clock'
            },
            locale: 'es-do',
            format: 'YYYY-MM-DD HH:mm:ss'
        });
        $('#reservationdatetime1').datetimepicker({
            icons: {
                time: 'far fa-clock'
            },
            locale: 'es-do',
            format: 'YYYY-MM-DD HH:mm:ss'
        });
    </script>
@endpush
