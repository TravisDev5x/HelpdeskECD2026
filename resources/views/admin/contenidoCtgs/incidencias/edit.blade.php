@extends('admin.layout')

@section('title', '| Actualizar')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Complementar') }}
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.contenido.ctg.incidencia.update', $sistema) }}" method="post" class="needs-validation"
                        novalidate>
                        <div class="form-row">
                            @csrf
                            @method('PUT')
                            <div class="form-group col-md-6">
                                <label for="sistema">Sistema</label>
                                <input type="text" name="sistema"
                                    class="form-control  @error('sistema') is-invalid @enderror"
                                    value="{{ old('sistema', $sistema->contenido) }}" oninput="this.value = this.value.toUpperCase()" required>
                                @error('sistema')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <input type="hidden" name="sistema_id" value="{{ old('sistema', $sistema->id) }}">
                            <div class="form-group col-3 mx-auto">
                                <label for="">&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">Actualizar</button>
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
