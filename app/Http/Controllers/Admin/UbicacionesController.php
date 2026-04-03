<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sede;
use App\Models\Ubicacion;
use Illuminate\Http\Request;

class UbicacionesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:modulo.ubicaciones']);
    }

    public function index()
    {
        $ubicaciones = Ubicacion::withTrashed()->get();

        return view('admin.ubicaciones.index', compact('ubicaciones'));
    }

    public function create()
    {
        $sedes = Sede::select('id', 'sede')->orderBy('sede')->get();
        return view('admin.ubicaciones.create', compact('sedes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ubicacion' => 'required',
        ]);
        
        $newUbicacion = Ubicacion::create();
        $newUbicacion->ubicacion =  $request->ubicacion;
        $newUbicacion->id_sede =  $request->sede;
        $newUbicacion->save();

        return redirect()->route('admin.ubicaciones.index');
    }

    public function edit(Ubicacion $ubicacion)
    {
        $sedes = Sede::select('id', 'sede')->orderBy('sede')->get();
        return view('admin.ubicaciones.edit', compact('ubicacion', 'sedes'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'ubicacion' => 'required',
        ]);

        $updateUbicacion = Ubicacion::find($request->ubicacionId);
        $updateUbicacion->ubicacion = $request->ubicacion;
        $updateUbicacion->id_sede = $request->sede;

        $updateUbicacion->update();

        return redirect()->route('admin.ubicaciones.index')->withFlash('Ubicacion actualizada');
    }

    public function destroy(Ubicacion $ubicacion)
    {
        $ubicacion->delete();
        return back()->withFlash('Ubicacion Suspendida');
    }

    public function restore($id)
    {
        $ubicacion = Ubicacion::withTrashed()->findOrFail($id);
        $ubicacion->restore();

        return back()->withFlash('Ubicacion Activada');
    }
}
