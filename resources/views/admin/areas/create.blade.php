@extends('admin.layout')

@section('title', '| Crear nueva area')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Nueva Area') }}
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.areas.store') }}" method="post" class="needs-validation" novalidate>
                        <div class="form-row">
                            @csrf
                            <div class="form-group col-md-6">
                                <label for="name">Nombre</label>
                                <input type="text" name="name"
                                    class="form-control  @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                    required>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label for="">&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">Guardar area</button>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="">&nbsp;</label>
                                <a href="{{ route('admin.areas.index') }}" class="btn btn-danger btn-block">Cancelar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/sistema/area-create.js') }}"></script>
@endpush
