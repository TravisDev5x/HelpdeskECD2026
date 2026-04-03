<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CtgContenido;
use Illuminate\Support\Facades\Auth;
use App\Support\Authorization\UserPrimaryRole;

class ContenidoCtgsController extends Controller
{
  public function __construct()
  {
    $this->middleware(['permission:read incidents'], ['only' => [
      'index', 'create', 'edit',
    ]]);
    $this->middleware(['permission:create incident'], ['only' => [
      'store', 'update', 'destroy', 'restore',
    ]]);
    $this->middleware(['permission:read products'], ['only' => [
      'indexProducto', 'createProducto', 'editProducto',
    ]]);
    $this->middleware(['permission:create product'], ['only' => [
      'storeProducto', 'updateProducto', 'destroyProducto', 'restoreProducto',
    ]]);
  }

  public function index()
  {
    $sistemas = CtgContenido::withTrashed()->where('ctg_id', 2)->get();
    return view('admin.contenidoCtgs.incidencias.index', compact('sistemas'));
  }

  public function create()
  {
    return view('admin.contenidoCtgs.incidencias.create');
  }

  public function store(Request $request)
  {
    $request->validate([
      'sistema' => 'required',
    ]);

    $newSistema = new CtgContenido();
    $newSistema->contenido = $request->sistema;
    $newSistema->ctg_id = 2;
    $newSistema->save();

    return redirect()->route('admin.contenido.ctg.incidencia.index')->withFlash('Guardado con exito.');
  }

  public function edit(CtgContenido $sistema)
  {

    $sistema = CtgContenido::find($sistema->id);

    return view('admin.contenidoCtgs.incidencias.edit', compact('sistema'));
  }

  public function update(Request $request)
  {
    $request->validate([
      'sistema' => 'required',
    ]);

    $updateSistema = CtgContenido::find($request->sistema_id);
    $updateSistema->contenido = $request->sistema;
    $updateSistema->update();

    $updateSistema = CtgContenido::get();
    return redirect()->route('admin.contenido.ctg.incidencia.index')->withFlash('Sistema actualizado');
  }

  public function destroy(CtgContenido $sistema)
  {
    $sistema->delete();
    return back()->withFlash('Sistema Suspendido');
  }

  public function restore($id)
  {
    $company = CtgContenido::withTrashed()->findOrFail($id);
    $company->restore();

    return back()->withFlash('Sistema Activado');
  }

  public function indexProducto()
  {
    if (UserPrimaryRole::name() === 'Mantenimiento') {
      $sistemas = CtgContenido::withTrashed()->where('ctg_id', 1)->where('area', 'Mantenimiento')->get();
    } else {
      $sistemas = CtgContenido::withTrashed()->whereIn('ctg_id', [1, 3])->where('area', 'Sistemas')->get();
    }
    return view('admin.contenidoCtgs.productos.index', compact('sistemas'));
  }

  public function createProducto()
  {
    return view('admin.contenidoCtgs.productos.create');
  }

  public function storeProducto(Request $request)
  {
    $request->validate([
      'producto' => 'required',
      'tipo' => 'required|integer|not_in:0',
    ]);

    $newSistema = new CtgContenido();
    $newSistema->contenido = $request->producto;
    $newSistema->ctg_id = $request->tipo;
    if (UserPrimaryRole::name() === 'Mantenimiento') {
      $newSistema->area = 'Mantenimiento';
    } else {
      $newSistema->area = 'Sistemas';
    }
    $newSistema->save();

    return redirect()->route('admin.contenido.ctg.productos.index')->withFlash('Guardado con exito.');
  }

  public function editProducto(CtgContenido $sistema)
  {

    $sistema = CtgContenido::find($sistema->id);

    return view('admin.contenidoCtgs.productos.edit', compact('sistema'));
  }

  public function updateProducto(Request $request)
  {
    $request->validate([
      'producto' => 'required',
      'tipo' => 'required|integer|not_in:0',
    ]);

    $updateSistema = CtgContenido::find($request->sistema_id);
    $updateSistema->contenido = $request->producto;
    $updateSistema->ctg_id = $request->tipo;
    $updateSistema->update();

    $updateSistema = CtgContenido::get();
    return redirect()->route('admin.contenido.ctg.productos.index')->withFlash('Producto actualizado');
  }

  public function destroyProducto(CtgContenido $sistema)
  {
    $sistema->delete();
    return back()->withFlash('Sistema Suspendido');
  }

  public function restoreProducto($id)
  {
    $company = CtgContenido::withTrashed()->findOrFail($id);
    $company->restore();

    return back()->withFlash('Sistema Activado');
  }
}
