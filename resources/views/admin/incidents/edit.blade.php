@extends('admin.layout')

@section('title', '| Actualizar')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Complementar') }}
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.incidents.update', $incident) }}" method="post" class="needs-validation"
                        novalidate>
                        <div class="form-row">
                            @csrf
                            @method('PUT')
                            @if ($incident->tipo !== null)
                                <input type="hidden" name="tipo" id="tipo" value="{{ $incident->tipo }}">
                            @else
                                <label for="tipo">Seleccionar tipo: </label>
                                <select name="tipo" id="tipo"
                                    class="custom-select @error('tipo') is-invalid @enderror">
                                    <option value="" selected>Tipo</option>
                                    <option value="Evento">Evento</option>
                                    <option value="Incidente">Incidente</option>
                                </select>
                            @endif
                            <div class="form-group col-md-6">
                                <label>Fecha de inhabilitación</label>
                                <div class="input-group date" id="reservationdatetime" data-target-input="nearest">
                                    <input type="text" name="disqualification_date"
                                        class="form-control datetimepicker-input @error('disqualification_date') is-invalid @enderror"
                                        value="{{ old('disqualification_date', $incident->disqualification_date) }}"
                                        required data-target="#reservationdatetime">
                                    <div class="input-group-append" data-target="#reservationdatetime"
                                        data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                    @error('disqualification_date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="sistema">Sistema</label>
                                <input type="text" name="sistema"
                                    class="form-control  @error('sistema') is-invalid @enderror"
                                    value="{{ old('sistema', $incident->sistema) }}" readonly>
                                @error('sistema')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="responsable">Responsable</label>
                                <input type="text" name="responsable"
                                    class="form-control  @error('responsable') is-invalid @enderror"
                                    value="{{ old('responsable', $incident->responsable) }}" readonly>
                                @error('responsable')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="criticidad">Nivel de criticidad</label>
                                <select id="criticidad" name="criticidad"
                                    class="form-control @error('criticidad') is-invalid @enderror">
                                    <option {{ $incident->criticidad ? '' : 'selected' }} disabled>Choose...</option>
                                    <option {{ $incident->criticidad == 1 ? 'selected' : '' }} value="1">Baja</option>
                                    <option {{ $incident->criticidad == 2 ? 'selected' : '' }} value="2">Media
                                    </option>
                                    <option {{ $incident->criticidad == 3 ? 'selected' : '' }} value="3">Alta</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="causa">Causa</label>
                                <textarea class="form-control @error('causa') is-invalid @enderror" name="causa" id="causa" rows="2"
                                    required>{{ old('causa', $incident->causa) }}</textarea>
                                @error('causa')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="observations">Análisis</label>
                                <textarea class="form-control @error('observations') is-invalid @enderror" name="observations" id="observations"
                                    rows="2" required>{{ old('observations', $incident->observations) }}</textarea>
                                @error('observations')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="acciones">Solución </label>
                                <textarea class="form-control @error('acciones') is-invalid @enderror" name="acciones" id="acciones" rows="2"
                                    required>{{ old('acciones', $incident->acciones) }}</textarea>
                                @error('acciones')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="notas">Notas de prevensión </label>
                                <textarea class="form-control @error('notas') is-invalid @enderror" name="notas" id="notas" rows="2">{{ old('notas', $incident->notas) }}</textarea>
                                @error('notas')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="lecciones">Lecciones aprendidas </label>
                                <textarea class="form-control @error('lecciones') is-invalid @enderror" name="lecciones" id="lecciones" rows="2">{{ old('lecciones', $incident->lecciones) }}</textarea>
                                @error('lecciones')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label>Fecha de habilitación</label>
                                <div class="input-group date" id="reservationdatetime1" data-target-input="nearest">
                                    <input type="text" name="enablement_date"
                                        class="form-control datetimepicker-input @error('enablement_date') is-invalid @enderror"
                                        value="{{ old('enablement_date', $incident->enablement_date ? $incident->enablement_date : null) }}"
                                        required data-target="#reservationdatetime1">
                                    <div class="input-group-append" data-target="#reservationdatetime1"
                                        data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                    @error('enablement_date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group col-3 mx-auto">
                                <label for="">&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">Actualizar</button>
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
