@extends('admin.layout')

@section('title', '| Crear nuevo componente')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Nuevo Componente') }}
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.components.store') }}" method="post" class="needs-validation" novalidate>
                        <div class="form-row">
                            @csrf
                            <div class="form-group col-md-6">
                                <label for="name">Componente</label>
                                <select class="form-control  @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" name="name" id="name" required>
                                    <option value="" selected disabled>Selecciona un componente...</option>
                                    @foreach ($componenteCtgs as $ctg)
                                        <option value="{{ $ctg->contenido }}">{{ $ctg->contenido }}</option>
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
                                        <option value="{{ $equipo->id }}">{{ $equipo->etiqueta }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('producto_id'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('producto_id') }}
                                    </div>
                                @endif
                            </div>
                            <div class="form-group col-md-6">
                                <label for="serie">Serie</label>
                                <input type="text" name="serie"
                                    class="form-control  @error('serie') is-invalid @enderror" value="{{ old('serie') }}"
                                    required>
                                @error('serie')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="marca">Marca</label>
                                <input type="text" name="marca"
                                    class="form-control  @error('marca') is-invalid @enderror" value="{{ old('marca') }}"
                                    required>
                                @error('marca')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="modelo">Modelo</label>
                                <input type="text" name="modelo"
                                    class="form-control  @error('modelo') is-invalid @enderror" value="{{ old('modelo') }}"
                                    required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="capacidad">Capacidad</label>
                                <input type="text" name="capacidad" class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Fecha de ingreso</label>
                                <div>
                                    <input type="date" value="{{ old('fecha_ingreso') }}" name="fecha_ingreso"
                                        class="form-control" id="datepicker1" required />
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="company_id">Empresa</label>
                                <select class="custom-select @error('company_id') is-invalid @enderror" name="company_id"
                                    id="company_id" required>
                                    <option value="" selected disabled>Seleccione una empresa...</option>
                                    @foreach ($companies as $companie)
                                        <option value="{{ $companie->id }}">{{ $companie->name }}</option>
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
                                    <option value="OPERABLE">OPERABLE</option>
                                    <option value="INOPERABLE">INOPERABLE</option>
                                    <option value="STOCK">STOCK</option>
                                    <option value="ROBADO">ROBADO</option>
                                    <option value="RECICLADO">RECICLADO</option>
                                    <option value="EN_REPARACION">EN REPARACION</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="costo">Costo</label>
                                <input type="number" name="costo"
                                    class="form-control  @error('costo') is-invalid @enderror" value="{{ old('costo') }}"
                                    required>
                                @error('costo')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-12">
                                <label for="observacion">Observación</label>
                                <textarea class="form-control" name="observacion" id="observacion" rows="1"> </textarea>
                            </div>
                            <div class="form-group col-md-6">
                                <button type="submit" class="btn btn-primary btn-block">Guardar Componente</button>
                            </div>
                            <div class="form-group col-md-6">
                                <a href="{{ route('admin.components.index') }}" class="btn btn-danger btn-block">Cancelar</a>
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
    <script src="{{ asset('js/sistema/assignments-create.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('adminlte/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        //Initialize Select2 Elements
        $('.select2').select2({
            tags: false
        });
    </script>
@endpush
