@extends('admin.layout')

@section('title', '| Empresas')

@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">EMPRESAS
                    <!-- <small>Listado</small> -->
                </h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Empresas</li>
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
                    <h3 class="card-title">Listado de empresas</h3>
                </div>
                <div class="bd-highlight pr-2">
                    <a class="btn btn-primary btn-sm" href="{{ route('admin.companies.create') }}">
                        <i class="fa fa-plus"></i> Crear empresa
                    </a>
                </div>
            </div>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <table id="companies-table" class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($companies as $company)
                        <tr>
                            <td>{{ $company->id }}</td>
                            <td>{{ $company->name }}</td>
                            <td>{{ $company->deleted_at ? 'INACTIVA' : 'ACTIVA' }}</td>
                            <td>
                                @if ($company->deleted_at)
                                    <form method="post" action="{{ route('admin.companies.restore', $company->id) }}"
                                        style="display: inline;">
                                        @csrf
                                        <button class="btn btn-xs btn-success" title="Activar Empresa"
                                            onclick="return confirm('¿Estás seguro de querer restaurar esta empresa?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-xs btn-info">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <form method="post" title="Desactivar Empresa"
                                        action="{{ route('admin.companies.destroy', $company) }}" style="display: inline;">
                                        @method('delete')
                                        @csrf
                                        <button class="btn btn-xs btn-danger"
                                            onclick="return confirm('¿Estás seguro de querer eliminar esta empresa?')"><i
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
                $('#companies-table').removeClass('nowrap');
            } else {
                $('#companies-table').addClass('nowrap');
            }

            $('#companies-table').DataTable({
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
