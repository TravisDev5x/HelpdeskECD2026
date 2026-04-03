@extends('admin.layout')

@section('title', '| Crear nuevo producto')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Nuevo Producto / Componente') }}
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.contenido.ctg.productos.store') }}" method="post" class="needs-validation" novalidate>
                        <div class="form-row">
                            @csrf
                            <div class="form-group col-md-6">
                                <label for="producto">Producto</label>
                                <input type="text" name="producto"
                                    class="form-control  @error('producto') is-invalid @enderror"
                                    value="{{ old('producto') }}" oninput="this.value = this.value.toUpperCase()" required>
                                @error('producto')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="tipo">Tipo</label>
                                <select name="tipo" id="tipo" class="form-control @error('tipo') is-invalid @enderror" required>
                                    <option value="" selected disabled>Selecciona un tipo...</option>
                                    <option value="1">PRODUCTO</option>
                                    @unlessrole('Mantenimiento|Operaciones')
                                    <option value="3">COMPONENTE</option>
                                    @endunlessrole
                                </select>
                                @error('tipo')
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
                                <a href="{{ route('admin.contenido.ctg.productos.index') }}" class="btn btn-danger btn-block">Cancelar</a>
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
