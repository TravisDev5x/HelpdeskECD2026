@extends('admin.layout')

@section('title', '| Editar falla')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Editar Falla') }}
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.failures.update', $failure) }}" method="post" class="needs-validation"
                        novalidate>
                        <div class="form-row">
                            @csrf
                            @method('PUT')
                            <div class="form-group col-md-6">
                                <label for="name">Nombre</label>
                                <input type="text" name="name"
                                    class="form-control  @error('name') is-invalid @enderror"
                                    value="{{ old('name', $failure->name) }}" required>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="area_id">Area</label>
                                <select class="custom-select @error('area_id') is-invalid @enderror" name="area_id"
                                    id="area_id" required>
                                    <option value="" selected disabled>Seleccione un departamento...</option>
                                    @foreach ($areas as $area)
                                        <option value="{{ $area->id }}"
                                            {{ old('area_id', $failure->area_id) == $area->id ? 'selected' : '' }}>
                                            {{ $area->name }}</option>
                                    @endforeach
                                </select>
                                @error('area_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-3">
                                <button type="submit" class="btn btn-primary btn-block">Actualizar falla</button>
                            </div>
                            <div class="form-group col-md-3">
                                <a href="{{ route('admin.failures.index') }}" class="btn btn-danger btn-block">Cancelar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/sistema/failure-create.js') }}"></script>
@endpush
