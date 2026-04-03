@extends('admin.layout')

@section('title', '| Catalogo Productos')

@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Catalogo Productos
                </h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Catalogo Productos / Componentes</li>
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
                    <h3 class="card-title">Listado de Productos / Componentes</h3>
                </div>
                <div class="bd-highlight pr-2">
                    <a class="btn btn-primary btn-sm" href="{{ route('admin.contenido.ctg.productos.create') }}">
                        <i class="fa fa-plus"></i> Crear
                    </a>
                </div>
            </div>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <table id="sistema-table" class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Estatus</th>
                        <th>Editar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sistemas as $sistema)
                        <tr>
                            <td>{{ $sistema->id }}</td>
                            <td>{{ $sistema->contenido }}</td>
                            <td>{{ $sistema->ctg->catalogo }}</td>
                            <td>{{ $sistema->deleted_at ? 'INACTIVO' : 'ACTIVO' }}</td>
                            <td>
                                @if ($sistema->deleted_at)
                                    <form method="post" action="{{ route('admin.contenido.ctg.productos.restore', $sistema->id) }}"
                                        style="display: inline;">
                                        @csrf
                                        <button class="btn btn-xs btn-success"
                                            onclick="return confirm('¿Estás seguro de querer activar este producto?')"><i
                                                class="fas fa-check"></i></button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.contenido.ctg.productos.edit', $sistema) }}" class="btn btn-xs btn-info">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <form method="post" action="{{ route('admin.contenido.ctg.productos.destroy', $sistema) }}"
                                        style="display: inline;">
                                        @csrf
                                        <button class="btn btn-xs btn-danger"
                                            onclick="return confirm('¿Estás seguro de querer suspender este producto?')"><i
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
                $('#sistema-table').removeClass('nowrap');
            } else {
                $('#sistema-table').addClass('nowrap');
            }

            $('#sistema-table').DataTable({
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
