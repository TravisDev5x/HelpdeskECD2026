<?php

namespace App\Http\Controllers\Admin;

use App\Models\DetailIncident;
use App\Models\DetailSede;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportsWorkbookExport;
use App\Models\Service;
use App\Models\DetailService;
use App\Models\DetailUsuariosSoporte;
use App\Exports\InventarioExportMantenimiento;
use App\Exports\InventarioExportSistemas;
use App\Models\Incident;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Support\Tickets\TicketStatus;

class ReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:read reports ticket|read reports inventory']);
    }

    public function download(Request $request)
    {
        $request->validate([
            'fechaInicio' => 'required|date',
            'fechaFin' => 'required|date|after_or_equal:fechaInicio',
        ]);

        $start = $request->fechaInicio . ' 00:00:00';
        $end = $request->fechaFin . ' 23:59:59';

        $services = DetailService::whereBetween('fecha_solicitud', [$request->fechaInicio, $end])
            ->orderBy('fecha_solicitud')
            ->get();

        $totalTickets = Service::whereBetween('created_at', [$start, $end])->count();
        $totalFinalizados = Service::whereBetween('created_at', [$start, $end])
            ->where('status', TicketStatus::FINALIZADO)
            ->count();
        $totalPendientes = max(0, $totalTickets - $totalFinalizados);
        $totalIncidencias = Incident::whereBetween('created_at', [$start, $end])->count();

        $kpis = [
            'totalTickets' => $totalTickets,
            'totalFinalizados' => $totalFinalizados,
            'totalPendientes' => $totalPendientes,
            'totalIncidencias' => $totalIncidencias,
        ];

        $porEstatus = $this->getReportDay($request)
            ->map(fn ($r) => ['name' => (string) $r->name, 'total' => (int) $r->total])
            ->values()
            ->all();

        $porArea = $this->getReportArea($request)
            ->map(fn ($r) => ['name' => (string) $r->name, 'total' => (int) $r->total])
            ->values()
            ->all();

        $porResponsable = $this->getReportUserSolution($request)
            ->map(fn ($r) => ['name' => (string) $r->name, 'total' => (int) $r->total])
            ->values()
            ->all();

        $nombreArchivo = sprintf(
            'Reportes Tickets %s %s.xlsx',
            $request->fechaInicio,
            $request->fechaFin
        );

        return Excel::download(
            new ReportsWorkbookExport(
                $request->fechaInicio,
                $request->fechaFin,
                $kpis,
                $porEstatus,
                $porArea,
                $porResponsable,
                $services,
            ),
            $nombreArchivo
        );
    }

    public function getReportArea(Request $request)
    {
        $data = DetailService::selectRaw('area_solicita as name')
            ->selectRaw('count(*) as total')
            ->whereBetween('fecha_solicitud', [$request->fechaInicio . ' 00:00:00', $request->fechaFin . ' 23:59:59'])
            ->groupBy('area_solicita')
            ->orderBy('total', 'desc')
            ->get();

        return $data;
    }

    public function getReportInventario(Request $request)
    {
        $data = Product::select('owner as name', DB::raw('count(id) as total'))
            // ->whereBetween('created_at', [$request->fechaInicio, $request->fechaFin . ' 23:59:59'])
            ->groupBy('owner')
            ->orderBy('total', 'asc')
            ->take(10)
            ->get();

        return $data;
    }

    public function getReportIncidencia(Request $request)
    {
        $data = Incident::join('users', 'users.id', '=', 'incidents.user_id')
            ->selectRaw('CASE
                          WHEN incidents.criticidad = 1 THEN "BAJA"
                          WHEN incidents.criticidad = 2 THEN "MEDIA"
                          WHEN incidents.criticidad = 3 THEN "ALTA"
                      END AS name')
            ->selectRaw('count(incidents.id) as total')
            ->whereBetween('incidents.disqualification_date', [$request->fechaInicio, $request->fechaFin . ' 23:59:59'])
            ->groupBy('incidents.criticidad')
            ->orderBy('total', 'asc')
            ->take(10)
            ->get();

        return $data;
    }

    public function getReportIncidenciaSistemas(Request $request)
    {
        $data = Incident::join('users', 'users.id', '=', 'incidents.user_id')
            ->selectRaw('incidents.sistema AS name')
            ->selectRaw('count(incidents.id) as total')
            ->whereBetween('incidents.created_at', [$request->fechaInicio, $request->fechaFin . ' 23:59:59'])
            ->groupBy('incidents.sistema')
            ->orderBy('total', 'asc')
            ->get();

        return $data;
    }

    public function reportIncidenciaSoporteUsuario(Request $request)
    {
        $users = [
            'Vallejo' => [
                '970' => 'ABRAHAM',
                '1011' => 'ERIC RAFAEL'
            ],
            'Toledo' => [
                '545' => 'EDUARDO',
                '668' => 'JOSE TRINIDAD'
            ],
            'Tlalpan' => [
                '97' => 'BRAYAN',
                '673' => 'LEONEL',
                '500' => 'VICTOR',
                '1009' => 'RICARDO'
            ]
        ];

        // Obtenemos todos los usuarios de los incidents (puedes agregar filtros por fecha si lo necesitas)
        $incidents = \DB::table('incidents')->pluck('user_id');


        // Contadores por sede
        $counts = [
            'Vallejo' => 0,
            'Toledo' => 0,
            'Tlalpan' => 0,
//            'Sin sede' => 0,
        ];

        foreach ($incidents as $usuario) {
            $found = false;

            foreach ($users as $sede => $empleados) {
                if (array_key_exists($usuario, $empleados)) {
                    $counts[$sede]++;
                    $found = true;
                    break;
                }
            }

//            if (!$found) {
//                $counts['Sin sede']++;
//            }
        }

        // Convertimos a formato JSON compatible con Highcharts
        $data = collect($counts)->map(function ($total, $name) {
            return [
                'name' => $name,
                'total' => $total
            ];
        })->values();

        return response()->json($data);
    }

    public function getReportFailure(Request $request)
    {
        $data = Service::join('failures', 'failures.id', '=', 'services.failure_id')
            ->selectRaw('failures.name')
            ->selectRaw('count(services.id) as total')
            // ->where('services.created_at', '>=', $request->fechaInicio.' 00:00:00')
            // ->where('services.created_at', '<=', $request->fechaInicio.' 23:59:59')
            ->whereBetween('services.created_at', [$request->fechaInicio, $request->fechaFin . ' 23:59:59'])
            ->groupBy('failures.name')
            ->orderBy('total', 'desc')
            ->take(10)
            ->get();
        // dd($data);
        return $data;
    }

    public function getReportUser(Request $request)
    {
        //dd(date_format($request->all(), 'Y-mm-dd'));
        $data = Service::join('users', 'users.id', '=', 'services.user_id')
            ->selectRaw('users.name')
            ->selectRaw('count(services.id) as total')
            // ->where('services.created_at', '>=', $request->fechaInicio.' 00:00:00')
            // ->where('services.created_at', '<=', $request->fechaInicio.' 23:59:59')
            ->whereBetween('services.created_at', [$request->fechaInicio . ' 00:00:00', $request->fechaFin . ' 23:59:59'])
            ->groupBy('users.name')
            ->orderBy('total', 'desc')
            ->take(10)
            ->get();
        // dd($data);
        return $data;
    }

    public function getReportDay(Request $request)
    {
        $data = Service::selectRaw('status as name')
            ->selectRaw('count(id) as total')
            // ->where('services.created_at', '>=', $request->fechaInicio.' 00:00:00')
            // ->where('services.created_at', '<=', $request->fechaInicio.' 23:59:59')
            ->whereBetween('created_at', [$request->fechaInicio, $request->fechaFin . ' 23:59:59'])
            ->groupBy('status')
            ->orderBy('total', 'desc')
            ->take(10)
            ->get();

        return $data;
    }


    public function getReportAreaSolution(Request $request)
    {
        $data = Service::join('failures', 'failures.id', '=', 'services.failure_id')
            ->join('areas', 'areas.id', '=', 'failures.area_id')
            ->selectRaw('areas.name')
            ->selectRaw('count(services.id) as total')
            // ->where('services.created_at', '>=', $request->fechaInicio.' 00:00:00')
            // ->where('services.created_at', '<=', $request->fechaInicio.' 23:59:59')
            ->whereBetween('services.created_at', [$request->fechaInicio, $request->fechaFin . ' 23:59:59'])
            ->groupBy('areas.name')
            ->orderBy('total', 'desc')
            ->take(10)
            ->get();

        return $data;
    }

    public function getReportUserSolution(Request $request)
    {
        $data = Service::join('users', 'users.id', '=', 'services.responsable_id')
            ->selectRaw('users.name')
            ->selectRaw('count(services.id) as total')
            // ->where('services.created_at', '>=', $request->fechaInicio.' 00:00:00')
            // ->where('services.created_at', '<=', $request->fechaInicio.' 23:59:59')
            ->whereBetween('services.created_at', [$request->fechaInicio, $request->fechaFin . ' 23:59:59'])
            ->groupBy('users.name')
            ->orderBy('total', 'desc')
            ->take(10)
            ->get();

        return $data;
    }

    public function getReportTime(Request $request)
    {
        $data = Service::join('failures', 'failures.id', '=', 'services.failure_id')
            ->join('areas', 'areas.id', '=', 'failures.area_id')
            ->selectRaw('areas.name')
            ->selectRaw('ROUND(TIMESTAMPDIFF(MINUTE, services.created_at, services.fecha_fin) / 60) as diferencia_hours') // Dividir por 60 para obtener horas
            ->where('services.status', TicketStatus::FINALIZADO)
            ->whereBetween('services.created_at', [$request->fechaInicio . ' 00:00:00', $request->fechaFin . ' 23:59:59'])
            ->orderBy('areas.name', 'asc')
            ->orderBy('diferencia_hours', 'asc')
            ->get();

        $result = collect();

        foreach ($data as $servicio) {
            $area = $servicio->name;
            if (!isset($serviciosPorArea[$area])) {
                $serviciosPorArea[$area] = collect(); // Inicializa una nueva colección si no existe
            }
            $serviciosPorArea[$area]->push($servicio->diferencia_hours); // Agrega el servicio a la colección correspondiente al área
        }

        foreach ($data as $servicio) {
            if ($area != $servicio->name) {
                $area = $servicio->name;
                $contador = $serviciosPorArea[$area]->toArray();
                sort($contador); // Ordena los números de menor a mayor
                $mediana = collect();
                $totalNumeros = count($contador);
                $indiceMediana = floor(($totalNumeros - 1) / 2); // Índice del valor mediano

                if ($totalNumeros % 2 == 1) {
                    $mediana = $contador[$indiceMediana];
                } else {
                    $mediana = ($contador[$indiceMediana] + $contador[$indiceMediana + 1]) / 2;
                }

                $arregloInterior = [
                    'name' => $area,
                    'total' => $mediana
                ];

                $result[] = $arregloInterior;
            }
        }

        return $result;
    }

    public function getReportTimeIncidencias(Request $request)
    {
        $inicio = Carbon::parse($request->fechaInicio)->startOfDay()->toDateTimeString();
        $fin = Carbon::parse($request->fechaFin)->endOfDay()->toDateTimeString();
        $data = DetailIncident::selectRaw('sistema as name')
            ->selectRaw('SUM(ROUND(TIMESTAMPDIFF(MINUTE, fecha_falla, fecha_solucion) / 60)) as diferencia_hours')
            ->whereBetween('fecha_creacion', [$inicio, $fin])
            ->groupBy('name')
            ->orderBy('name', 'asc')
            ->get();

        $result = $data->map(function ($item) {
            $item['remaining_hours'] = 720 - $item['diferencia_hours'];
            $item['remaining_percentage'] = ($item['remaining_hours'] / 720) * 100;
            return [
                'name' => $item['name'],
                'total' => $item['remaining_percentage']
            ];
        });

        return $result->values();
    }

    public function getReportPersonalSoporte(Request $request)
    {
        $data = Service::join('users', 'users.id', '=', 'services.responsable_id')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->selectRaw('users.name')
            ->selectRaw('ROUND(AVG(TIMESTAMPDIFF(MINUTE, services.created_at, services.fecha_fin))  / 60) as total')
            ->where('roles.name', 'Soporte')
            ->where('status', TicketStatus::FINALIZADO)
            ->whereBetween('services.created_at', [$request->fechaInicio, $request->fechaFin . ' 23:59:59'])
            ->groupBy('users.name')
            ->orderBy('total', 'desc')
            ->take(10)
            ->get();

        return $data;
    }

    public function getReportSedeTicket(Request $request)
    {
        $data = Service::join('users', 'users.id', '=', 'services.responsable_id')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->selectRaw('services.sede_id AS name, count(services.id) as total')
            ->where('roles.name', 'Soporte')
            ->whereBetween('services.created_at', [$request->fechaInicio, $request->fechaFin . ' 23:59:59'])
            ->groupBy('services.sede_id')
            ->orderBy('total', 'desc')
            ->get();

        return $data;
    }

    public function getReportTimeFalla(Request $request)
    {
        $data = Service::join('users', 'users.id', '=', 'services.responsable_id')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->join('failures', 'failures.id', '=', 'services.failure_id')
            ->selectRaw('failures.name AS name, ROUND(AVG(TIMESTAMPDIFF(MINUTE, services.created_at, services.fecha_fin)) / 60) AS total') // Dividir por 60 para obtener horas
            ->where('roles.name', 'Soporte') // Filtra por el rol 'Soporte'
            ->whereBetween('services.created_at', [$request->fechaInicio, $request->fechaFin . ' 23:59:59'])
            ->groupBy('failures.name')
            ->orderBy('total', 'desc') // Ordenar por horas en lugar de minutos
            ->take(10) // No necesitas pasar '10' como una cadena
            ->get();
        // dd($data);

        return $data;
    }

    public function getDetail(Request $request)
    {
        if ($request->type == 'Areas') {
            $data = DetailService::where('area_solicita', $request->filter)
                //   ->where('services.created_at', '>=', $request->fechaInicio.' 00:00:00')
                // ->where('services.created_at', '<=', $request->fechaInicio.' 23:59:59')
                ->whereBetween('fecha_solicitud', [$request->fechaInicio, $request->fechaFin . ' 23:59:59'])
                ->get();
        } else if ($request->type == 'Failures') {
            $data = DetailService::where('falla', $request->filter)
                //   ->where('services.created_at', '>=', $request->fechaInicio.' 00:00:00')
                // ->where('services.created_at', '<=', $request->fechaInicio.' 23:59:59')
                ->whereBetween('fecha_solicitud', [$request->fechaInicio, $request->fechaFin . ' 23:59:59'])
                ->get();
            //   dd($data);
        } else if ($request->type == 'Users') {
            $data = DetailService::where('usuario', $request->filter)
                //   ->where('services.created_at', '>=', $request->fechaInicio.' 00:00:00')
                // ->where('services.created_at', '<=', $request->fechaInicio.' 23:59:59')
                ->whereBetween('fecha_solicitud', [$request->fechaInicio, $request->fechaFin . ' 23:59:59'])
                ->get();
        } else if ($request->type == 'Days') {
            $data = DetailService::where('status', $request->filter)
                //   ->where('services.created_at', '>=', $request->fechaInicio.' 00:00:00')
                // ->where('services.created_at', '<=', $request->fechaInicio.' 23:59:59')
                ->whereBetween('fecha_solicitud', [$request->fechaInicio, $request->fechaFin . ' 23:59:59'])
                ->get();
        } else if ($request->type == 'AreaSolutions') {
            $data = DetailService::where('area_atiende', $request->filter)
                //   ->where('services.created_at', '>=', $request->fechaInicio.' 00:00:00')
                // ->where('services.created_at', '<=', $request->fechaInicio.' 23:59:59')
                ->whereBetween('fecha_solicitud', [$request->fechaInicio, $request->fechaFin . ' 23:59:59'])
                ->get();
        } else if ($request->type == 'UserSolutions') {
            $data = DetailService::where('responsable', $request->filter)
                //   ->where('services.created_at', '>=', $request->fechaInicio.' 00:00:00')
                // ->where('services.created_at', '<=', $request->fechaInicio.' 23:59:59')
                ->whereBetween('fecha_solicitud', [$request->fechaInicio, $request->fechaFin . ' 23:59:59'])
                ->get();
        } else if ($request->type == 'UserSolutions') {
            $data = DetailService::where('responsable', $request->filter)
                //   ->where('services.created_at', '>=', $request->fechaInicio.' 00:00:00')
                // ->where('services.created_at', '<=', $request->fechaInicio.' 23:59:59')
                ->whereBetween('fecha_solicitud', [$request->fechaInicio, $request->fechaFin . ' 23:59:59'])
                ->get();
        }

        return $data;
    }

    public function getDetailTimeIncidencias(Request $request)
    {
        $data = DetailIncident::where('sistema', $request->filter)
            //   ->where('services.created_at', '>=', $request->fechaInicio.' 00:00:00')
            // ->where('services.created_at', '<=', $request->fechaInicio.' 23:59:59')
            ->whereBetween('fecha_creacion', [$request->fechaInicio, $request->fechaFin . ' 23:59:59'])
            ->get();
        return $data;
    }

    public function getDetailIncidents(Request $request)
    {
        // Mapea las palabras a los valores numéricos correspondientes
        $criticidadMapping = [
            'BAJA' => 1,
            'MEDIA' => 2,
            'ALTA' => 3
        ];

        // Verifica si el valor del filtro existe en el mapeo
        if (array_key_exists($request->filter, $criticidadMapping)) {
            // Obtiene el valor numérico correspondiente al filtro
            $criticidad = $criticidadMapping[$request->filter];

            // Realiza la consulta utilizando el valor numérico
            $data = DetailIncident::where('criticidad', $criticidad)
                ->whereBetween('fecha_creacion', [$request->fechaInicio, $request->fechaFin . ' 23:59:59'])
                ->get();
        } else {
            // Si el filtro no coincide con ninguno de los valores mapeados, devuelve un resultado vacío o maneja el error según corresponda
            $data = [];
        }

        return $data;
    }

    public function getDetailIncidentsSistemas(Request $request)
    {
        // Verifica si el sistema del filtro es válido
        $sistema = $request->filter;

        // Realiza la consulta filtrando por sistema y rango de fechas
        $data = DetailIncident::where('sistema', $sistema)
            ->whereBetween('fecha_creacion', [$request->fechaInicio, $request->fechaFin . ' 23:59:59'])
            ->get();

        return $data;
    }

    public function getDetailUsuarioSoporte(Request $request)
    {
        if ($request->type == 'UsuarioSoporte') {
            $data = DetailUsuariosSoporte::where('atendio', $request->filter)
                ->whereBetween('fecha_solicitud', [$request->fechaInicio, $request->fechaFin . ' 23:59:59'])
                ->get();
        }

        return $data;
    }

    public function getDetailSede(Request $request)
    {
        if ($request->type == 'Sedes') {
            $data = DetailSede::where('sede', $request->filter)->where('rol', 'Soporte')
                ->whereBetween('fecha_solicitud', [$request->fechaInicio, $request->fechaFin . ' 23:59:59'])
                ->get();
        }

        return $data;
    }

    public function getDetailTimeFalla(Request $request)
    {
        if ($request->type == 'TiempoFallas') {
            $data = DetailSede::where('falla', $request->filter)->where('rol', 'Soporte')
                ->whereBetween('fecha_solicitud', [$request->fechaInicio, $request->fechaFin . ' 23:59:59'])
                ->get();
        }

        return $data;
    }

    public function inventory()
    {
        return view('admin.reports.inventory');
    }

    public function report_download_mantenimiento()
    {
        return Excel::download(new InventarioExportMantenimiento(), 'Reporte de tickets mantenimiento.xlsx');
    }

    public function report_download_sistemas()
    {
        return Excel::download(new InventarioExportSistemas(), 'Reporte de tickets sistemas.xlsx');
    }
}
