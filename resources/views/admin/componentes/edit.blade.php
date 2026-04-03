@extends('admin.layout')

@section('title', '| Editar componente')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">{{ __('Editar Componente') }}
            </div>
            <div class="card-body">
                <form action="{{ route('admin.components.update', $componente->id) }}" method="post"
                    class="needs-validation" novalidate>
                    <div class="form-row">
                        @csrf
                        @method('PUT')
                        <div class="form-group col-md-6">
                            <label for="name">Componente</label>
                            <select class="form-control @error('name') is-invalid @enderror" name="name" id="name"
                                required>
                                <option value="" selected disabled>Selecciona un componente...</option>
                                @foreach ($componenteCtgs as $ctg)
                                <option value="{{ $ctg->contenido }}" {{ old('name', $componente->name) ==
                                    $ctg->contenido ? 'selected' : '' }}>
                                    {{ $ctg->contenido }}
                                </option>
                                @endforeach
                            </select>
                            @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="producto_id">Equipo para asignar</label>
                            <select class="custom-select select2 @error('producto_id') is-invalid @enderror"
                                name="producto_id" id="producto_id" required>
                                <option value="" selected disabled>Selecciona un equipo...</option>
                                @foreach ($equipos as $equipo)
                                <option value="{{ $equipo->id }}" {{ old('producto_id', $componente->producto_id) ==
                                    $equipo->id ? 'selected' : '' }}>
                                    {{ $equipo->etiqueta }}
                                </option>
                                @endforeach
                            </select>
                            @error('producto_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="serie">Serie</label>
                            <input type="text" name="serie" class="form-control  @error('serie') is-invalid @enderror"
                                value="{{ old('serie', $componente->serie) }}" required>
                            @error('serie')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="marca">Marca</label>
                            <input type="text" name="marca" class="form-control  @error('marca') is-invalid @enderror"
                                value="{{ old('marca', $componente->marca) }}" required>
                            @error('marca')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="modelo">Modelo</label>
                            <input type="text" name="modelo" class="form-control  @error('modelo') is-invalid @enderror"
                                value="{{ old('modelo', $componente->modelo) }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="capacidad">Capacidad</label>
                            <input type="text" name="capacidad" class="form-control"
                                value="{{ old('capacidad', $componente->capacidad) }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Fecha de ingreso</label>
                            <div>
                                <input type="date" value="{{ old('fecha_ingreso', $componente->fecha_ingreso) }}"
                                    name="fecha_ingreso" class="form-control" id="datepicker1" required />
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="company_id">Empresa</label>
                            <select class="custom-select @error('company_id') is-invalid @enderror" name="company_id"
                                id="company_id" required>
                                <option value="" selected disabled>Seleccione una empresa...</option>
                                @foreach ($companies as $companie)
                                <option value="{{ $companie->id }}" {{ old('company_id', $componente->company_id) ==
                                    $companie->id ? 'selected' : '' }}>
                                    {{ $companie->name }}</option>
                                @endforeach
                            </select>
                            @error('company_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="status">Status</label>
                            <select class="custom-select @error('status') is-invalid @enderror" name="status"
                                id="status" required>
                                <option value="" selected disabled>Seleccione un status...</option>
                                <option value="OPERABLE" {{ old('status', $componente->status) == 'OPERABLE' ?
                                    'selected' : '' }}>
                                    OPERABLE</option>
                                <option value="INOPERABLE" {{ old('status', $componente->status) == 'INOPERABLE' ?
                                    'selected' : '' }}>
                                    INOPERABLE</option>
                                <option value="STOCK" {{ old('status', $componente->status) == 'STOCK' ? 'selected' : ''
                                    }}>
                                    STOCK</option>
                                <option value="ROBADO" {{ old('status', $componente->status) == 'ROBADO' ? 'selected' :
                                    '' }}>
                                    ROBADO</option>
                                <option value="RECICLADO" {{ old('status', $componente->status) == 'RECICLADO' ? 'selected' :
                                    '' }}>
                                    RECICLADO</option>
                                <option value="EN_REPARACION" {{ old('status', $componente->status) == 'EN_REPARACION' ?
                                    'selected' : '' }}>
                                    EN REPARACION</option>
                            </select>
                            @error('status')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="costo">Costo</label>
                            <input type="number" name="costo" class="form-control  @error('costo') is-invalid @enderror"
                                value="{{ old('costo', $componente->costo) }}" required>
                            @error('costo')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group col-md-12">
                            <label for="observacion">Observación</label>
                            <textarea class="form-control" name="observacion" id="observacion"
                                rows="1">{{ old('observacion', $componente->observacion) }}</textarea>
                        </div>
                        <div class="form-group col-md-6">
                            <button type="submit" class="btn btn-success btn-block">Actualiza Componente</button>
                        </div>
                        <div class="form-group col-md-6">
                            <a href="{{ route('admin.components.index') }}"
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
<link rel="stylesheet" href="{{ asset('adminlte/plugins/datepiker/css/bootstrap-datepicker.min.css') }}">
@endpush
@push('scripts')
<!-- date picker -->
<script src="{{ asset('adminlte/plugins/datepiker/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('js/sistema/product-create.js') }}"></script>

<script>
    //Date picker
        $('#datepicker').datepicker({
            autoclose: true,
            locale: 'es',
            format: 'yyyy/mm/dd'
        });
</script>
@endpush