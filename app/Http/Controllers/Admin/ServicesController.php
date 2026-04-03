<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Notifications\InternalUserNotification;
use App\Notifications\TicketCreated;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Notification;
use App\Models\Area;
use App\Models\Failure;
use App\Models\Service;
use App\Models\HistoricalServices;
use App\Support\Tickets\TicketStatus;
use App\Models\Sede;
use App\Models\User;
use App\Support\DashboardTicketStats;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Support\Tickets\ServiceActivoCriticoGeneralApproval;
use App\Support\Tickets\ServiceFollowUpStatusApplier;
use App\Support\Tickets\ServiceObservationAppender;
use App\Support\Notifications\InternalNotificationRecipients;
use App\Support\Tickets\TicketQueryByRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ServicesController extends Controller
{
  public function __construct()
  {
    $this->middleware(['permission:create service'], ['only' => ['create', 'store']]);
    $this->middleware(['permission:read services'], ['only' => ['index', 'get_finalizados', 'get_historial_services', 'getService', 'validation']]);
    $this->middleware(['permission:update service'], ['only' => ['update', 'relanzarServicio']]);
  }

  public function index()
  {
    $hoy = Carbon::now();
    $stats = DashboardTicketStats::counts();

    return view('admin.services.finalizados', array_merge($stats, compact('hoy')));
  }

  public function create()
  {
    $areas = Area::pluck('name', 'id');
    $sedes = Sede::select('id', 'sede')->orderBy('sede')->get();
    return view('admin.services.create', compact('areas', 'sedes'));
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'failure_id' => ['required'],
      'description' => ['required'],
      'sede_id' => ['required'],
    ]);

    $data['user_id'] = auth()->id();
    $data['status'] = TicketStatus::PENDIENTE;

    $service = Service::create($data);
    $data['service_id'] = $service->id;
    HistoricalServices::create($data);

    // Nuevo ticket: aviso en panel y correo solo para Admin (Soporte/Infra ven notifs de tickets asignados a ellos).
    $failure = Failure::with('area')->find($data['failure_id']);
    if ($failure) {
      $users = InternalNotificationRecipients::withPermissionScoped(
          'receive internal notification ticket created',
          static fn (Builder $q) => $q->whereNotNull('email')
      );

      $ticketUrl = route('home') . '?id=' . $service->id;
      $messageBody = sprintf(
        'Falla: %s · %s',
        $failure->name,
        Str::limit(strip_tags((string) $service->description), 140)
      );

      if ($users->isNotEmpty()) {
        try {
          Notification::send(
            $users,
            new InternalUserNotification(
              'Nuevo ticket #' . $service->id,
              $messageBody,
              $ticketUrl,
              'ticket_created'
            )
          );
        } catch (\Throwable $e) {
          Log::error('No se pudo guardar notificación en base de datos al crear ticket', [
            'service_id' => $service->id,
            'error' => $e->getMessage(),
          ]);
        }

        try {
          Notification::send($users, new TicketCreated($service, $failure->name));
        } catch (\Throwable $e) {
          Log::warning('No se pudo enviar correo TicketCreated', [
            'service_id' => $service->id,
            'error' => $e->getMessage(),
          ]);
        }
      }
    }

    return redirect()->route('home')->with('flash', 'Ticket guardado');
  }

  public function getService(Request $request)
  {
    $datos = Service::with('failure')->whereId($request->id)->firstOrFail();
    Gate::authorize('view', $datos);

    return $datos;
  }

  public function update(Request $request, $id)
  {
    $serviceAux = Service::whereId($request->id)->firstOrFail();
    Gate::authorize('update', $serviceAux);

    if (! $request->status) {
      $data = $request->validate([
        'id' => ['required'],
        'observations' => [
          'required',
        ],
      ]);
      ServiceObservationAppender::run($serviceAux, $data['observations']);
      $serviceAux->refresh();
      if ((int) $serviceAux->failure_id === 31) {
        return back();
      }

      return response()->json(['mensaje' => 'OK']);
    }

    $validated = $request->validate([
      'id' => ['required'],
      'status' => [
        'required',
        \Illuminate\Validation\Rule::in(TicketStatus::all()),
      ],
      'solution' => $request->status == TicketStatus::FINALIZADO ? ['required'] : ['nullable'],
      'observations' => [
        'required',
      ],
    ]);

    $actor = Auth::user();
    if (! $actor instanceof User) {
      abort(403);
    }

    $serviceHistorical = ServiceFollowUpStatusApplier::apply($serviceAux, $actor, $validated);

    if ($serviceHistorical->failure_id == 31) {
      return back();
    } else {

      return response()->json(['mensaje' => 'OK']);
    }
  }

  public function validation(Request $request, $id)
  {
    $service = Service::findOrFail($id);
    Gate::authorize('update', $service);

    ServiceActivoCriticoGeneralApproval::apply($service);

    return back();
  }

  public function get_finalizados()
  {
    $inicio = Carbon::now()->subMonths(1)->format('Y-m-d H:i:s');
    $hoy = Carbon::now()->format('Y-m-d H:i:s');
    $user = Auth::user();
    if (! $user instanceof User) {
      abort(403);
    }

    $status = TicketStatus::closed();

    $services = TicketQueryByRole::queryWithListRelations()
      ->whereIn('services.status', $status)
      ->whereBetween('services.created_at', [$inicio, $hoy]);
    TicketQueryByRole::applyUserVisibilityScope($services, $user);

    return Datatables::of($services)
      ->editColumn('created_at', function ($services) {
        return $services->created_at ? Carbon::parse($services->created_at)->format('Y-m-d H:i:s') : null;
      })
      ->make(true);
  }

  public function get_historial_services(Request $request)
  {
    $service = Service::findOrFail($request->id);
    Gate::authorize('view', $service);

    $historic = HistoricalServices::getTablehistorical($request->id);
    return Datatables::of($historic)
      ->make(true);
  }

  public function relanzarServicio(Request $request)
  {
    $request->validate([
      'id' => 'required|integer',
      'comentario' => 'required|string',
    ]);

    $servicio = Service::findOrFail($request->id);
    Gate::authorize('update', $servicio);

    $servicio->status = TicketStatus::PENDIENTE;
    $servicio->comentario_cliente = $request->comentario;
    $servicio->fecha_fin = null;
    $servicio->fecha_seguimiento = null;
    $servicio->fecha_relanzar = Carbon::now();
    $servicio->save();

    $historico = new HistoricalServices;
    $historico->service_id = $servicio->id;
    $historico->user_id = $servicio->user_id;
    $historico->failure_id = $servicio->failure_id;
    $historico->sede_id = $servicio->sede_id;
    $historico->status = TicketStatus::PENDIENTE;
    $historico->fecha_seguimiento = null;
    $historico->comentario_cliente = $servicio->comentario_cliente;
    $historico->responsable_id = $servicio->responsable_id;
    $historico->solution = $servicio->solution;
    $historico->description = $servicio->description;
    $historico->observations = $servicio->observations;
    $historico->fecha_relanzar = $servicio->fecha_relanzar;;
    $historico->save();

    return response()->json(['message' => 'Servicio relanzado con éxito']);
  }
}
