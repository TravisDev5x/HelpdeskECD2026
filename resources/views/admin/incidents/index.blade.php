@extends('admin.layout')

@section('title', '| Incidencias')

@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">INCIDENCIAS
                    <!-- <small>Listado</small> -->
                </h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Incidencias</li>
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
                    <a class="btn btn-primary btn-sm" href="{{ route('admin.incidents.create') }}">
                        <i class="fa fa-plus"></i> Crear incidencia
                    </a>
                </div>
                {{-- <div class="bd-highlight pr-2">
                    <a class="btn btn-primary btn-sm" href="{{ route('admin.incidentsEvents') }}">
                        <i class="fa fa-plus"></i> Crear evento
                    </a>
                </div> --}}
            </div>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <table id="incidents-table" class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Inhabilitación</th>
                        <th>Sistema informatico</th>
                        <th>Causa</th>
                        <th>Responsable</th>
                        <th>Acciones</th>
                        <th>Habilitación</th>
                        <th>T. Inhabilitación</th>
                        <th>Observaciones</th>
                        <th>Lecciones aprendidas</th>
                        <th>Criticidad</th>
                        <th>Editar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($incidents as $incident)
                        <tr>
                            <td>{{ $incident->id }}</td>
                            <td>{{ $incident->tipo }}</td>
                            <td>{{ $incident->disqualification_date }}</td>
                            <td>{{ $incident->sistema }}</td>
                            <td>{{ $incident->causa }}</td>
                            <td>{{ $incident->responsable }}</td>
                            <td>{{ $incident->acciones }}</td>
                            <td>{{ $incident->enablement_date }}</td>
                            <td>
                                @if ($incident->enablement_date != null)
                                    {{ $incident->disqualification_date->diff($incident->enablement_date)->format('%d d, %H:%I:%S') }}
                                @else
                                    {{ $incident->disqualification_date->diff(now())->format('%d d, %H:%I:%S') }}
                                @endif
                            </td>
                            <td>{{ $incident->observations }}</td>
                            <td>{{ $incident->lecciones }}</td>
                            <td>@switch($incident->criticidad)
                                    @case(1)
                                        BAJA <i class="fa fa-thermometer-empty d-flex justify-content-between" aria-hidden="true" style="color: green"></i>
                                    @break
                                    @case(2)
                                    MEDIA <i class="fa fa-thermometer-half d-flex justify-content-between" aria-hidden="true" style="color: orange"></i>
                                    @break
                                    @case(3)
                                    ALTA <i class="fa fa-thermometer-full d-flex justify-content-between" aria-hidden="true" style="color: red"></i>
                                    @break

                                    @default

                                @endswitch
                            </td>
                            <td>
                                @can('update incident')
                                    {{-- @if ($incident->enablement_date == null) --}}
                                    <a href="{{ route('admin.incidents.edit', $incident) }}" class="btn btn-xs btn-info">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    {{-- @endif --}}
                                @endcan
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
