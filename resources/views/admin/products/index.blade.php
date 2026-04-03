@extends('admin.layout')

@section('title', '| Inventario')

{{-- 1. Estilos: Usamos push para enviarlos al head del layout --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <style>
        .table-responsive { overflow-x: auto; }
        .badge { font-size: 90%; }
    </style>
@endpush

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
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">Listado de productos</h3>

            <div class="d-flex">
                {{-- Filtro de Empresa --}}
                <div class="mr-2">
                    <select id="empresaFilter" class="form-control form-control-sm">
                        <option value="">Todas las empresas</option>
                        @foreach ($empresas as $empresa)
                            <option value="{{ $empresa->name }}">{{ $empresa->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mr-2">
                    <a class="btn btn-warning btn-sm" href="{{ route('admin.contenido.ctg.productos.index') }}">
                        <i class="fa fa-list"></i> Catalogo
                    </a>
                </div>

                @unlessrole('Mantenimiento|Operaciones')
                <div class="mr-2">
                    <a class="btn btn-secondary btn-sm" href="{{ route('admin.components.index') }}">
                        <i class="fas fa-atom"></i> Componentes
                    </a>
                </div>
                <div class="mr-2">
                    <a class="btn btn-info btn-sm" href="{{ route('admin.maintenances.index') }}">
                        <i class="fa fa-eye"></i> Mantenimientos
                    </a>
                </div>
                @endunlessrole

                <div class="mr-2">
                    <a class="btn btn-primary btn-sm" href="{{ route('admin.products.create') }}">
                        <i class="fa fa-plus"></i> Nuevo
                    </a>
                </div>

                @can('descarga_productosall')
                <div>
                    <a class="btn btn-success btn-sm" href="{{ route('producto.downloadall') }}">
                        <i class="fa fa-download"></i> Descargar
                    </a>
                </div>
                @endcan
            </div>
        </div>
    </div>

    <div class="card-body">
        <table id="products-table" class="table table-bordered table-striped table-sm w-100">
            <thead>
                <tr>
                    <th>ID</th>
                    <th style="min-width: 100px;">Acciones</th>
                    <th>Nombre</th>
                    @unlessrole('Mantenimiento|Operaciones')
                        <th>Serie</th>
                    @endunlessrole
                    <th>Etiqueta</th>
                    <th>Empresa</th>
                    <th>Status</th>
                    <th>Costo</th>
                    <th>Fecha Ingreso</th>
                    <th>Asignado a</th>
                    <th>Modelo</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

{{-- 2. Scripts: Se cargan al final del body --}}
@push('scripts')
    <script src="{{ asset('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
    // Configuración segura pasada desde PHP
    const config = {
        routes: {
            ajaxIndex: '{{ route('get_products') }}',
            unassign: '{{ route('admin.products.unassign') }}',
            // Usamos rutas relativas para reemplazar ID después
            assignBase: '{{ url("admin/assignments") }}',
            maintBase: '{{ url("admin/maintenances") }}',
            prodBase: '{{ url("admin/products") }}'
        },
        permissions: {
            canUpdate: @json(auth()->user()->can('update product')),
            isLimited: @json(auth()->user()->hasRole(['Mantenimiento', 'Operaciones']))
        },
        token: '{{ csrf_token() }}'
    };

    $(document).ready(function () {
        
        var table = $('#products-table').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            responsive: true,
            ajax: config.routes.ajaxIndex,
            order: [[0, 'desc']],
            language: { url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json" },
            columns: [
                { data: 'id', name: 'id' },
                { 
                    data: null, 
                    name: 'actions', 
                    orderable: false, 
                    searchable: false,
                    render: function (data, type, row) {
                        return renderBtns(row);
                    }
                },
                { data: 'name', name: 'name' },
                @unlessrole('Mantenimiento|Operaciones')
                { data: 'serie', name: 'serie' },
                @endunlessrole
                { data: 'etiqueta', name: 'etiqueta' },
                { data: 'company.name', name: 'company.name' },
                { 
                    data: 'status', 
                    name: 'status',
                    render: function(d) {
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
                        let cls = statusClassMap[d] || 'secondary';
                        return `<span class="badge badge-${cls}">${d}</span>`;
                    }
                },
                { data: 'costo', name: 'costo' },
                { data: 'fecha_ingreso', name: 'fecha_ingreso' },
                { 
                    data: 'employee.name', 
                    name: 'employee.name',
                    render: function(d, t, r) {
                        if(!r.employee) return '<small class="text-muted">Sin asignar</small>';
                        const fullName = [r.employee.name, r.employee.ap_paterno, r.employee.ap_materno]
                            .filter(Boolean)
                            .join(' ');
                        return `<small>${fullName}</small>`;
                    }
                },
                { data: 'modelo', name: 'modelo' }
            ]
        });

        // Función para pintar botones HTML
        function renderBtns(row) {
            let html = '<div class="btn-group">';
            
            // Lógica Asignar/Desasignar
            const blockedStatuses = ['INOPERABLE', 'RECICLADO', 'NO_ENTREGADO', 'ABSOLETO', 'OBSOLETO'];
            if (!blockedStatuses.includes(row.status)) {
                if (!row.employee_id) {
                    html += `<a href="${config.routes.assignBase}/${row.id}/edit" class="btn btn-xs btn-info mr-1" title="Asignar"><i class="fas fa-user-plus"></i></a>`;
                } else {
                    html += `<button class="btn btn-xs btn-danger mr-1" onclick="desasignar(${row.id})" title="Desasignar"><i class="fas fa-user-minus"></i></button>`;
                }
            }

            // Lógica Editar/Mantenimiento
            if (!config.permissions.isLimited && config.permissions.canUpdate) {
                html += `<a href="${config.routes.maintBase}/${row.id}/edit" class="btn btn-xs btn-primary mr-1"><i class="fas fa-cogs"></i></a>`;
            }

            if (config.permissions.canUpdate) {
                html += `<a href="${config.routes.prodBase}/${row.id}/edit" class="btn btn-xs btn-info mr-1"><i class="fas fa-pencil-alt"></i></a>`;
                html += `<a href="${config.routes.prodBase}/${row.id}/history" class="btn btn-xs btn-warning"><i class="fas fa-list"></i></a>`;
            }

            html += '</div>';
            return html;
        }

        // Filtro de Empresa
        $('#empresaFilter').on('change', function () {
            table.column('company.name:name').search(this.value || '').draw();
        });

        // Función Global Desasignar
        window.desasignar = function(id) {
            if(!confirm('¿Quitar asignación? El equipo pasará a OPERATIVO.')) return;

            $.ajax({
                url: config.routes.unassign,
                type: 'POST',
                data: { _token: config.token, id: id },
                success: function(res) {
                    alert('Equipo liberado.');
                    table.ajax.reload(null, false);
                },
                error: function(err) {
                    console.error(err);
                    alert('Error al desasignar.');
                }
            });
        };
    });
    </script>
@endpush