<?php

namespace App\Http\Controllers\Admin;

use App\Models\Area;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Incident;
use App\Models\User;
use App\Models\AssetUser;
use App\Models\CtgContenido;
use App\Models\CtgSubcategoria;
use App\Http\Requests\UpdateIncident;
use App\Models\IncidenciaSeguridadDato;
use App\Notifications\IncidenciaSeguridadCreada;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;

class IncidentsController extends Controller
{
  public function __construct()
  {
    $this->middleware(['permission:create incident'], ['only' => ['create', 'store']]);
    $this->middleware(['permission:read incidents'], ['only' => ['index']]);
    $this->middleware(['permission:update incident'], ['only' => ['edit', 'update']]);
  }

  public function index()
  {
    $wordlist = User::where('certification', '0')->get();
    $wordCount = $wordlist->count();
    $assets = AssetUser::select('user_id')->distinct()->get();
    $users = User::select('id')->where('certification', '1')->whereNotIn('id', $assets)->count();
    $incidents = Incident::get();
    return view('admin.incidents.index', compact('incidents'))->with('count', $wordCount)->with('countChecks', $users);
  }

  public function create()
  {
    $sistemas = CtgContenido::where('ctg_id', 2)->get();
    $areas = Area::select('id', 'name')->orderBy('name')->get();
    return view('admin.incidents.create', compact('sistemas', 'areas'));
  }

  public function incidentsEvents()
  {
    $sistemas = CtgContenido::where('ctg_id', 2)->get();
    $areas = Area::select('id', 'name')->orderBy('name')->get();
    return view('admin.incidents.createEvent', compact('sistemas', 'areas'));
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'disqualification_date' => 'required|date',
      'sistema' => 'required',
      'tipo' => 'required',
      'causa' => 'required',
      'responsable' => 'nullable',
      'acciones' => 'nullable',
      'observations' => 'nullable',
      'enablement_date' => 'nullable|date|after:disqualification_date',
      'criticidad' => 'nullable',
      'notas' => 'nullable',
      'lecciones' => 'nullable'
    ], [
      'enablement_date.after' => 'La fecha de habilitación debe ser posterior a la fecha de descalificación.',
      'disqualification_date.required' => 'La fecha de inhabilitación es obligatoria.',
      'causa.required' => 'La causa es obligatoria.',
      'enablement_date.date' => 'La fecha de habilitación debe ser una fecha válida.',
    ]);

    // Convertir las fechas a instancias de Carbon para facilitar la comparación
    $disqualificationDate = new Carbon($request->disqualification_date);
    $enablementDate = $request->enablement_date ? new Carbon($request->enablement_date) : null;

    // Validar que enablement_date no sea antes de disqualification_date
    if ($enablementDate && $enablementDate <= $disqualificationDate) {
      return back()->withFlash(['La fecha de habilitación debe ser posterior a la fecha de descalificación.']);
    }

    // Asignar las fechas al arreglo de datos
    $data['disqualification_date'] = $disqualificationDate->toDateTimeString();
    if ($enablementDate) {
      $data['enablement_date'] = $enablementDate->toDateTimeString();
    }

    // Asignar el ID del usuario autenticado
    $data['user_id'] = auth()->id();

    try {
      // Crear el incidente
      Incident::create($data);
    } catch (\Throwable $th) {
      return back()->withErrors('Error al guardar. Intentalo de nuevo.');
    }

    return redirect()->route('admin.incidents.index')->withFlash('Guardado con éxito.');
  }


  public function edit(Incident $incident)
  {
    return view('admin.incidents.edit', compact('incident'));
  }

  public function update(UpdateIncident $request, Incident $incident)
  {
    $incident->update($request->validated());

    return redirect()->route('admin.incidents.index')->withFlash('Incidencia actualizada');
  }

  public function ciberseguridad()
  {
    $wordlist = User::where('certification', '0')->get();
    $wordCount = $wordlist->count();
    $assets = AssetUser::select('user_id')->distinct()->get();
    $users = User::select('id')->where('certification', '1')->whereNotIn('id', $assets)->count();
    $datos = IncidenciaSeguridadDato::get();
    return view('admin.incidents.index_seguridad', compact('datos'))->with('count', $wordCount)->with('countChecks', $users);
  }

  public function createSeguridad()
  {
    $categorias = CtgContenido::where('ctg_id', 4)->get();
    $subcategorias = CtgSubcategoria::orderBy('id')->get();
    return view('admin.incidents.createSeguridad', compact('categorias'));
  }

  public function obtenerSubcategoria($ctg_contenido_id)
  {
    $subcategorias = CtgSubcategoria::where('ctg_contenido_id', $ctg_contenido_id)->get();
    return response()->json($subcategorias);
  }

  public function saveSeguridad(Request $request)
  {
    $create = new IncidenciaSeguridadDato();
    $create->user_id = Auth::user()->id;
    $create->ctg_contenido_id = $request->ctg_contenido_id;
    $create->ctg_subcategoria_id = $request->ctg_subcategoria_id;
    $create->fecha_incidencia = $request->fecha_incidencia;
    $create->lugar_incidencia = $request->lugar_incidencia;
    $create->comentario = $request->comentario;

    try {
      $create->save();

      try {

        Notification::route('mail', 'desarrollo@ecd.mx')
          ->notify(new IncidenciaSeguridadCreada($create));

        $flashMessage = 'Reporte guardado y correos enviados.';
      } catch (\Exception $e) {
        $flashMessage = 'Reporte guardado, pero error al enviar correo: ' . $e->getMessage();
      }
    } catch (\Throwable $th) {
      $flashMessage = 'Error al guardar reporte: ' . $th->getMessage();
    }

    return redirect()->route('ciberseguridad.incidencias')->with('flash', $flashMessage);
  }
}
