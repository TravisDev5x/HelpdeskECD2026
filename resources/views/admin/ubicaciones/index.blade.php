@extends('admin.layout')

@section('title', '| Ubicaciones')

@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">UBICACIONES
                    <!-- <small>Listado</small> -->
                </h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Ubicaciones</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex bd-highlight">
                <div class="mr-auto bd-highlight">
                    <h3 class="card-title">Listado de ubicaciones</h3>
                </div>
                <div class="bd-highlight pr-2">
                    <a class="btn btn-primary btn-sm" href="{{ route('admin.ubicaciones.create') }}">
                        <i class="fa fa-plus"></i> Crear ubicacion
                    </a>
                </div>
            </div>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <table id="ubicaciones-table" class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ubicacion</th>
                        <th>Sede</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ubicaciones as $ubicacion)
                        <tr>
                            <td>{{ $ubicacion->id }}</td>
                            <td>{{ $ubicacion->ubicacion }}</td>
                            <td>{{ $ubicacion->sede->sede ?? 'SIN SEDE' }}</td>
                            <td>{{ $ubicacion->deleted_at ? 'INACTIVA' : 'ACTIVA' }}</td>
                            <td>
                                @if ($ubicacion->deleted_at)
                                    <form method="post" action="{{ route('admin.ubicaciones.restore', $ubicacion->id) }}"
                                        style="display: inline;">
                                        @csrf
                                        <button class="btn btn-xs btn-success" title="Activar ubicacion"
                                            onclick="return confirm('¿Estás seguro de querer restaurar esta ubicacion?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.ubicaciones.edit', $ubicacion) }}" class="btn btn-xs btn-info">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <form method="post" title="Desactivar ubicacion"
                                        action="{{ route('admin.ubicaciones.destroy', $ubicacion) }}" style="display: inline;">
                                        @method('delete')
                                        @csrf
                                        <button class="btn btn-xs btn-danger"
                                            onclick="return confirm('¿Estás seguro de querer eliminar esta ubicacion?')"><i
                                                class="fas fa-trash-alt"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
@endsection

@push('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('css/style-datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endpush

@push('scripts')
    <!-- DataTables -->
    <script src="{{ asset('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
        $(function() {

            if ($(window).width() < 576) {
                $('#ubicaciones-table').removeClass('nowrap');
            } else {
                $('#ubicaciones-table').addClass('nowrap');
            }

            $('#ubicaciones-table').DataTable({
                "processing": true,
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                language: {
                    'url': '../js/spanish.json',
                },
            });
        });
    </script>
@endpush
