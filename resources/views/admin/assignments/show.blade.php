@extends('admin.layout')

@section('title', '| Detalle de asignación')
@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">DETALLE</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.assignments.list') }}">Lista de asignaciones</a></li>
                    <li class="breadcrumb-item active">Detalle de asignación</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">

                    <table id="assignments-table" class="table table-bordered table-sm mt-4">
                        <thead>
                            <tr>
                                <th>Equipo</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>Etiqueta</th>
                                <th>Empresa</th>
                                <th>Fecha de asignación</th>
                                <th>Fecha de aceptación</th>
                                @hasanyrole('Soporte|Admin')
                                    <th>Revisión</th>
                                @endhasanyrole
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($assignments as $assignment)
                                <tr>
                                    <td>{{ $assignment->name }}</td>
                                    <td>{{ $assignment->marca }}</td>
                                    <td>{{ $assignment->modelo }}</td>
                                    <td>{{ $assignment->etiqueta }}</td>
                                    <td>{{ $assignment->company->name }}</td>
                                    <td>{{ $assignment->date_assignment }}</td>
                                    @if ($assignment->acepted_at)
                                        <td>{{ $assignment->acepted_at }}</td>
                                    @else
                                        @if ($equipoAsignado->contains($assignment->id))
                                            <td>
                                                <button type="button" class="btn btn-info btn-sm"
                                                    onclick="aceptar({{ $assignment->id }})">Aceptar</button>
                                            </td>
                                        @else
                                            <td>{{ 'Sin Aceptar' }}</td>
                                        @endif
                                    @endif
                                    @hasanyrole('Soporte|Admin')
                                        @if ($assignment->revision == 1)
                                            <td>Revisado</td>
                                        @elseif(!is_null($assignment->review_observations))
                                            <td>{{ $assignment->review_observations }}</td>
                                        @else
                                            <td>Sin Revisar</td>
                                        @endif
                                    @endhasanyrole
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- DataTables -->
    <script src="{{ asset('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
        $(function() {
            if ($(window).width() < 576) {
                $('#assignments-table').removeClass('nowrap');
            } else {
                $('#assignments-table').addClass('nowrap');
            }

            var table = $('#assignments-table').DataTable({
                "processing": true,
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                language: {
                    'url': '../../js/spanish.json',
                },
                order: [
                    [0, 'asc']
                ],
                columnDefs: [{
                        orderable: false,
                        targets: [3]
                    },
                    {
                        className: "text-center",
                        targets: [3]
                    },
                ],
            });
        });

        function aceptar(id) {
            $.ajax({
                url: "{{ route('admin.aceptaProducto') }}",
                data: {
                    id: id
                },
                beforeSend: function() {
                    // alert('before send');
                }
            }).done(function(response) {
                alert(response);
                location.reload();
            });
        }
    </script>
@endpush
