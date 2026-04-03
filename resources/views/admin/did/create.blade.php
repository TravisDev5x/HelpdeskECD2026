@extends('admin.layout')

@section('title', '| Crear nuevo usuario')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Nuevo Usuario') }}
                </div>

                <div class="card-body">
                    <form action="{{ route('did.store') }}" method="post" class="needs-validation" novalidate>
                        <div class="form-row">
                            @csrf
                            <div class="form-group col-md-6">
                                <label for="did">DID</label>
                                <input type="text" name="did"
                                    class="form-control  @error('did') is-invalid @enderror" value="{{ old('did') }}"
                                    required>
                                @error('did')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="cuenta">Cuenta</label>
                                <input type="text" name="cuenta"
                                    class="form-control  @error('cuenta') is-invalid @enderror" value="{{ old('cuenta') }}"
                                    required>
                                @error('cuenta')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="proveedor">Proveedor</label>
                                <select class="custom-select @error('proveedor') is-invalid @enderror" name="proveedor"
                                    id="proveedor" required>
                                    <option value="" selected disabled>Seleccione un departamento...</option>
                                    <option value="Inconcert">Inconcert</option>
                                    <option value="Ccc">CCC</option>
                                    <option value="Alestra">Alestra</option>
                                    <option value="Marcatel">Marcatel</option>
                                    <option value="Metro carrier">Metro carrier</option>
                                    <option value="Convergia">Convergia</option>
                                </select>
                                @error('proveedor')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label for="tipo">tipo</label>
                                <select class="custom-select @error('tipo') is-invalid @enderror" name="tipo"
                                    id="tipo" required>
                                    <option value="" selected disabled>Seleccione un departamento...</option>
                                    <option value="entrada">Entrada</option>
                                    <option value="salida">Salida</option>
                                </select>
                                @error('tipo')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group col-md-3">
                                <button type="submit" class="btn btn-primary btn-block">Crear DID</button>
                            </div>
                            <div class="form-group col-md-3">
                                <a href="{{ route('did') }}" class="btn btn-danger btn-block">Cancelar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('styles')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/select2/css/select2.min.css') }}">
@endpush
@push('scripts')
    <script src="{{ asset('js/sistema/user-create.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('adminlte/plugins/select2/js/select2.full.min.js') }}"></script>
@endpush
