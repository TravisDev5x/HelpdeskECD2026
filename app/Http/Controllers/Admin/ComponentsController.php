<?php

namespace App\Http\Controllers\Admin;

use App\Models\Company;
use App\Models\Componente;
use App\Models\CtgContenido;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Composer;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ComponentsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:read products'], ['only' => ['index', 'create', 'edit']]);
        $this->middleware(['permission:create product'], ['only' => ['store']]);
        $this->middleware(['permission:update product'], ['only' => ['update', 'restore']]);
        $this->middleware(['permission:delete product'], ['only' => ['destroy']]);
    }

    public function index()
    {
        $componentes = Componente::withTrashed()->get();

        return view('admin.componentes.index', compact('componentes'));
    }

    public function create()
    {
        $componenteCtgs = CtgContenido::where('ctg_id', '3')->where('area', 'Sistemas')->get();
        $companies = Company::select('id', 'name')->orderBy('name')->get();
        $equipos = Product::where('owner', 'Sistemas')
            ->whereNotIn('etiqueta', ['N/A', ''])
            ->where(function ($query) {
                $query->where('etiqueta', 'LIKE', '%-PC-%')
                    ->orWhere('etiqueta', 'LIKE', '%-LAP-%')
                    ->orWhere('etiqueta', 'LIKE', '%-MAC-%');
            })
            ->get();
        return view('admin.componentes.create', compact('componenteCtgs', 'companies', 'equipos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'producto_id' => 'required|integer|exists:products,id', // Asegúrate de que el ID del producto exista en la tabla 'equipos'
            'serie' => 'required',
            'marca' => 'required',
            'modelo' => 'required',
            'capacidad' => 'nullable',
            'fecha_ingreso' => 'required|date',
            'company_id' => 'required|exists:companies,id', // Asegúrate de que el ID de la empresa exista en la tabla 'companies'
            'status' => 'required',
            'costo' => 'required|numeric',
            'observacion' => 'nullable',
        ]);


        Componente::create([
            'producto_id' => $request->input('producto_id'),
            'name' => $request->input('name'),
            'serie' => $request->input('serie'),
            'marca' => $request->input('marca'),
            'modelo' => $request->input('modelo'),
            'capacidad' => $request->input('capacidad'),
            'observacion' => $request->input('observacion'),
            'costo' => $request->input('costo'),
            'status' => $request->input('status'),
            'fecha_ingreso' => $request->input('fecha_ingreso'),
            'owner' => 'Sistemas',
            'company_id' => $request->input('company_id'),
            'user_id' => Auth::user()->id,
        ]);

        return redirect()->route('admin.components.index')->with('success', 'Componente creado con éxito');
    }

    public function edit($id)
    {
        $componente = Componente::find($id);
        $componenteCtgs = CtgContenido::where('ctg_id', '3')->where('area', 'Sistemas')->get();
        $companies = Company::select('id', 'name')->orderBy('name')->get();
        $equipos = Product::where('owner', 'Sistemas')
            ->whereNotIn('etiqueta', ['N/A', ''])
            ->where(function ($query) {
                $query->where('etiqueta', 'LIKE', '%-PC-%')
                    ->orWhere('etiqueta', 'LIKE', '%-LAP-%')
                    ->orWhere('etiqueta', 'LIKE', '%-MAC-%');
            })
            ->get();
        return view('admin.componentes.edit', compact('componente', 'componenteCtgs', 'companies', 'equipos'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required',
            'producto_id' => 'required|integer|exists:products,id',
            'serie' => 'required',
            'marca' => 'required',
            'modelo' => 'required',
            'capacidad' => 'nullable',
            'fecha_ingreso' => 'required|date',
            'company_id' => 'required|exists:companies,id',
            'status' => 'required',
            'costo' => 'required|numeric',
            'observacion' => 'nullable',
        ]);

        $componente = Componente::findOrFail($id);

        $componente->update([
            'producto_id' => $request->input('producto_id'),
            'name' => $request->input('name'),
            'serie' => $request->input('serie'),
            'marca' => $request->input('marca'),
            'modelo' => $request->input('modelo'),
            'capacidad' => $request->input('capacidad'),
            'observacion' => $request->input('observacion'),
            'costo' => $request->input('costo'),
            'status' => $request->input('status'),
            'fecha_ingreso' => $request->input('fecha_ingreso'),
            'owner' => 'Sistemas',
            'company_id' => $request->input('company_id'),
            'employee_id' => Auth::user()->id,
        ]);

        return redirect()->route('admin.components.index')->with('success', 'Componente actualizado con éxito');
    }


    public function destroy(Componente $componente)
    {
        $componente->delete();

        return back()->withFlash('Componente Suspendido');
    }

    public function restore($id)
    {
        $componente = Componente::withTrashed()->findOrFail($id);
        $componente->restore();

        return back()->withFlash('Componente Activado');
    }
}
