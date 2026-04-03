@extends('admin.layout')

@section('title', '| Editar puesto')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Editar Puesato') }}
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.positions.update', $position) }}" method="post" class="needs-validation"
                        novalidate>
                        <div class="form-row">
                            @csrf
                            @method('PUT')
                            <div class="form-group col-md-6">
                                <label for="name">Nombre</label>
                                <input type="text" name="name"
                                    class="form-control  @error('name') is-invalid @enderror"
                                    value="{{ old('name', $position->name) }}" required>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="department_id">Departamento</label>
                                <select class="custom-select @error('department_id') is-invalid @enderror"
                                    name="department_id" id="department_id" required>
                                    <option value="" selected disabled>Seleccione un departamento...</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}"
                                            {{ old('department_id', $position->department_id) == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}</option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="area">Area</label>
                                <input type="text" name="area"
                                    class="form-control  @error('area') is-invalid @enderror"
                                    value="{{ old('area', $position->area) }}" required>
                                @error('area')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="extension">extension</label>
                                <input type="number" name="extension"
                                    class="form-control  @error('extension') is-invalid @enderror"
                                    value="{{ old('extension', $position->extension) }}">
                                @error('extension')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label for="">&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">Actualizar puesto</button>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="">&nbsp;</label>
                                <a href="{{ route('admin.positions.index') }}"
                                    class="btn btn-danger btn-block">Cancelar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/sistema/position-create.js') }}"></script>
@endpush
