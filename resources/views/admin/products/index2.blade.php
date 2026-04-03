@extends('admin.layout')

@section('title', '| Inventario')

@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">PRODUCTOS</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Inventario</li>
                </ol>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex bd-highlight">
                <div class="mr-auto bd-highlight">
                    <h3 class="card-title">Listado de productos</h3>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table id="products-table" class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>&nbsp;</th>
                        <th>Nombre</th>
                        <th>Serie</th>
                        <th>Etiqueta</th>
                        <th>Empresa</th>
                        <th>Status</th>
                        <th>Costo</th>
                        <th>Fecha de ingreso</th>
                        <th>Asignado</th>
                        <th>Modelo</th>
                        <th>Campaña</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script>
            $(function() {
                if ($(window).width() < 576) {
                    $('#products-table').removeClass('nowrap');
                } else {
                    $('#products-table').addClass('nowrap');
                }

                oTable = $('#products-table').DataTable({
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
                    ajax: '{{ route('get_products') }}',
                    columns: [{
                            data: 'id',
                            name: 'id'
                        },
                        {
                            data: null,
                            searchable: false,
                            'mRender': function(datos) {
                                var show = '';
                                var asig = '';
                                show += '@can('update product')<a href="products/' +
                                    datos.id +
                                    '/history" class="btn btn-xs btn-warning mr-1" title="Histórico"><i class="fas fa-list"></i></a>@endcan'

                                return show;
                            }
                        },
                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'serie',
                            name: 'serie'
                        },
                        {
                            data: 'etiqueta',
                            name: 'etiqueta'
                        },
                        {
                            data: 'company.name',
                            name: 'company.name'
                        },
                        {
                            data: 'status',
                            name: 'status',
                            render: function(data) {
                                const statusClassMap = {
                                    'OPERABLE': 'success',
                                    'OPERATIVO': 'success',
                                    'INOPERABLE': 'danger',
                                    'EN_REPARACION': 'warning',
                                    'STOCK': 'info',
                                    'ROBADO': 'dark',
                                    'RECICLADO': 'secondary',
                                    'NO_ENTREGADO': 'secondary',
                                    'ABSOLETO': 'secondary',
                                    'OBSOLETO': 'secondary'
                                };
                                const cls = statusClassMap[data] || 'secondary';
                                return '<span class="badge badge-' + cls + '">' + data + '</span>';
                            }
                        },
                        {
                            data: 'costo',
                            name: 'costo'
                        },
                        {
                            data: 'fecha_ingreso',
                            name: 'fecha_ingreso'
                        },
                        {
                            data: 'employee.name',
                            name: 'employee.name',
                            render: function(data, type, row) {
                                if (row.employee) {
                                    var fullName = row.employee.name || '';
                                    if (row.employee.ap_paterno) {
                                        fullName += ' ' + row.employee.ap_paterno;
                                    }
                                    if (row.employee.ap_materno) {
                                        fullName += ' ' + row.employee.ap_materno;
                                    }
                                    return fullName;
                                } else {
                                    return 'Sin asignar';
                                }
                            }
                        },
                        {
                            data: 'modelo',
                            name: 'modelo'
                        },
                        {
                            data: 'employee.campaign.name',
                            name: 'employee.campaign.name',
                            render: function(data) {
                                return data || 'Sin campaña';
                            },
                            defaultContent: 'Sin campaña'
                        }
                    ],
                    order: [
                        [0, 'asc']
                    ],
                    columnDefs: [{
                        orderable: false,
                        targets: [1]
                    }],
                });
            });
        </script>
    @endpush
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/style-datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/moment/moment.min.js') }}"></script>
@endpush
