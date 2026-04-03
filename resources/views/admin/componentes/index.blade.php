@extends('admin.layout')

@section('title', '| Componentes')

@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Componentes
                    <!-- <small>Listado</small> -->
                </h1>
            </div><!-- /.col -->
            {{-- <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Inventario</li>
                </ol>
            </div><!-- /.col --> --}}
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex bd-highlight">
                <div class="mr-auto bd-highlight">
                    <h3 class="card-title">Listado de Componentes</h3>
                </div>
                <div class="bd-highlight pr-2">
                    <a class="btn btn-primary btn-sm" href="{{ route('admin.components.create') }}">
                        <i class="fa fa-plus"></i>
                    </a>
                </div>
            </div>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <table id="componente-table" class="table table-bordered table-striped table-sm">
                <thead>
                    <tr class="text-center">
                        <th>ID</th>
                        <th>Asignado</th>
                        <th>Componente</th>
                        <th>Fecha Ingreso</th>
                        <th>&nbsp;</th>
                        {{-- <th>Serie</th> --}}
                        {{-- <th>Marca</th> --}}
                        {{-- <th>Modelo</th --}}
                        {{-- <th>Costo</th> --}}
                        {{-- <th>Observaciones</th> --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach ($componentes as $componente)
                        <tr class="text-center">
                            <td>{{ $componente->id }}</td>
                            <td>{{ $componente->equipo->etiqueta }}</td>
                            <td>{{ $componente->name ?? '-' }}</td>
                            <td>{{ $componente->fecha_ingreso }}</td>
                            <td>
                                @if ($componente->deleted_at)
                                    <form method="post"
                                        action="{{ route('admin.components.restore', $componente->id) }}"
                                        style="display: inline;">
                                        @csrf
                                        <button class="btn btn-xs btn-success"
                                            onclick="return confirm('¿Estás seguro de querer activar este componente?')"><i
                                                class="fas fa-check"></i></button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.components.edit', $componente) }}"
                                        class="btn btn-xs btn-info">
                                        <i class="fab fa-angellist	"></i>
                                    </a>
                                    <form method="post"
                                        action="{{ route('admin.components.destroy', $componente) }}"
                                        style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-xs btn-danger"
                                            onclick="return confirm('¿Estás seguro de querer suspender este componente?')"><i
                                                class="fas fa-trash-alt"></i></button>
                                    </form>
                                @endif
                            </td>

                            {{-- <td>{{ $componente->serie ?? '-' }}</td> --}}
                            {{-- <td>{{ $componente->marca ?? '-' }}</td> --}}
                            {{-- <td>{{ $componente->modelo ?? '-' }}</td> --}}
                            {{-- <td>{{ $componente->costo ?? '-' }}</td> --}}
                            {{-- <td>{{ $componente->observaciones }}</td> --}}
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- /.card-body -->
    </div>
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
                $('#componente-table').removeClass('nowrap');
            } else {
                $('#componente-table').addClass('nowrap');
            }

            $('#componente-table').DataTable({
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
