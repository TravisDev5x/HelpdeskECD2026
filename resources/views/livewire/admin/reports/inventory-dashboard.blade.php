<div class="inventory-report-dashboard">
    <div class="card card-outline card-primary mb-3">
        <div class="card-header">
            <h3 class="card-title">Filtros</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label for="filter-name">Nombre (producto o usuario)</label>
                    <input
                        id="filter-name"
                        type="text"
                        class="form-control form-control-sm"
                        placeholder="Buscar por nombre..."
                        wire:model="filterName"
                    >
                </div>
                <div class="col-md-4">
                    <label for="filter-brand">Marca</label>
                    <input
                        id="filter-brand"
                        type="text"
                        class="form-control form-control-sm"
                        placeholder="Buscar por marca..."
                        wire:model="filterBrand"
                    >
                </div>
                <div class="col-md-4">
                    <label for="filter-status">Estatus (tabla por estatus)</label>
                    <select id="filter-status" class="form-control form-control-sm" wire:model="filterStatus">
                        <option value="">Todos</option>
                        <option value="OPERABLE">OPERABLE</option>
                        <option value="INOPERABLE">INOPERABLE</option>
                        <option value="CONSUMIBLE">CONSUMIBLE</option>
                        <option value="STOCK">STOCK</option>
                        <option value="ROBADO">ROBADO</option>
                        <option value="RECICLADO">RECICLADO</option>
                        <option value="EN_REPARACION">EN REPARACION</option>
                    </select>
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-end">
                <button type="button" class="btn btn-primary btn-sm mr-2" wire:click="applyFilters">
                    <i class="fas fa-filter mr-1"></i> Aplicar filtros
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="clearFilters">
                    <i class="fas fa-eraser mr-1"></i> Limpiar filtros
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">INVENTARIO POR NOMBRE</h3>
                    <div class="card-tools">
                        <span class="badge badge-secondary inventory-reg-badge">{{ $products->count() }} registros</span>
                    </div>
                </div>
                <div class="card-body inventory-dt-card-body">
                    <div wire:ignore class="inventory-dt-scroll">
                        <table id="table-1" class="table table-bordered table-striped table-hover table-sm w-100">
                            <thead>
                                <tr>
                                    <th>NOMBRE</th>
                                    <th style="width: 40px">CANTIDAD</th>
                                    <th style="width: 40px">PORCENTAJE</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td class="text-center">{{ $product->cantidad }}</td>
                                        <td class="text-center">
                                            {{ number_format(($product->cantidad / max($products->sum('cantidad'), 1)) * 100, 2) }}%
                                        </td>
                                    </tr>
                                @endforeach
                                @if ($products->isEmpty())
                                    <tr><td colspan="3" class="text-center text-muted">Sin resultados con los filtros actuales.</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <div class="d-flex bd-highlight">
                        <div class="mr-auto bd-highlight">
                            <h3 class="card-title">INVENTARIO POR NOMBRE Y ESTATUS</h3>
                        </div>
                        <div class="bd-highlight pr-2">
                            <span class="badge badge-secondary inventory-reg-badge">{{ $productsStatus->count() }} registros</span>
                        </div>

                        {{-- Misma regla que la página y las rutas report.download.* (read reports ticket|read reports inventory) --}}
                        @can('read reports inventory')
                            <div class="bd-highlight pr-2">
                                <a class="btn btn-secondary btn-sm" href="{{ route('report.download.sistemas') }}">
                                    <i class="fa fa-download"></i> SISTEMAS
                                </a>
                            </div>
                            <div class="bd-highlight pr-2">
                                <a class="btn btn-secondary btn-sm" href="{{ route('report.download.mantenimiento') }}">
                                    <i class="fa fa-download"></i> MANTENIMIENTO
                                </a>
                            </div>
                        @endcan
                    </div>
                </div>
                <div class="card-body inventory-dt-card-body">
                    <div wire:ignore class="inventory-dt-scroll">
                        <table id="table-2" class="table table-bordered table-striped table-hover table-sm w-100">
                            <thead>
                                <tr>
                                    <th>NOMBRE</th>
                                    <th style="width: 40px">OPERABLE</th>
                                    <th style="width: 40px">INOPERABLE</th>
                                    <th style="width: 40px">CONSUMIBLE</th>
                                    <th style="width: 40px">STOCK</th>
                                    <th style="width: 40px">ROBADO</th>
                                    <th style="width: 40px">RECICLADO</th>
                                    <th style="width: 40px">EN REPARACION</th>
                                    <th style="width: 40px">TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($productsStatus as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td class="text-center">{{ $product->OPERABLE }}</td>
                                        <td class="text-center">{{ $product->INOPERABLE }}</td>
                                        <td class="text-center">{{ $product->CONSUMIBLE }}</td>
                                        <td class="text-center">{{ $product->STOCK }}</td>
                                        <td class="text-center">{{ $product->ROBADO }}</td>
                                        <td class="text-center">{{ $product->RECICLADO }}</td>
                                        <td class="text-center">{{ $product->EN_REPARACION }}</td>
                                        <td class="text-center">{{ $product->cantidad }}</td>
                                    </tr>
                                @endforeach
                                @if ($productsStatus->isEmpty())
                                    <tr><td colspan="9" class="text-center text-muted">Sin resultados con los filtros actuales.</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title">INVENTARIO POR NOMBRE Y MARCA</h3>
                    <div class="card-tools">
                        <span class="badge badge-secondary inventory-reg-badge">{{ $productsMarca->count() }} registros</span>
                    </div>
                </div>
                <div class="card-body inventory-dt-card-body">
                    <div wire:ignore class="inventory-dt-scroll">
                        <table id="table-3" class="table table-bordered table-striped table-hover table-sm w-100">
                            <thead>
                                <tr>
                                    <th>NOMBRE</th>
                                    <th>MARCA</th>
                                    <th style="width: 40px">CANTIDAD</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($productsMarca as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->marca }}</td>
                                        <td class="text-center">{{ $product->cantidad }}</td>
                                    </tr>
                                @endforeach
                                @if ($productsMarca->isEmpty())
                                    <tr><td colspan="3" class="text-center text-muted">Sin resultados con los filtros actuales.</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">ASIGNACION DE EQUIPOS</h3>
                    <div class="card-tools">
                        <span class="badge badge-secondary inventory-reg-badge">{{ $users->count() }} registros</span>
                    </div>
                </div>
                <div class="card-body inventory-dt-card-body">
                    <div wire:ignore class="inventory-dt-scroll">
                        <table id="table-4" class="table table-bordered table-striped table-hover table-sm w-100">
                            <thead>
                                <tr>
                                    <th>NOMBRE</th>
                                    <th style="width: 40px">CANTIDAD</th>
                                    <th>VER</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $user->name . ' ' . $user->ap_paterno . ' ' . $user->ap_materno }}</td>
                                        <td class="text-center">{{ $user->cantidad }}</td>
                                        <td>
                                            <a href="../assignments/{{ $user->id }}" class="btn btn-xs btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                @if ($users->isEmpty())
                                    <tr><td colspan="3" class="text-center text-muted">Sin resultados con los filtros actuales.</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/style-datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <style>
        /* Evitar recorte: DataTables + table-responsive + sticky dentro de cards suele cortar filas */
        .inventory-report-dashboard .card.card-outline {
            overflow: visible !important;
        }

        .inventory-report-dashboard .inventory-dt-card-body {
            overflow: visible !important;
        }

        .inventory-report-dashboard .inventory-dt-scroll {
            overflow-x: auto;
            overflow-y: visible;
            max-width: 100%;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 0.35rem;
        }

        .inventory-report-dashboard .dataTables_wrapper .row {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .inventory-report-dashboard .dataTables_wrapper .col-sm-12 {
            padding-left: 0;
            padding-right: 0;
        }

        #table-1 thead th,
        #table-2 thead th,
        #table-3 thead th,
        #table-4 thead th {
            background: #f8f9fa;
            white-space: nowrap;
        }

        body.dark-mode #table-1 thead th,
        body.dark-mode #table-2 thead th,
        body.dark-mode #table-3 thead th,
        body.dark-mode #table-4 thead th {
            background: #343a40 !important;
            color: #e9ecef !important;
            border-color: #495057 !important;
        }

        body:not(.dark-mode) .inventory-report-dashboard .inventory-reg-badge.badge-secondary {
            background-color: #e9ecef;
            color: #343a40;
        }

        .inventory-report-dashboard table.dataTable tbody td {
            vertical-align: middle !important;
        }

        .dataTables_wrapper .dataTables_filter input {
            min-width: 220px;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script>
        (function () {
            function destroyInventoryTables() {
                ['#table-1', '#table-2', '#table-3', '#table-4'].forEach(function (selector) {
                    if ($.fn.DataTable.isDataTable(selector)) {
                        $(selector).DataTable().destroy();
                    }
                });
            }

            function initInventoryTables() {
                ['#table-1', '#table-2', '#table-3', '#table-4'].forEach(function (selector) {
                    var $table = $(selector);
                    if (!$table.length || $.fn.DataTable.isDataTable(selector)) {
                        return;
                    }

                    $table.DataTable({
                        processing: false,
                        paging: true,
                        lengthChange: true,
                        searching: true,
                        ordering: true,
                        info: true,
                        pageLength: 10,
                        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                        autoWidth: false,
                        responsive: false,
                        stateSave: true,
                        deferRender: true,
                        order: [[0, 'asc']],
                        language: {
                            url: '../js/spanish.json',
                            search: 'Filtro rápido:',
                            searchPlaceholder: 'Escribe para filtrar...'
                        }
                    });
                });
            }

            document.addEventListener('DOMContentLoaded', initInventoryTables);
            document.addEventListener('livewire:navigated', initInventoryTables);
            document.addEventListener('livewire:init', initInventoryTables);

            window.addEventListener('inventory-table-refresh', function () {
                destroyInventoryTables();
                window.requestAnimationFrame(initInventoryTables);
            });
        })();
    </script>
@endpush

