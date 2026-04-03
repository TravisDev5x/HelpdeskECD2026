@extends('admin.layout')

@section('title', '| Detalle de Asignación')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <span>{{ __('Historico De Asignación') }}</span>
                <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-danger ml-auto">Regresar</a>
              </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered table-sm text-center">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>No.Empleado</th>
                                        <th>Nombre</th>
                                        <th>Puesto</th>
                                        <th>Departamento</th>
                                        <th>Estatus</th>
                                        <th>Costo</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($historico as $his)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $his->employee->usuario ?? 'Sin Usuario' }}</td>
                                            <td>{{ $his->employee->name . ' ' . $his->employee->ap_paterno . ' ' . $his->employee->ap_materno }}
                                            </td>
                                            <td>{{ $his->employee->position->name ?? 'Sin Puesto' }}</td>
                                            <td>{{ $his->employee->department->name ?? 'Sin Departamento' }}</td>
                                            <td>{{ $his->assignment }}</td>
                                            <td>{{ $his->costo_estado ? '$' . $his->costo_estado : 'SIN COSTO' }}</td>
                                            <td>{{ $his->created_at }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
