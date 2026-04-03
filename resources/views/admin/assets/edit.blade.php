@extends('admin.layout')

@section('title', '| Actualizar activo')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Editar Activo') }}
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.assets.update', $asset) }}" method="post" class="needs-validation"
                        novalidate>
                        <div class="form-row">
                            @csrf
                            @method('PUT')
                            <div class="form-group col-md-6">
                                <label for="name">Nombre</label>
                                <input type="text" name="name"
                                    class="form-control  @error('name') is-invalid @enderror"
                                    value="{{ old('name', $asset->name) }}" required>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label for="">&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">Actualizar Activo</button>
                            </div>
                            <div class="form-group col-md-3">
                              <label for="">&nbsp;</label>
                              <a href="{{ route('admin.assets.index') }}" class="btn btn-danger btn-block">Cancelar</a>
                          </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/sistema/asset-create.js') }}"></script>
@endpush
