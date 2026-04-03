@extends('admin.layout')

@section('title', '| Asignación de equipo')
@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">ASIGNAR</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.assignments.index') }}">Asignar</a></li>
                    <li class="breadcrumb-item active">Asignación de equipo</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Asignación de equipo') }}</div>
                <div class="card-body">
                    <form action="{{ route('admin.assignments.store') }}" method="post" enctype="multipart/form-data"
                        class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="id" value="{{ old('id', $product->id) }}" class="form-control">
                        <input type="hidden" name="costo_estado" id="costo_estado_input"
                            value="{{ old('costo_estado', $product->costo) }}">
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Serie: </label> {{ $product->serie }}
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Equipo: </label> {{ $product->name }}
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Marca: </label> {{ $product->marca }}
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Modelo: </label> {{ $product->modelo }}
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Etiqueta: </label> {{ $product->etiqueta }}
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Costo: </label> <span
                                            id="costo">{{ old('costo', $product->costo) }}</span>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Costo Con Descuento: </label> <span
                                            id="costo_estado">{{ old('costo_estado', $product->costo) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div>
                                    <label for="employee_id">Empleado</label>
                                    <select class="custom-select select2 @error('employee_id') is-invalid @enderror"
                                        name="employee_id" id="employee_id" value="{{ old('employee_id') }}" required>
                                        <option value="" selected disabled>Selecciona un empleado</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}"
                                                {{ old('employee_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name . ' ' . $user->ap_paterno . ' ' . $user->ap_materno }}
                                                ({{ $user->usuario }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('employee_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ 'Selecciona un empleado' }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div>
                                    <label for="estado_equipo">Estado Equipo</label>
                                    <select class="custom-select select2 @error('estado_equipo') is-invalid @enderror"
                                        name="estado_equipo" id="estado_equipo" value="{{ old('estado_equipo') }}"
                                        required>
                                        <option value="" selected disabled>Selecciona un estado de equipo...</option>
                                        @foreach ($estadoEquipos as $estado)
                                            <option value="{{ $estado->id }}" data-descuento="{{ $estado->descuento }}"
                                                {{ old('estado_equipo') == $estado->id ? 'selected' : '' }}>
                                                {{ $estado->estado }}</option>
                                        @endforeach
                                    </select>
                                    @error('estado_equipo')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ 'Selecciona un estado del equipo' }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="ubicacion">Ubicación</label>
                                <input type="text" name="ubicacion" id="ubicacion" class="form-control"
                                    value="{{ old('ubicacion') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="responsiva">Evidencia responsiva</label>
                                <input type="file" name="responsiva" id="responsiva"
                                    class="form-control-file @error('responsiva') is-invalid @enderror" accept=".pdf">
                                @error('responsiva')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong> <!-- Muestra el mensaje de error de validación -->
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-12">
                                <label for="observations">Observaciones</label>
                                <textarea name="observations" class="form-control @error('observations') is-invalid @enderror" rows="3">{{ old('observations') }}</textarea>
                                @error('observations')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ 'El campo es obligatorio' }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <button type="submit" class="btn btn-primary btn-block">Asignar equipo</button>
                            </div>
                            <div class="form-group col-md-6">
                                <a href="{{ route('admin.products.index') }}" class="btn btn-danger btn-block">Cancelar</a>
                            </div>
                        </div>
                    </form>
                </div>
                <table class="table-auto w-full border-collapse border border-gray-300">
                    <thead class="bg-gray-50">
                        <tr class="text-center">
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción
                            </th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Descuento</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Tiempo de Uso
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($estadoEquipos as $equipos)
                            <tr class="text-center hover:bg-gray-100">
                                <td class="px-6 py-4 whitespace-nowrap">{{ $equipos->estado }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $equipos->descripcion }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $equipos->descuento }}%</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $equipos->tiempo_uso ?? 'NO APLICA' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/select2/css/select2.min.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('adminlte/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2').select2({
            tags: false
        });

        $('#estado_equipo').change(function() {
            const costoOriginal = parseFloat($('#costo').text());
            const selectedOption = $(this).find(':selected');
            const descuento = parseFloat(selectedOption.data('descuento')) || 0;
            const nuevoCosto = costoOriginal * (descuento / 100);
            $('#costo_estado').text(nuevoCosto.toFixed(2));
            $('#costo_estado_input').val(nuevoCosto.toFixed(2));
        });
    </script>
@endpush
