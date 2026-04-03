@php
    /**
     * LÓGICA DE VISIBILIDAD Y RUTAS ACTIVAS
     * Centralizamos las validaciones complejas para mantener el HTML limpio.
     */
    $user = auth()->user();

    // -- Validaciones para el bloque CATÁLOGOS --
    $isCatalogActive = request()->routeIs([
        'admin.companies*', 'admin.sedes*', 'admin.ubicaciones*', 'admin.departments*',
        'admin.positions*', 'admin.failures*', 'admin.campaigns*', 'did*',
        'admin.contenido.ctg.*'
    ]);

    $canAccessCatalogs = $user && (
        $user->hasRole('Admin') ||
        $user->can('read incidents') || $user->can('read companies') ||
        $user->can('modulo.sedes') || $user->can('modulo.ubicaciones') ||
        $user->can('read departments') || $user->can('read positions') ||
        $user->can('read failures') || $user->can('read campaigns') ||
        $user->can('modulo.did')
    );

    // -- Validaciones para el bloque CONFIGURACIÓN --
    $isConfigActive = request()->routeIs([
        'admin.companies*', 'admin.sedes*', 'admin.ubicaciones*', 'admin.departments*',
        'admin.positions*', 'admin.failures*', 'admin.campaigns*', 'did*',
        'admin.assets*', 'admin.tests*', 'admin.nivel', 'admin.permissions*',
        'admin.roles*', 'admin.bitacora*', 'admin.bitacoraHost*', 'admin.agenda*'
    ]);

    $canAccessConfig = $user && (
        $user->hasRole('Admin') ||
        $user->can('read companies') || $user->can('modulo.sedes') ||
        $user->can('modulo.ubicaciones') || $user->can('read departments') ||
        $user->can('read positions') || $user->can('read failures') ||
        $user->can('read campaigns') || $user->can('modulo.did') ||
        $user->can('read assets') || $user->can('read tests') ||
        $user->can('read roles') || $user->can('read bitacoras') ||
        $user->can('read bitacorasHost')
    );

    // Inventario V2: árbol si hay lectura operativa, «mis asignaciones», o gestión de catálogos/config
    $showInventoryV2Nav = $user && (
        $user->can('read inventory')
        || $user->can('read inventory own assignments')
        || $user->can('read inventory assignment history')
        || $user->can('manage inventory config')
        || $user->can('manage inventory labels')
        || $user->can('manage inventory maintenance catalogs')
    );

    // Reportes: incluye monitoreo inventario V2 (misma sección de menú)
    $isReportsTreeActive = request()->is('admin/reports*')
        || request()->routeIs('inventory.v2.monitor', 'inventory.monitor.*');

    // Inventario V2 (menú): rutas que abren/resaltan el árbol (monitoreo: inventory.v2.monitor bajo Reportes)
    $inventoryV2TreeRoutes = [
        'inventory.v2.index',
        'inventory.v2.my-assignments',
        'inventory.v2.maintenance',
        'inventory.v2.pending',
        'inventory.v2.assignments',
        'inventory.v2.assignments.summary',
        'inventory.v2.assignment-history',
        'inventory.v2.assignment-history.export',
        'inventory.components',
        'inventory.config.*',
        'inventory.pdf.*',
        'inventory.export',
    ];
@endphp

<nav class="mt-2" role="navigation" aria-label="Menú principal de HelpDesk">
    <ul class="nav nav-pills nav-sidebar nav-child-indent flex-column" data-widget="treeview" role="menu" data-accordion="false">

        {{-- ========================================== --}}
        {{-- BLOQUE: OPERACIÓN DIARIA                   --}}
        {{-- ========================================== --}}
        <li class="nav-header text-muted" style="font-size: 0.8rem;">OPERACIÓN</li>
        
        @can('read services')
            <li class="nav-item">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-tachometer-alt text-secondary"></i>
                    <p>Inicio</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.services.index') }}" class="nav-link {{ request()->routeIs('admin.services.index') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-check-double text-secondary"></i>
                    <p>Servicios Finalizados</p>
                </a>
            </li>
        @endcan

        {{-- REPORTES --}}
        @can('read reports')
            <li class="nav-item has-treeview {{ $isReportsTreeActive ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ $isReportsTreeActive ? 'active' : '' }}">
                    <i class="nav-icon fas fa-chart-line text-secondary"></i>
                    <p>Reportes <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                    @can('read reports ticket')
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.index') ? 'active' : '' }}">
                                <i class="fas fa-ticket-alt nav-icon text-secondary"></i>
                                <p>Tickets</p>
                            </a>
                        </li>
                    @endcan
                    @can('read reports inventory')
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.inventory') }}" class="nav-link {{ request()->routeIs('admin.reports.inventory') ? 'active' : '' }}">
                                <i class="fas fa-boxes nav-icon text-secondary"></i>
                                <p>Detalle Inventario</p>
                            </a>
                        </li>
                    @endcan
                    @can('read inventory monitor')
                        <li class="nav-item">
                            <a href="{{ route('inventory.v2.monitor') }}" class="nav-link {{ request()->routeIs('inventory.v2.monitor', 'inventory.monitor.*') ? 'active' : '' }}">
                                <i class="fas fa-chart-line nav-icon text-info"></i>
                                <p>Monitoreo inventario V2</p>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan

        {{-- NUEVO TICKET --}}
        @can('create service')
            <li class="nav-item">
                <a href="{{ route('admin.services.create') }}" class="nav-link {{ request()->routeIs('admin.services.create') ? 'active' : '' }}">
                    <i class="fas fa-plus-circle nav-icon text-secondary"></i>
                    <p>Nuevo Ticket</p>
                </a>
            </li>
        @endcan

        {{-- ========================================== --}}
        {{-- BLOQUE: INVENTARIO                         --}}
        {{-- ========================================== --}}
        <li class="nav-header text-muted" style="font-size: 0.8rem;">INVENTARIO</li>
        
        {{-- INVENTARIO V1 (ANTIGUO) --}}
        @hasanyrole('Admin|Soporte|Infraestructura|Mantenimiento|Auditor')
            <li class="nav-item">
                <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.index') ? 'active' : '' }}">
                    <i class="fas fa-archive nav-icon text-secondary"></i>
                    <p>Inventario V1 (Antiguo)</p>
                </a>
            </li>
        @endhasanyrole

        {{-- INVENTARIO V2: operación (read inventory) + catálogos/config bajo el mismo árbol --}}
        @if($showInventoryV2Nav)
            <li class="nav-item has-treeview {{ request()->routeIs($inventoryV2TreeRoutes) ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->routeIs($inventoryV2TreeRoutes) ? 'active' : '' }}">
                    <i class="nav-icon fas fa-box-open text-primary"></i>
                    <p>Inventario V2 <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                    @can('read inventory own assignments')
                        @cannot('read inventory')
                            <li class="nav-item">
                                <a href="{{ route('inventory.v2.my-assignments') }}" class="nav-link {{ request()->routeIs('inventory.v2.my-assignments') ? 'active' : '' }}">
                                    <i class="fas fa-user-check nav-icon text-success"></i>
                                    <p>Mis equipos asignados</p>
                                </a>
                            </li>
                        @endcannot
                    @endcan
                    @can('read inventory assignment history')
                        @cannot('read inventory')
                            <li class="nav-item">
                                <a href="{{ route('inventory.v2.assignment-history') }}" class="nav-link {{ request()->routeIs('inventory.v2.assignment-history', 'inventory.v2.assignment-history.export') ? 'active' : '' }}">
                                    <i class="fas fa-history nav-icon text-secondary"></i>
                                    <p>Historial asignaciones</p>
                                </a>
                            </li>
                        @endcannot
                    @endcan
                    @can('read inventory')
                        <li class="nav-item">
                            <a href="{{ route('inventory.v2.index') }}" class="nav-link {{ request()->routeIs('inventory.v2.index') ? 'active' : '' }}">
                                <i class="fas fa-list-ul nav-icon text-secondary"></i>
                                <p>Listado de Activos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('inventory.v2.maintenance') }}" class="nav-link {{ request()->routeIs('inventory.v2.maintenance') ? 'active' : '' }}">
                                <i class="fas fa-wrench nav-icon text-primary"></i>
                                <p>Mantenimientos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('inventory.v2.pending') }}" class="nav-link {{ request()->routeIs('inventory.v2.pending') ? 'active' : '' }}">
                                <i class="fas fa-clipboard-list nav-icon text-warning"></i>
                                <p>Pendientes</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('inventory.v2.assignments.summary') }}" class="nav-link {{ request()->routeIs('inventory.v2.assignments.summary') ? 'active' : '' }}">
                                <i class="fas fa-users nav-icon text-success"></i>
                                <p>Resumen asignaciones</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('inventory.v2.assignments') }}" class="nav-link {{ request()->routeIs('inventory.v2.assignments') ? 'active' : '' }}">
                                <i class="fas fa-list-ul nav-icon text-info"></i>
                                <p>Asignaciones por activo</p>
                            </a>
                        </li>
                        @can('read inventory assignment history')
                        <li class="nav-item">
                            <a href="{{ route('inventory.v2.assignment-history') }}" class="nav-link {{ request()->routeIs('inventory.v2.assignment-history', 'inventory.v2.assignment-history.export') ? 'active' : '' }}">
                                <i class="fas fa-history nav-icon text-secondary"></i>
                                <p>Historial asignaciones</p>
                            </a>
                        </li>
                        @endcan
                        <li class="nav-item">
                            <a href="{{ route('inventory.components') }}" class="nav-link {{ request()->routeIs('inventory.components') ? 'active' : '' }}">
                                <i class="fas fa-microchip nav-icon text-warning"></i>
                                <p>Componentes</p>
                            </a>
                        </li>
                    @endcan
                    @can('manage inventory config')
                        <li class="nav-item">
                            <a href="{{ route('inventory.config.categories') }}" class="nav-link {{ request()->routeIs('inventory.config.categories') ? 'active' : '' }}">
                                <i class="fas fa-folder nav-icon text-secondary"></i>
                                <p>Categorías</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('inventory.config.status') }}" class="nav-link {{ request()->routeIs('inventory.config.status') ? 'active' : '' }}">
                                <i class="fas fa-tags nav-icon text-secondary"></i>
                                <p>Estatus</p>
                            </a>
                        </li>
                    @endcan
                    @canany(['manage inventory config', 'manage inventory labels'])
                        <li class="nav-item">
                            <a href="{{ route('inventory.config.labels') }}" class="nav-link {{ request()->routeIs('inventory.config.labels') ? 'active' : '' }}">
                                <i class="fas fa-tag nav-icon text-secondary"></i>
                                <p>Etiquetas por sede</p>
                            </a>
                        </li>
                    @endcanany
                    @can('manage inventory maintenance catalogs')
                        <li class="nav-item">
                            <a href="{{ route('inventory.config.maintenance-catalogs') }}" class="nav-link {{ request()->routeIs('inventory.config.maintenance-catalogs') ? 'active' : '' }}">
                                <i class="fas fa-wrench nav-icon text-info"></i>
                                <p>Catálogos de mantenimiento</p>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endif

        {{-- ASIGNACIONES --}}
        @can('read assignmentsIndividual')
            <li class="nav-item has-treeview {{ request()->is('admin/assignments*', 'admin/revision*') ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->is('admin/assignments*', 'admin/revision*') ? 'active' : '' }}">
                    <i class="fas fa-people-carry nav-icon text-secondary"></i>
                    <p>Asignaciones <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('admin.assignments.list') }}" class="nav-link {{ request()->routeIs('admin.assignments.list') ? 'active' : '' }}">
                            <i class="fas fa-list-ul nav-icon text-secondary"></i>
                            <p>Lista de asignación</p>
                        </a>
                    </li>

                    @role('Auditor')
                        <li class="nav-item">
                            <a href="{{ route('admin.revision.index') }}" class="nav-link {{ request()->routeIs('admin.revision.index') ? 'active' : '' }}">
                                <i class="fas fa-clipboard-check nav-icon text-secondary"></i>
                                <p>Revisión</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.revision.product') }}" class="nav-link {{ request()->routeIs('admin.revision.product') ? 'active' : '' }}">
                                <i class="fas fa-box-open nav-icon text-secondary"></i>
                                <p>Revisión por producto</p>
                            </a>
                        </li>
                    @endrole

                    @can('read assignments')
                        @unlessrole('Mantenimiento')
                            <li class="nav-item">
                                <a href="{{ route('admin.assignments.list2') }}" class="nav-link {{ request()->routeIs('admin.assignments.list2') ? 'active' : '' }}">
                                    <i class="fas fa-filter nav-icon text-secondary"></i>
                                    <p>Filtrar por producto</p>
                                </a>
                            </li>
                        @endunlessrole

                        <li class="nav-item">
                            <a href="{{ route('admin.assignments.index') }}" class="nav-link {{ request()->routeIs('admin.assignments.index') ? 'active' : '' }}">
                                <i class="fas fa-user-plus nav-icon text-secondary"></i>
                                <p>Asignación masiva</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.assignments.remove') }}" class="nav-link {{ request()->routeIs('admin.assignments.remove') ? 'active' : '' }}">
                                <i class="fas fa-user-minus nav-icon text-secondary"></i>
                                <p>Desasignación masiva</p>
                            </a>
                        </li>

                        @unlessrole('Mantenimiento')
                            <li class="nav-item">
                                <a href="{{ route('admin.assignments.log') }}" class="nav-link {{ request()->routeIs('admin.assignments.log') ? 'active' : '' }}">
                                    <i class="fas fa-history nav-icon text-secondary"></i>
                                    <p>Histórico</p>
                                </a>
                            </li>
                        @endunlessrole
                    @endcan
                </ul>
            </li>
        @endcan

        {{-- AREAS --}}
        @can('read areas')
            <li class="nav-item">
                <a href="{{ route('admin.areas.index') }}" class="nav-link {{ request()->routeIs('admin.areas*') ? 'active' : '' }}">
                    <i class="fas fa-map-marked-alt nav-icon text-secondary"></i>
                    <p>Areas</p>
                </a>
            </li>
        @endcan

        {{-- INCIDENCIAS --}}
        @can('read incidents')
            <li class="nav-item has-treeview {{ request()->is('admin/incidents*', 'admin/contenido/ctg*', 'admin/ciberseguridad*') ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->is('admin/incidents*', 'admin/contenido/ctg*', 'admin/ciberseguridad*') ? 'active' : '' }}">
                    <i class="fas fa-exclamation-triangle nav-icon text-secondary"></i>
                    <p>Incidencias <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('admin.incidents.index') }}" class="nav-link {{ request()->routeIs('admin.incidents.index') ? 'active' : '' }}">
                            <i class="fa fa-list-alt nav-icon text-secondary"></i>
                            <p>Ver incidencias</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('ciberseguridad.incidencias') }}" class="nav-link {{ request()->routeIs('ciberseguridad.incidencias') ? 'active' : '' }}">
                            <i class="fa fa-shield-virus nav-icon text-secondary"></i>
                            <p>Ciberseguridad</p>
                        </a>
                    </li>
                </ul>
            </li>
        @endcan

        {{-- USUARIOS --}}
        @can('read users')
            <li class="nav-item has-treeview {{ request()->is('admin/users*') ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                    <i class="fas fa-users nav-icon text-secondary"></i>
                    <p>Usuarios <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.index') && ! request()->boolean('trashed') && ! request()->boolean('pending') ? 'active' : '' }}">
                            <i class="fa fa-list nav-icon text-secondary"></i>
                            <p>Ver todos los usuarios</p>
                        </a>
                    </li>

                    @can('check activos')
                        @isset($countChecks)
                            <li class="nav-item">
                                <a href="{{ route('admin.users.index', ['pending' => 1]) }}" class="nav-link {{ request()->routeIs('admin.users.index') && request()->boolean('pending') ? 'active' : '' }}">
                                    <button class="btn-sm border-circle bg-secondary w-100 text-left">
                                        <i class="fa fa-bell mr-1"></i> Pendientes: {{ $countChecks }}
                                    </button>
                                </a>
                            </li>
                        @endisset
                    @endcan
                </ul>
            </li>
        @endcan

        {{-- DIRECTORIO --}}
        @can('read directory')
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-address-book text-secondary"></i>
                    <p>Directorio</p>
                </a>
            </li>
        @endcan

        {{-- ========================================== --}}
        {{-- BLOQUE: CATÁLOGOS                          --}}
        {{-- ========================================== --}}
        @if($canAccessCatalogs)
            <li class="nav-header text-muted" style="font-size: 0.8rem;">CATÁLOGOS</li>
            <li class="nav-item has-treeview {{ $isCatalogActive ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ $isCatalogActive ? 'active' : '' }}">
                    <i class="nav-icon fas fa-book text-secondary"></i>
                    <p>Catálogos <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                    @can('read companies')
                        <li class="nav-item">
                            <a href="{{ route('admin.companies.index') }}" class="nav-link {{ request()->routeIs('admin.companies*') ? 'active' : '' }}">
                                <i class="fas fa-building nav-icon text-secondary"></i><p>Empresas</p>
                            </a>
                        </li>
                    @endcan
                    @can('modulo.sedes')
                        <li class="nav-item">
                            <a href="{{ route('admin.sedes.index') }}" class="nav-link {{ request()->routeIs('admin.sedes*') ? 'active' : '' }}">
                                <i class="fa fa-city nav-icon text-secondary"></i><p>Sedes</p>
                            </a>
                        </li>
                    @endcan
                    @can('modulo.ubicaciones')
                        <li class="nav-item">
                            <a href="{{ route('admin.ubicaciones.index') }}" class="nav-link {{ request()->routeIs('admin.ubicaciones*') ? 'active' : '' }}">
                                <i class="fa fa-map-pin nav-icon text-secondary"></i><p>Ubicaciones</p>
                            </a>
                        </li>
                    @endcan
                    @can('read departments')
                        <li class="nav-item">
                            <a href="{{ route('admin.departments.index') }}" class="nav-link {{ request()->routeIs('admin.departments*') ? 'active' : '' }}">
                                <i class="fas fa-sitemap nav-icon text-secondary"></i><p>Departamentos</p>
                            </a>
                        </li>
                    @endcan
                    @can('read positions')
                        <li class="nav-item">
                            <a href="{{ route('admin.positions.index') }}" class="nav-link {{ request()->routeIs('admin.positions*') ? 'active' : '' }}">
                                <i class="fas fa-id-badge nav-icon text-secondary"></i><p>Puestos</p>
                            </a>
                        </li>
                    @endcan
                    <li class="nav-item">
                        <a href="{{ route('admin.contenido.ctg.productos.index') }}" class="nav-link {{ request()->routeIs('admin.contenido.ctg.productos.index') ? 'active' : '' }}">
                            <i class="fas fa-boxes nav-icon text-secondary"></i><p>Catálogo Productos / Componentes</p>
                        </a>
                    </li>

                    @can('read incidents')
                        <li class="nav-item">
                            <a href="{{ route('admin.contenido.ctg.incidencia.index') }}" class="nav-link {{ request()->routeIs('admin.contenido.ctg.incidencia.index') ? 'active' : '' }}">
                                <i class="fa fa-list nav-icon text-secondary"></i><p>Catálogo Incidencias</p>
                            </a>
                        </li>
                    @endcan
                    @can('read campaigns')
                        <li class="nav-item">
                            <a href="{{ route('admin.campaigns.index') }}" class="nav-link {{ request()->routeIs('admin.campaigns*') ? 'active' : '' }}">
                                <i class="fas fa-headset nav-icon text-secondary"></i><p>Campañas</p>
                            </a>
                        </li>
                    @endcan
                    @can('modulo.did')
                        <li class="nav-item">
                            <a href="{{ route('did') }}" class="nav-link {{ request()->routeIs('did*') ? 'active' : '' }}">
                                <i class="fas fa-phone-square-alt nav-icon text-secondary"></i><p>DID</p>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endif

        {{-- ========================================== --}}
        {{-- BLOQUE: CONFIGURACIÓN                      --}}
        {{-- ========================================== --}}
        @if($canAccessConfig)
            <li class="nav-header text-muted" style="font-size: 0.8rem;">CONFIGURACIÓN</li>
            <li class="nav-item has-treeview {{ $isConfigActive ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ $isConfigActive ? 'active' : '' }}">
                    <i class="nav-icon fas fa-cog text-secondary"></i>
                    <p>Configuración <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                    @can('read assets')
                        <li class="nav-item">
                            <a href="{{ route('admin.assets.index') }}" class="nav-link {{ request()->routeIs('admin.assets*') ? 'active' : '' }}">
                                <i class="fas fa-network-wired nav-icon text-secondary"></i><p>Activos para pruebas</p>
                            </a>
                        </li>
                    @endcan
                    @can('read tests')
                        <li class="nav-item">
                            <a href="{{ route('admin.tests.index') }}" class="nav-link {{ request()->routeIs('admin.tests.index') ? 'active' : '' }}">
                                <i class="fas fa-vial nav-icon text-secondary"></i><p>Pruebas Nivel 1</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.nivel') }}" class="nav-link {{ request()->routeIs('admin.nivel') ? 'active' : '' }}">
                                <i class="fa fa-circle nav-icon text-secondary"></i><p>Pruebas Nivel 2</p>
                            </a>
                        </li>
                    @endcan
                    @role('Admin')
                        <li class="nav-item">
                            <a href="{{ route('admin.permissions.index') }}" class="nav-link {{ request()->routeIs('admin.permissions*') ? 'active' : '' }}">
                                <i class="fas fa-key nav-icon text-secondary"></i><p>Permisos</p>
                            </a>
                        </li>
                    @endrole
                    @can('read roles')
                        <li class="nav-item has-treeview {{ request()->is('admin/roles*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->is('admin/roles*') ? 'active' : '' }}">
                                <i class="fas fa-user-tag nav-icon text-secondary"></i><p>Roles <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.index') ? 'active' : '' }}">
                                        <i class="fa fa-list nav-icon text-secondary"></i><p>Ver todos los roles</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.roles.create') }}" class="nav-link {{ request()->routeIs('admin.roles.create') ? 'active' : '' }}">
                                        <i class="fas fa-plus-circle nav-icon text-secondary"></i><p>Crear un rol</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endcan
                    @can('read bitacoras')
                        <li class="nav-item">
                            <a href="{{ route('admin.bitacora.index') }}" class="nav-link {{ request()->routeIs('admin.bitacora.index') ? 'active' : '' }}">
                                <i class="fa fa-book nav-icon text-secondary"></i><p>Bitácora</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.bitacora.create') }}" class="nav-link {{ request()->routeIs('admin.bitacora.create') ? 'active' : '' }}">
                                <i class="fas fa-pencil-alt nav-icon text-secondary"></i><p>Crear Bitácora</p>
                            </a>
                        </li>
                    @endcan
                    @can('read bitacorasHost')
                        <li class="nav-item">
                            <a href="{{ route('admin.bitacoraHost.index') }}" class="nav-link {{ request()->routeIs('admin.bitacoraHost.index') ? 'active' : '' }}">
                                <i class="fa fa-server nav-icon text-secondary"></i><p>Bitácora Hosts</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.bitacoraHost.create') }}" class="nav-link {{ request()->routeIs('admin.bitacoraHost.create') ? 'active' : '' }}">
                                <i class="fas fa-pencil-alt nav-icon text-secondary"></i><p>Crear Bitácora Host</p>
                            </a>
                        </li>
                    @endcan
                    @role('Admin')
                        @can('read calendar')
                            <li class="nav-item">
                                <a href="{{ route('admin.agenda.index') }}" class="nav-link {{ request()->routeIs('admin.agenda.index') ? 'active' : '' }}">
                                    <i class="fas fa-calendar-alt nav-icon text-secondary"></i><p>Agenda</p>
                                </a>
                            </li>
                        @endcan
                    @endrole
                </ul>
            </li>
        @endif

        {{-- ========================================== --}}
        {{-- BLOQUE: SEGURIDAD Y AUDITORÍA              --}}
        {{-- ========================================== --}}
        @hasanyrole('Admin|Soporte')
            <li class="nav-header text-muted" style="font-size: 0.8rem;">SEGURIDAD</li>
            <li class="nav-item has-treeview {{ request()->routeIs('admin.audits*', 'admin.sessions*') ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->routeIs('admin.audits*', 'admin.sessions*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-shield-alt text-secondary"></i>
                    <p>Seguridad y Logs <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('admin.audits.index') }}" class="nav-link {{ request()->routeIs('admin.audits.index') ? 'active' : '' }}">
                            <i class="fas fa-history nav-icon text-secondary"></i>
                            <p>Trazabilidad / Logs</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.sessions.active') }}" class="nav-link {{ request()->routeIs('admin.sessions.active') ? 'active' : '' }}">
                            <i class="fas fa-satellite-dish nav-icon text-secondary"></i>
                            <p>Monitor de Sesiones</p>
                        </a>
                    </li>
                </ul>
            </li>
        @endhasanyrole

    </ul>
</nav>