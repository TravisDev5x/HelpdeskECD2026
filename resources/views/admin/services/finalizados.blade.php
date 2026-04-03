@extends('admin.layout')

@section('title', '| Servicios Finalizados')

@section('content')
    <section class="content">
        @include('admin.partials.indicadores')
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Tickets Cerrados</h3>
                    </div>
                    <div class="card-body">
                        <table id="services-table"
                            class="table table-bordered table-striped table-sm table-condensed table-fixed">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Solicitante</th>
                                    <th>Servicio</th>
                                    <th>Fecha de solicitud</th>
                                    <th>Fecha de termino</th>
                                    <th>T. Respuesta</th>
                                    <th>Atendió</th>
                                    <th>Historial</th>
                                    {{-- <th></th> --}}
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <div class="modal fade" id="historial" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">


            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="activocritico">Historico</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table id="table-modal" class="table table-bordered table-striped table-sm table-condensed table-fixed">
                        <thead>
                            <tr>
                                <th>Atendió</th>
                                <th>Descripción</th>
                                <th>Observacion</th>
                                <th>Solucion</th>
                                <th>fecha</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="relanzarModal" tabindex="-1" aria-labelledby="relanzarModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="relanzarModalLabel">Relanzar Servicio</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="relanzarForm">
                        <input type="hidden" id="serviceId">
                        <div class="form-group">
                            <label for="comentario">Comentario:</label>
                            <textarea id="comentario" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="guardarComentario()">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
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
    <script src="{{ asset('adminlte/plugins/moment/moment.min.js') }}"></script>

    <script src="{{ asset('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script>
        if ($(window).width() < 576) {
            $('#services-table').removeClass('nowrap');
        } else {
            $('#services-table').addClass('nowrap');
        }

        $(function() {
            $('body').tooltip({
                selector: '[data-toggle="tooltip"]'
            });
            $('#services-table').DataTable({
                "processing": true,
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "serverSide": true,
                language: {
                    'url': '../js/spanish.json',
                },

                ajax: {
                    "url": '{{ route('get_finalizados') }}',
                    "type": 'GET',

                },
                columns: [

                    {
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: function(row) {
                            if (row.user) {
                                var fullName = row.user.name;
                                if (row.user.ap_paterno) {
                                    fullName += ' ' + row.user.ap_paterno;
                                }
                                if (row.user.ap_materno) {
                                    fullName += ' ' + row.user.ap_materno;
                                }
                                return fullName.trim();
                            }
                        },
                        name: 'user.name',
                    },
                    {
                        data: 'failure.name',
                        name: 'failure.name'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'fecha_fin',
                        name: 'fecha_fin'
                    },
                    {
                        data: null,
                        searchable: false,
                        'mRender': function(datos) {



                            var dateOne = moment(datos.created_at);
                            var dateTwo = moment(datos.fecha_fin);
                            var result = dateTwo.diff(dateOne, 'minutes')
                            var mostrar = result + ' min'
                            return mostrar

                        }
                    },
                    {
                        data: function(row) {
                            if (row.responsable) {
                                var fullName = row.responsable.name;
                                if (row.responsable.ap_paterno) {
                                    fullName += ' ' + row.responsable.ap_paterno;
                                }
                                if (row.responsable.ap_materno) {
                                    fullName += ' ' + row.responsable.ap_materno;
                                }
                                return fullName.trim();
                            }
                        },
                        name: 'responsable.name',
                    },
                    {
                        data: null,
                        searchable: false,
                        'mRender': function(datos) {

                            var boton =
                                ' <button class="btn btn-info btn-xs" title="historial" onclick="historial(' +
                                "'" + datos.id + "'" +
                                ')"> <i class="fas fa-file-alt"></i> </button>';
                            return boton

                        }
                    },
                    // {
                    //     data: null,
                    //     render: function(data) {
                    //         var fechaFin = moment(data.fecha_fin);
                    //         var ahora = moment();
                    //         var diferenciaHoras = ahora.diff(fechaFin, 'hours');

                    //         if (diferenciaHoras < 24) {
                    //             return '<button class="btn btn-secondary btn-sm" onclick="comentarioCliente(' +
                    //                 data.id + ')">Relanzar</button>';
                    //         } else {
                    //             return '';
                    //         }
                    //     }
                    // }
                ],
                order: [
                    [0, 'desc']
                ],
            });
        });

        function historial(id) {
            var table_histo = $('#table-modal').DataTable({
                "processing": true,
                "serverSide": true,
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,

                ajax: {
                    "url": '{{ route('get_historial_services') }}',
                    "type": 'GET',
                    "data": {
                        id: id
                    },

                },
                columns: [{
                        data: 'nombre_r',
                        name: 'nombre_r'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'observations',
                        name: 'observations',
                        render: function(data) {
                            return data ? data : 'Aun sin observaciones';
                        }
                    },
                    {
                        data: 'solution',
                        name: 'solution',
                        render: function(data) {
                            return data ? data : 'Aun sin solución';
                        }
                    },
                    {
                        data: 'fecha',
                        name: 'fecha'
                    },


                ],

                columnDefs: [{
                        className: "text-center",
                        targets: [1]
                    },
                    {
                        orderable: false,
                        targets: [1]
                    },
                ],
            });
            $('#historial').modal('show')
            table_histo.destroy();
        }

        function comentarioCliente(id) {
            $('#serviceId').val(id); // Guardamos el ID del servicio en un input oculto
            $('#comentario').val(''); // Limpiamos el campo de texto
            $('#relanzarModal').modal('show'); // Mostramos el modal
        }

        function guardarComentario() {
            var id = $('#serviceId').val();
            var comentario = $('#comentario').val();

            if (comentario.trim() === '') {
                alert('Por favor, ingrese un comentario.');
                return;
            }

            $.ajax({
                url: '{{ route('relanzar_servicio') }}', // Ruta a definir en web.php
                type: 'POST',
                data: {
                    id: id,
                    comentario: comentario,
                    _token: '{{ csrf_token() }}' // Protección CSRF
                },
                success: function(response) {
                    alert('Servicio relanzado correctamente.');
                    $('#relanzarModal').modal('hide');
                    $('#services-table').DataTable().ajax.reload(); // Recargar la tabla
                },
                error: function(xhr) {
                    alert('Error al relanzar el servicio.');
                }
            });
        }
    </script>
@endpush
