<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateDepartment;
use App\Models\Sede;
use Illuminate\Http\Request;

class SedesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:modulo.sedes']);
    }

    public function index()
    {
        return view('admin.sedes.index');
    }

    public function create()
    {
        return view('admin.sedes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'sede' => 'required',
        ]);
        
        $newSede = Sede::create();
        $newSede->sede =  $request->sede;
        $newSede->save();

        return redirect()->route('admin.sedes.index');
    }

    public function edit(Sede $sede)
    {
        return view('admin.sedes.edit', compact('sede'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'sede' => 'required',
        ]);

        $updateSede = Sede::find($request->sedeId);
        $updateSede->sede = $request->sede;

        $updateSede->update();

        return redirect()->route('admin.sedes.index')->withFlash('Sede actualizada');
    }

    public function destroy(Sede $sede)
    {
        $sede->delete();
        return back()->withFlash('Sede Suspendida');
    }

    public function restore($id)
    {
        $sede = Sede::withTrashed()->findOrFail($id);
        $sede->restore();

        return back()->withFlash('Sede Activada');
    }
}
