@extends('admin.layout')

@section('title', '| Crear nueva incidencia')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Nueva Incidencia') }}
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.contenido.ctg.incidencia.store') }}" method="post" class="needs-validation" novalidate>
                        <div class="form-row">
                            @csrf
                            <input type="hidden" name="tipo" value="Incidente" name="tipo" id="tipo">
                            <div class="form-group col-md-6">
                                <label for="sistema">Incidencia</label>
                                <input type="text" name="sistema"
                                    class="form-control  @error('sistema') is-invalid @enderror"
                                    value="{{ old('sistema') }}" oninput="this.value = this.value.toUpperCase()" required>
                                @error('sistema')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-3 mx-auto">
                                <label for="">&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">Guardar</button>
                            </div>
                            <div class="form-group col-3 mx-auto">
                                <label for="">&nbsp;</label>
                                <a href="{{ route('admin.contenido.ctg.incidencia.index') }}" class="btn btn-danger btn-block">Cancelar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('adminlte/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/moment/locales.js') }}"></script>
    <script src="{{ asset('js/sistema/asset-create.js') }}"></script>
@endpush
