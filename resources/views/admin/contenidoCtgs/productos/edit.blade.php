@extends('admin.layout')

@section('title', '| Actualizar')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Complementar') }}
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.contenido.ctg.productos.update', $sistema) }}" method="post" class="needs-validation"
                        novalidate>
                        <div class="form-row">
                            @csrf
                            @method('PUT')
                            <div class="form-group col-md-6">
                                <label for="producto">Producto</label>
                                <input type="text" name="producto"
                                    class="form-control  @error('producto') is-invalid @enderror"
                                    value="{{ old('producto', $sistema->contenido) }}" oninput="this.value = this.value.toUpperCase()" required>
                                @error('producto')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="tipo">Tipo</label>
                                <select name="tipo" id="tipo" class="form-control @error('tipo') is-invalid @enderror" required>
                                    <option {{ old('tipo', $sistema->ctg_id) == '2' ? 'selected' : '' }} value="1">PRODUCTO</option>
                                    @unlessrole('Mantenimiento|Operaciones')
                                    <option {{ old('tipo', $sistema->ctg_id) == '3' ? 'selected' : '' }} value="3">COMPONENTE</option>
                                    @endunlessrole
                                </select>
                                @error('tipo')
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
