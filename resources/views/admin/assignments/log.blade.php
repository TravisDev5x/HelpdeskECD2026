@extends('admin.layout')

@section('title', '| Historial de asignaciones')

@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">HISTORICO DE ASIGNACIONES
                    <!-- <small>Listado</small> -->
                </h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Historial de asignaciones</li>
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
                    <h3 class="card-title">Listado de asignaciones</h3>
                </div>
            </div>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <table id="assignments-table" class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>No.</th>
                        <th>Equipo</th>
                        <th>Serie</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Movimiento</th>
                        <th>Asignador</th>
                        <th>Fecha movimiento</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
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
    {{-- moment --}}
    <script src="{{ asset('adminlte/plugins/moment/moment.min.js') }}"></script>
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

            $('#assignments-table').DataTable({
                "processing": true,
                "serverSide": true,
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
                ajax: '{{ route('get_logassignments') }}',
                columns: [
                  {
                        data: 'employee.name',
                        name: 'employee.name',
                        render: function(data, type, row) {
                            if (row.employee) {
                                var fullName = row.employee.name;
                                if (row.employee.ap_paterno) {
                                    fullName += ' ' + row.employee.ap_paterno;
                                }
                                if (row.employee.ap_materno) {
                                    fullName += ' ' + row.employee.ap_materno;
                                }
                                return fullName;
                            } else {
                                return 'SIN DATO';
                            }
                        }
                    },
                    {
                        data: 'employee.usuario',
                        name: 'employee.usuario',
                        render: function(data, type, row) {
                            if (row.employee) {
                                var fullUser = row.employee.usuario;
                                return fullUser;
                            } else {
                                return 'SIN DATO';
                            }
                        }
                    },
                    {
                        data: 'product.name',
                        name: 'product.name',
                        render: function(data, type, row) {
                            if (row.product) {
                                var fullProduct = row.product.name;
                                return fullProduct;
                            } else {
                                return 'SIN DATO';
                            }
                        }
                    },
                    {
                        data: 'product.serie',
                        name: 'product.serie',
                        render: function(data, type, row) {
                            if (row.product) {
                                var fullProduct = row.product.serie;
                                return fullProduct;
                            } else {
                                return 'SIN DATO';
                            }
                        }
                    },
                    {
                        data: 'product.marca',
                        name: 'product.marca',
                        render: function(data, type, row) {
                            if (row.product) {
                                var fullProduct = row.product.marca;
                                return fullProduct;
                            } else {
                                return 'SIN DATO';
                            }
                        }
                    },
                    {
                        data: 'product.modelo',
                        name: 'product.modelo',
                        render: function(data, type, row) {
                            if (row.product) {
                                var fullProduct = row.product.modelo;
                                return fullProduct;
                            } else {
                                return 'SIN DATO';
                            }
                        }
                    },
                    {
                        data: 'assignment',
                        name: 'assignment'
                    },
                    {
                        data: 'user.name',
                        name: 'user.name',
                        render: function(data, type, row) {
                            if (row.user) {
                                var fullName = row.user.name;
                                if (row.user.ap_paterno) {
                                    fullName += ' ' + row.user.ap_paterno;
                                }
                                if (row.user.ap_materno) {
                                    fullName += ' ' + row.user.ap_materno;
                                }
                                return fullName;
                            } else {
                                return 'SIN DATO';
                            }
                        }
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'observations',
                        name: 'observations',
                        render: function(data, type, row) {
                            if (row.observations) {
                                var fullProduct = row.observations;
                                return fullProduct;
                            } else {
                                return 'SIN OBSERVACIONES';
                            }
                        }
                    }
                ],
                rowCallback: function(nRow, aData, iDisplayIndex) {
                    if (aData.assignment == 'Asignación') {
                        $('td', nRow).eq(6).css({
                            background: "#218838"
                        });
                        $('td', nRow).eq(6).css({
                            color: "#ffffff"
                        });
                    } else if (aData.assignment == 'Desasignación') {
                        $('td', nRow).eq(6).css({
                            color: "#ffffff"
                        });
                        $('td', nRow).eq(6).css({
                            background: "#c82333"
                        });
                    }
                },
                order: [
                    [8, 'desc']
                ],
                columnDefs: [{
                    targets: 8,
                    render: function(data) {
                        return moment(data).format('YYYY-MM-DD h:mm:ss');
                    }
                }, ],
            });
        });
    </script>
@endpush
