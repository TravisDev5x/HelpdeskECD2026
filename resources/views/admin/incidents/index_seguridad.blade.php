@extends('admin.layout')

@section('title', '| Incidencias')

@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h6 class="m-0 text-dark">REPORTE DE INCIDENCIAS DE CIBERSEGURIDAD Y PROTECCION DE DATOS
                    <!-- <small>Listado</small> -->
                </h6>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Ciberseguridad y Datos</li>
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
                    <h3 class="card-title">Listado de incidencias</h3>
                </div>
                <div class="bd-highlight pr-2">
                    <a class="btn btn-primary btn-sm" href="{{ route('ciberseguridad.create') }}">
                        <i class="fa fa-plus"></i> Crear
                    </a>
                </div>
            </div>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <table id="incidents-table" class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>REPORTA</th>
                        <th>CATEGORIA</th>
                        <th>SUBCATEGORIA</th>
                        <th>CRITICIDAD</th>
                        <th>COMENTARIO</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($datos as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->user->name . ' ' . $item->user->ap_paterno . ' ' . $item->user->ap_materno }}<Ftd>
                            <td>{{ $item->categoria->contenido }}</td>
                            <td>{{ $item->subcategoria->subcategoria }}</td>
                            <td>{{ $item->subcategoria->criticidad }}</td>
                            <td>{{ $item->comentario }}</td>
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
    <link rel="stylesheet"
        href="{{ asset('adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
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
                $('#incidents-table').removeClass('nowrap');
            } else {
                $('#incidents-table').addClass('nowrap');
            }

            $('#incidents-table').DataTable({
                "processing": true,
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "scrollX": true,
                  "responsive": true,
                language: {
                    'url': '../js/spanish.json',
                },
            });
        });

    </script>
@endpush
