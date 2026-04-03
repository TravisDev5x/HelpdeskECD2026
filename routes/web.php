<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AreasController;
use App\Http\Controllers\Admin\AssetsController;
use App\Http\Controllers\Admin\AssignmentsController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\BitacoraController;
use App\Http\Controllers\Admin\BitacoraHostController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\CampaignsController;
use App\Http\Controllers\Admin\CompaniesController;
use App\Http\Controllers\Admin\ComponentsController;
use App\Http\Controllers\Admin\ContenidoCtgsController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DidController;
use App\Http\Controllers\Admin\FailuresController;
use App\Http\Controllers\Admin\IncidentsController;
use App\Http\Controllers\Admin\InventoryAssignmentHistoryExportController;
use App\Http\Controllers\Admin\InventoryExportController;
use App\Http\Controllers\Admin\InventoryMonitorExportController;
use App\Http\Controllers\Admin\MaintenancesController;
use App\Http\Controllers\Admin\NotificationsController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\PositionsController;
use App\Http\Controllers\Admin\ProductsController;
use App\Http\Controllers\Admin\ReportsController;
use App\Livewire\Admin\Reports\TicketsReportsPage;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SedesController;
use App\Http\Controllers\Admin\ServicesController;
use App\Http\Controllers\Admin\TestsController;
use App\Http\Controllers\Admin\UbicacionesController;
use App\Http\Controllers\Admin\UserController;
use App\Livewire\Admin\Users\UserCreate;
use App\Livewire\Admin\Users\UserEdit;
use App\Livewire\Admin\Users\UserProfile;
use App\Livewire\Admin\Users\UsersIndex;
use App\Livewire\Admin\Users\UserShow;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\PasswordConfirm;
use App\Livewire\Auth\PasswordRequest;
use App\Livewire\Auth\PasswordReset;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas Principales y Autenticación
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('login');
});

// Throttle rutas de autenticación: 25 peticiones/minuto por IP (protección fuerza bruta)
Route::middleware(['throttle:25,1'])->group(function () {
    Auth::routes([
        'login' => false,
        'register' => false,
        'reset' => false,
        'confirm' => false,
    ]);
    Route::get('login', '\\'.Login::class)->middleware('guest')->name('login');
    Route::get('password/reset', '\\'.PasswordRequest::class)->middleware('guest')->name('password.request');
    Route::get('password/reset/{token}', '\\'.PasswordReset::class)->middleware('guest')->name('password.reset');
});

Route::middleware(['auth', 'throttle:25,1'])->group(function () {
    Route::get('password/confirm', '\\'.PasswordConfirm::class)->name('password.confirm');
});

// Rutas generales para usuarios logueados
Route::middleware(['auth', 'password.expiry'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home')->middleware('permission:read services');
    Route::get('get-service', [ServicesController::class, 'getService'])
        ->middleware('permission:read services');
});

// Gestión de cambio de contraseña (caducidad). Sin middleware `password.expiry` para evitar bucle de redirección.
Route::middleware(['auth'])->group(function () {
    Route::get('/password/change', [PasswordController::class, 'showChangeForm'])->name('password.change');
    // Nombre distinto de `password.update` (Auth::routes / reset) para permitir `php artisan route:cache`.
    Route::post('/password/change', [PasswordController::class, 'update'])->name('password.change.submit');
});

// Sesión: mantener viva (keepalive) y cierre por inactividad (el middleware SessionInactivity hace el logout)
Route::middleware(['auth', 'password.expiry'])->group(function () {
    Route::get('session/keepalive', function () {
        return response()->json(['ok' => true]);
    })->name('session.keepalive');
    Route::get('session/logout-inactivity', function () {
        return redirect()->route('login');
    })->name('session.logout-inactivity');
});

/*
|--------------------------------------------------------------------------
| RUTAS DEL PANEL DE ADMINISTRACIÓN (Controllers en App\Http\Controllers\Admin)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth', 'password.expiry'])
    ->group(function () {

        // --- Dashboard y Herramientas Generales ---
        Route::get('/', [AdminController::class, 'index'])->name('admin')->middleware('permission:read services');
        Route::get('/indicadores', [AdminController::class, 'indicadores_solos'])->middleware('permission:read services');
        // Agenda / Calendario
        Route::get('calendar', [CalendarController::class, 'index'])->name('admin.agenda.index')->middleware('permission:read calendar');
        Route::post('calendar/store', [CalendarController::class, 'store'])->name('admin.agenda.store')->middleware('permission:read calendar');
        Route::get('get_calendar', [CalendarController::class, 'get_calendar'])->middleware('permission:read calendar');
        Route::get('get-event', [CalendarController::class, 'get_event'])->middleware('permission:read calendar');
        Route::post('calendar/update', [CalendarController::class, 'update'])->name('admin.agenda.update')->middleware('permission:read calendar');

        // --- Gestión de Usuarios (Livewire) ---
        Route::get('users/{user}/summary', [UserController::class, 'summary'])->name('admin.users.summary')->middleware('permission:read users');
        Route::get('users', '\\'.UsersIndex::class)->name('admin.users.index')->middleware('permission:read users');
        Route::get('users/create', '\\'.UserCreate::class)->name('admin.users.create')->middleware('permission:create user');
        Route::get('users/{user}/edit', '\\'.UserEdit::class)->name('admin.users.edit')->middleware('permission:update user');
        Route::get('users/{user}', '\\'.UserShow::class)->name('admin.users.show')->middleware('permission:read users');
        Route::get('profile', '\\'.UserProfile::class)->name('profile');

        // Acciones Especiales (Certificación)
        Route::get('certification/{user}', [UserController::class, 'updateCertification'])->middleware('permission:edit certification');
        Route::post('certification-masiva', [UserController::class, 'certificacion_masiva'])
            ->name('admin.certification-masiva')
            ->middleware('permission:edit certification');

        Route::get('notifications', [NotificationsController::class, 'index'])->name('admin.notifications.index');
        Route::post('notifications/read-all', [NotificationsController::class, 'markAllAsRead'])->name('admin.notifications.read-all');
        Route::post('notifications/{id}/read', [NotificationsController::class, 'markAsRead'])->name('admin.notifications.read');
        Route::get('notifications/{id}/open', [NotificationsController::class, 'open'])->name('admin.notifications.open');

        // --- Roles y Permisos ---
        Route::resource('permissions', PermissionsController::class, ['except' => 'show', 'as' => 'admin']);
        Route::resource('roles', RoleController::class, ['except' => 'show', 'as' => 'admin']);

        // --- Mesa de Ayuda (Tickets y Servicios) ---
        Route::resource('services', ServicesController::class, ['except' => ['show', 'edit', 'destroy'], 'as' => 'admin'])->middleware('permission:read services');
        Route::put('service-validation/{id}', [ServicesController::class, 'validation'])->name('admin.service_validation');
        Route::get('get_finalizados', [ServicesController::class, 'get_finalizados'])->name('get_finalizados');
        Route::get('get_historial_services', [ServicesController::class, 'get_historial_services'])->name('get_historial_services');
        Route::post('/relanzar-servicio', [ServicesController::class, 'relanzarServicio'])->name('relanzar_servicio');

        // Campañas
        Route::resource('campaigns', CampaignsController::class, ['except' => 'show', 'as' => 'admin']);
        Route::post('campaigns/{campaign}/restore', [CampaignsController::class, 'restore'])->name('admin.campaigns.restore');
        Route::get('get_historial_review', [CampaignsController::class, 'get_historial_review'])->name('get_historial_review');
        Route::post('campanig/check_campaña', [CampaignsController::class, 'check_campaña'])->name('check_campaña');

        // --- Inventario y Activos (OLD & NEW) ---
        Route::resource('products', ProductsController::class, ['except' => 'show', 'as' => 'admin']);
        Route::get('get-products', [ProductsController::class, 'getProducts'])->name('get_products')->middleware('permission:read products');

        Route::get('aceptaProducto', [ProductsController::class, 'aceptaProducto'])->name('admin.aceptaProducto')->middleware('permission:read assignmentsIndividual');
        Route::get('/productos/download_all', [ProductsController::class, 'download_inventariocompleto'])
            ->name('producto.downloadall')
            ->middleware('permission:descarga_productosall');
        Route::get('products/{id}/history', [ProductsController::class, 'show'])->name('products.show');
        Route::post('products/unassign', [ProductsController::class, 'unassign'])->name('admin.products.unassign')->middleware('permission:update product');

        // Asignaciones
        Route::resource('assignments', AssignmentsController::class, ['except' => ['create', 'update'], 'as' => 'admin']);
        Route::get('get-assignments', [AssignmentsController::class, 'getAssignments'])->name('get_assignments');
        Route::get('get-desassignments', [AssignmentsController::class, 'getDesAssignments'])->name('get_desassignments');
        Route::get('assignments-remove', [AssignmentsController::class, 'removeAssignments'])->name('admin.assignments.remove')->middleware('permission:remove assignments');
        Route::get('assignments-list', [AssignmentsController::class, 'list'])->name('admin.assignments.list');
        Route::get('assignments-list2', [AssignmentsController::class, 'list2'])->name('admin.assignments.list2');
        Route::get('get-listassignments', [AssignmentsController::class, 'getListAssignments'])->name('get_listassignments');
        // El POST store ya lo define Route::resource('assignments', ...) como admin.assignments.store (evita nombre duplicado y fallo de route:cache).
        Route::get('get-listassignments2', [AssignmentsController::class, 'getListAssignments2'])->name('get_listassignments2');
        Route::get('assignments-log', [AssignmentsController::class, 'log'])->name('admin.assignments.log');
        Route::get('get-logassignments', [AssignmentsController::class, 'getLogAssignments'])->name('get_logassignments');
        Route::put('assignments-masiva', [AssignmentsController::class, 'masiva'])->name('admin.assignments-masiva');
        Route::delete('assignments-destroyMasiva', [AssignmentsController::class, 'destroyMasiva'])->name('admin.assignments-destroyMasiva');

        // Auditoría de Inventario / Revisiones
        Route::get('revisonAuditor', [ProductsController::class, 'revision_auditor'])->name('admin.revisionAuditor');
        Route::get('revision', [AssignmentsController::class, 'revisionIndex'])->name('admin.revision.index');
        Route::get('/revision/{id}/productos/{name}', [AssignmentsController::class, 'revisionShow']);
        Route::get('/assignments/{id}/productos/{name}', [AssignmentsController::class, 'Show']);
        Route::post('/revision/observacion', [ProductsController::class, 'revision_observacion'])->name('admin.revision.observation');
        Route::get('/revision/productos', [AssignmentsController::class, 'revisionproduct'])->name('admin.revision.product');
        Route::get('get_revisionproducto', [AssignmentsController::class, 'get_revisionproducto'])->name('get_revisionproducto');

        // --- Infraestructura y Configuración ---
        Route::resource('departments', DepartmentController::class, ['except' => 'show', 'as' => 'admin']);
        Route::post('departments/{department}/restore', [DepartmentController::class, 'restore'])->name('admin.departments.restore');
        Route::get('get-positions', [DepartmentController::class, 'getPositions']);

        Route::resource('positions', PositionsController::class, ['except' => 'show', 'as' => 'admin']);
        Route::post('positions/{position}/restore', [PositionsController::class, 'restore'])->name('admin.positions.restore');

        Route::resource('areas', AreasController::class, ['except' => 'show', 'as' => 'admin']);
        Route::post('areas/{area}/restore', [AreasController::class, 'restore'])->name('admin.areas.restore');

        Route::resource('companies', CompaniesController::class, ['except' => 'show', 'as' => 'admin']);
        Route::post('companies/{company}/restore', [CompaniesController::class, 'restore'])->name('admin.companies.restore');

        Route::resource('sedes', SedesController::class, ['except' => 'show', 'as' => 'admin']);
        Route::post('sedes/restore/{sede}', [SedesController::class, 'restore'])->name('admin.sedes.restore');
        // store/update/destroy: ya definidos por Route::resource('sedes', ...)

        Route::resource('ubicaciones', UbicacionesController::class, ['except' => 'show', 'as' => 'admin']);
        Route::post('ubicaciones/restore/{ubicacion}', [UbicacionesController::class, 'restore'])->name('admin.ubicaciones.restore');
        // store/update/destroy: ya definidos por Route::resource('ubicaciones', ...)
        Route::get('ubicaciones/{sedeID}', [ProductsController::class, 'getUbicacionesPorSede'])->name('get_ubicaciones_sedes');

        Route::resource('assets', AssetsController::class, ['except' => 'show', 'as' => 'admin']);
        Route::post('assets/{asset}/restore', [AssetsController::class, 'restore'])->name('admin.assets.restore');
        Route::get('get-assets', [AssetsController::class, 'getAssets'])->name('get_assets')->middleware('permission:read assets');

        Route::resource('components', ComponentsController::class, ['except' => 'show', 'as' => 'admin']);
        Route::post('componentes/restore/{componente}', [ComponentsController::class, 'restore'])->name('admin.components.restore');
        // store/update/destroy: resource (destroy = DELETE; las vistas deben usar @method('DELETE'))

        // --- Fallas e Incidencias ---
        Route::resource('failures', FailuresController::class, ['except' => 'show', 'as' => 'admin']);
        Route::post('failures/{failure}/restore', [FailuresController::class, 'restore'])->name('admin.failures.restore');
        Route::get('get-failures', [FailuresController::class, 'getFallas']);

        // CORRECCIÓN DE TYPO: 'delate' cambiado a 'delete'
        Route::resource('maintenances', MaintenancesController::class, ['except' => ['create', 'destroy'], 'as' => 'admin']);
        Route::get('get_mantenances', [MaintenancesController::class, 'get_mantenances'])->name('get_mantenances');

        Route::resource('incidents', IncidentsController::class, ['except' => 'show', 'as' => 'admin']);
        Route::get('get-incidents', [IncidentsController::class, 'getIncidents'])->name('get_incidents')->middleware('permission:read incidents');
        Route::get('incidentsEvents', [IncidentsController::class, 'incidentsEvents'])->name('admin.incidentsEvents')->middleware('permission:create incident');
        Route::get('ciberseguridad/incidencias', [IncidentsController::class, 'ciberseguridad'])->name('ciberseguridad.incidencias')->middleware('permission:read incidents');
        Route::get('/ciberseguridad/create', [IncidentsController::class, 'createSeguridad'])->name('ciberseguridad.create')->middleware('permission:create incident');
        Route::post('/guardar-seguridad', [IncidentsController::class, 'saveSeguridad'])->name('guardar.seguridad')->middleware('permission:create incident');
        Route::get('subcategorias/{id}', [IncidentsController::class, 'obtenerSubcategoria'])->middleware('permission:read incidents');

        // Grupo Contenido CTG
        Route::prefix('contenido/ctg')->group(function () {
            // Incidencias
            Route::get('incidencias', [ContenidoCtgsController::class, 'index'])->name('admin.contenido.ctg.incidencia.index');
            Route::get('create', [ContenidoCtgsController::class, 'create'])->name('admin.contenido.ctg.incidencia.create');
            Route::post('store', [ContenidoCtgsController::class, 'store'])->name('admin.contenido.ctg.incidencia.store');
            Route::get('edit/{sistema}', [ContenidoCtgsController::class, 'edit'])->name('admin.contenido.ctg.incidencia.edit');
            Route::put('update', [ContenidoCtgsController::class, 'update'])->name('admin.contenido.ctg.incidencia.update');
            Route::post('destroy/{sistema}', [ContenidoCtgsController::class, 'destroy'])->name('admin.contenido.ctg.incidencia.destroy');
            Route::post('restore/{sistema}', [ContenidoCtgsController::class, 'restore'])->name('admin.contenido.ctg.incidencia.restore');
            // Productos
            Route::get('producto', [ContenidoCtgsController::class, 'indexProducto'])->name('admin.contenido.ctg.productos.index');
            Route::get('producto/create', [ContenidoCtgsController::class, 'createProducto'])->name('admin.contenido.ctg.productos.create');
            Route::post('producto/store', [ContenidoCtgsController::class, 'storeProducto'])->name('admin.contenido.ctg.productos.store');
            Route::get('producto/edit/{sistema}', [ContenidoCtgsController::class, 'editProducto'])->name('admin.contenido.ctg.productos.edit');
            Route::put('producto/update', [ContenidoCtgsController::class, 'updateProducto'])->name('admin.contenido.ctg.productos.update');
            Route::post('producto/destroy/{sistema}', [ContenidoCtgsController::class, 'destroyProducto'])->name('admin.contenido.ctg.productos.destroy');
            Route::post('producto/restore/{sistema}', [ContenidoCtgsController::class, 'restoreProducto'])->name('admin.contenido.ctg.productos.restore');
        });

        // --- Telefonía (DID) ---
        Route::get('did', [DidController::class, 'index'])->name('did');
        Route::get('get_did', [DidController::class, 'get_did'])->name('get_did');
        Route::get('did/create', [DidController::class, 'create'])->name('did.create');
        Route::post('did/store', [DidController::class, 'store'])->name('did.store');
        Route::get('did/show/{id}', [DidController::class, 'show'])->name('did.show');
        Route::post('did/update/{id}', [DidController::class, 'update'])->name('did.update');
        Route::get('/obtener_detalle_did', [DidController::class, 'detalle'])->name('did.detalle');

        // --- Pruebas y Tests ---
        Route::resource('tests', TestsController::class, ['except' => 'show', 'as' => 'admin']);
        Route::get('get-tests', [TestsController::class, 'getTests'])->name('get_tests')->middleware('permission:read tests');
        Route::get('nivel', [TestsController::class, 'nivel2'])->name('admin.nivel')->middleware('permission:read tests');

        // --- Bitácoras y Auditoría (Trazabilidad) ---
        Route::resource('bitacora', BitacoraController::class, ['as' => 'admin']);
        Route::get('get-bitacoras', [BitacoraController::class, 'getBitacoras'])->name('get_bitacoras');
        Route::resource('bitacoraHost', BitacoraHostController::class, ['as' => 'admin']);
        Route::get('get-bitacorasHost', [BitacoraHostController::class, 'getBitacoras'])->name('get_bitacorasHost');

        // Trazabilidad ISO 27000 (Controller tradicional)
        Route::get('audits', [AuditController::class, 'index'])->name('admin.audits.index')->middleware('role:Admin|Soporte');

        // --- Reportes ---
        Route::get('reports', '\\'.TicketsReportsPage::class)->name('admin.reports.index')->middleware('permission:read reports ticket');
        Route::get('reports/inventory', [ReportsController::class, 'inventory'])->name('admin.reports.inventory')->middleware('permission:read reports inventory');
        Route::get('download', [ReportsController::class, 'download'])
            ->name('admin.reports.download')
            ->middleware('permission:read reports ticket|read reports inventory');

        // Datos JSON para Reportes (AJAX)
        Route::get('report-areas', [ReportsController::class, 'getReportArea'])->middleware('permission:read reports ticket|read reports inventory');
        Route::get('report-inventario', [ReportsController::class, 'getReportInventario'])->middleware('permission:read reports ticket|read reports inventory');
        Route::get('report-incidencias', [ReportsController::class, 'getReportIncidencia'])->middleware('permission:read reports ticket|read reports inventory');
        Route::get('report-incidencias-sistemas', [ReportsController::class, 'getReportIncidenciaSistemas'])->middleware('permission:read reports ticket|read reports inventory');
        Route::get('report-incidencias-sede-sistemas', [ReportsController::class, 'reportIncidenciaSoporteUsuario'])->middleware('permission:read reports ticket|read reports inventory');
        Route::get('report-usuarios-soporte', [ReportsController::class, 'getReportPersonalSoporte'])->middleware('permission:read reports ticket|read reports inventory');
        Route::get('report-tickets-sede', [ReportsController::class, 'getReportSedeTicket'])->middleware('permission:read reports ticket|read reports inventory');
        Route::get('report-tiempo-fallas', [ReportsController::class, 'getReportTimeFalla'])->middleware('permission:read reports ticket|read reports inventory');
        Route::get('report-failures', [ReportsController::class, 'getReportFailure'])->middleware('permission:read reports ticket|read reports inventory');
        Route::get('report-users', [ReportsController::class, 'getReportUser'])->middleware('permission:read reports ticket|read reports inventory');
        Route::get('report-days', [ReportsController::class, 'getReportDay'])->middleware('permission:read reports ticket|read reports inventory');
        Route::get('report-areas-solution', [ReportsController::class, 'getReportAreaSolution'])->middleware('permission:read reports ticket|read reports inventory');
        Route::get('report-users-solution', [ReportsController::class, 'getReportUserSolution'])->middleware('permission:read reports ticket|read reports inventory');
        Route::get('report-time', [ReportsController::class, 'getReportTime'])->middleware('permission:read reports ticket|read reports inventory');
        Route::get('report-time-incidencia', [ReportsController::class, 'getReportTimeIncidencias'])->middleware('permission:read reports ticket|read reports inventory');
        Route::get('report_download_mantenimiento', [ReportsController::class, 'report_download_mantenimiento'])->name('report.download.mantenimiento')->middleware('permission:read reports ticket|read reports inventory');
        Route::get('report_download_sistemas', [ReportsController::class, 'report_download_sistemas'])->name('report.download.sistemas')->middleware('permission:read reports ticket|read reports inventory');
        Route::get('report-detail', [ReportsController::class, 'getDetail'])->middleware('permission:read reports ticket|read reports inventory');
        Route::get('report-detail-time-incidencia', [ReportsController::class, 'getDetailTimeIncidencias'])->middleware('permission:read reports ticket|read reports inventory');
        Route::get('report-incidents', [ReportsController::class, 'getDetailIncidents'])->middleware('permission:read reports ticket|read reports inventory');
        Route::get('report-incidents-sistemas', [ReportsController::class, 'getDetailIncidentsSistemas'])->middleware('permission:read reports ticket|read reports inventory');
        Route::get('report-user-soporte', [ReportsController::class, 'getDetailUsuarioSoporte'])->middleware('permission:read reports ticket|read reports inventory');
        Route::get('report-sede', [ReportsController::class, 'getDetailSede'])->middleware('permission:read reports ticket|read reports inventory');
        Route::get('report-time-falla', [ReportsController::class, 'getDetailTimeFalla'])->middleware('permission:read reports ticket|read reports inventory');
    });

/*
|--------------------------------------------------------------------------
| RUTAS LIVEWIRE / COMPONENTES
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth', 'password.expiry'])
    ->group(function () {
        // Cadenas con \ inicial: RouteServiceProvider añade namespace App\Http\Controllers si no empiezan por \.
        Route::get('active-sessions', '\App\Livewire\Admin\Sessions\ActiveSessions')
            ->name('admin.sessions.active')
            ->middleware('role:Admin|Soporte');

        Route::get('/v2/inventario', '\App\Livewire\Inventory\InventoryIndex')
            ->name('inventory.v2.index')
            ->middleware('permission:read inventory');

        Route::get('/v2/mantenimientos', '\App\Livewire\Inventory\InventoryMaintenanceIndex')
            ->name('inventory.v2.maintenance')
            ->middleware('permission:read inventory');

        Route::get('/v2/asignaciones/resumen', '\App\Livewire\Inventory\AssignmentsSummaryIndex')
            ->name('inventory.v2.assignments.summary')
            ->middleware('permission:read inventory');

        Route::get('/v2/mis-asignaciones', '\App\Livewire\Inventory\OwnAssignmentsIndex')
            ->name('inventory.v2.my-assignments')
            ->middleware('permission:read inventory own assignments');

        Route::get('/v2/asignaciones', '\App\Livewire\Inventory\AssignmentsIndex')
            ->name('inventory.v2.assignments')
            ->middleware('permission:read inventory');

        Route::get('/v2/historial-asignaciones', '\App\Livewire\Inventory\InventoryAssignmentsHistoryIndex')
            ->name('inventory.v2.assignment-history')
            ->middleware('permission:read inventory|read inventory assignment history');

        Route::get('/v2/monitor', '\App\Livewire\Inventory\MonitorIndex')
            ->name('inventory.v2.monitor')
            ->middleware('permission:read inventory monitor');

        Route::get('/v2/pendientes', '\App\Livewire\Inventory\InventoryPendingIndex')
            ->name('inventory.v2.pending')
            ->middleware('permission:read inventory');

        Route::get('/v2/config/estatus', '\App\Livewire\Inventory\StatusManager')
            ->name('inventory.config.status')
            ->middleware('permission:manage inventory config');

        Route::get('/v2/config/categorias', '\App\Livewire\Inventory\CategoryManager')
            ->name('inventory.config.categories')
            ->middleware('permission:manage inventory config');

        Route::get('/v2/config/etiquetas', '\App\Livewire\Inventory\LabelManager')
            ->name('inventory.config.labels')
            ->middleware('permission:manage inventory config|manage inventory labels');

        Route::get('/v2/config/mantenimiento-catalogos', '\App\Livewire\Inventory\MaintenanceCatalogManager')
            ->name('inventory.config.maintenance-catalogs')
            ->middleware('permission:manage inventory maintenance catalogs');

        Route::get('/v2/componentes', '\App\Livewire\Inventory\ComponentIndex')
            ->name('inventory.components')
            ->middleware('permission:read inventory');

        Route::get('inventory/export', InventoryExportController::class)
            ->name('inventory.export')
            ->middleware('permission:read inventory');

        Route::get('/v2/historial-asignaciones/export', InventoryAssignmentHistoryExportController::class)
            ->name('inventory.v2.assignment-history.export')
            ->middleware('permission:read inventory|read inventory assignment history');

        Route::get('/v2/monitor/export/changes', [InventoryMonitorExportController::class, 'exportChanges'])
            ->name('inventory.monitor.export.changes')
            ->middleware('permission:read inventory monitor');
        Route::get('/v2/monitor/export/alerts', [InventoryMonitorExportController::class, 'exportAlertDetail'])
            ->name('inventory.monitor.export.alerts')
            ->middleware('permission:read inventory monitor');
    });
