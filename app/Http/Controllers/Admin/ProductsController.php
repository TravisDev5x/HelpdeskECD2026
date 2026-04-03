<?php

namespace App\Http\Controllers\Admin;

use App\Models\Assignment;
use App\Models\Company;
use App\Models\CtgContenido;
use App\Exports\InvetarioCompletoExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProduct;
use App\Models\Product;
use App\Models\Sede;
use App\Models\Ubicacion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\Authorization\UserPrimaryRole;
use App\Support\Inventory\ProductOwnerCatalog;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\Datatables\Datatables;

class ProductsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:read products'], ['only' => [
            'index', 'getProducts', 'getUbicacionesPorSede', 'show', 'revision_auditor', 'revision_observacion',
        ]]);
        $this->middleware(['permission:create product'], ['only' => ['create', 'store']]);
        $this->middleware(['permission:update product'], ['only' => ['edit', 'update']]);
        $this->middleware(['permission:delete product'], ['only' => ['destroy']]);
        $this->middleware(['permission:descarga_productosall'], ['only' => ['download_inventariocompleto']]);
        $this->middleware(['permission:read assignmentsIndividual'], ['only' => ['aceptaProducto']]);
    }

    public function index()
    {
        $empresas = Company::select('id', 'name')->orderBy('name')->get();
        // Usamos el rol directamente para decidir la vista, lógica simplificada
        $view = (UserPrimaryRole::name() === 'Control')
            ? 'admin.products.index2'
            : 'admin.products.index';

        return view($view, compact('empresas'));
    }

    public function getProducts(Request $request)
    {
        $roleName = UserPrimaryRole::name() ?? '';

        // 2. Consulta: eager load acotado (menos columnas por fila en JSON / menos RAM en DataTables).
        $query = Product::query()
            ->select('products.*')
            ->with([
                'company' => static function ($q) {
                    $q->select('id', 'name', 'deleted_at');
                },
                'employee' => static function ($q) {
                    $q->select('id', 'name', 'ap_paterno', 'ap_materno', 'campaign_id', 'deleted_at');
                },
                'employee.campaign' => static function ($q) {
                    $q->select('id', 'name', 'deleted_at');
                },
            ]);

        // 3. Filtros por rol (capa compartida con reportes/export)
        ProductOwnerCatalog::applyProductsListOwnerFilter($query, $roleName);

        // 4. RETORNO CORREGIDO: Usamos la función minúscula datatables()
        // Esto instancia la clase correctamente en lugar de llamarla estáticamente.
        return datatables()->eloquent($query)
            
            ->addColumn('company.name', function (Product $product) {
                return $product->company ? $product->company->name : '';
            })
            ->addColumn('employee.name', function (Product $product) {
                return $product->employee ? $product->employee->name : '';
            })
            ->filterColumn('company.name', function ($query, $keyword) {
                $normalized = preg_replace('/^\^|\$$/', '', (string) $keyword);
                $normalized = str_replace('\\', '', $normalized);
                $query->whereHas('company', function ($companyQuery) use ($normalized) {
                    $companyQuery->where('name', 'like', "%{$normalized}%");
                });
            })
            ->editColumn('fecha_ingreso', function (Product $product) {
                return $product->fecha_ingreso ? $product->fecha_ingreso->format('d/m/Y') : '';
            })
            
            // Ordenamiento manual para evitar conflictos SQL
            ->orderColumn('company.name', function ($query, $order) {
                $query->leftJoin('companies', 'companies.id', '=', 'products.company_id')
                      ->orderBy('companies.name', $order)
                      ->select('products.*');
            })
            ->orderColumn('employee.name', function ($query, $order) {
                $query->leftJoin('users as employees', 'employees.id', '=', 'products.employee_id')
                      ->orderBy('employees.name', $order)
                      ->select('products.*');
            })

            ->rawColumns(['status', 'actions'])
            ->make(true);
    }

    public function create()
    {
        $sedes       = Sede::select('id', 'sede')->orderBy('sede')->get();
        $ubicaciones = Ubicacion::select('id', 'ubicacion', 'id_sede')->orderBy('ubicacion')->get();
        $companies   = Company::pluck('name', 'id');
        
        // Reutilizamos lógica extraída
        $productos   = $this->getContenidoPorRol(UserPrimaryRole::name() ?? '');

        return view('admin.products.create', compact('companies', 'productos', 'sedes', 'ubicaciones'));
    }

    public function getUbicacionesPorSede($sedeId)
    {
        $ubicaciones = Ubicacion::where('id_sede', $sedeId)->get();
        
        // Retornar array vacío es mejor que 404 para selects dependientes, 
        // pero mantengo tu lógica de 404 si el front lo espera así.
        return $ubicaciones->isEmpty() 
            ? response()->json([], 404) 
            : response()->json($ubicaciones);
    }

    public function store(Request $request)
    {
        // Validación limpia
        $data = $request->validate([
            'company_id'    => 'required',
            'serie'         => 'required|max:255',
            'name'          => 'required|max:255',
            'etiqueta'      => 'required|max:255',
            'marca'         => 'nullable|max:255',
            'modelo'        => 'nullable|max:255',
            'medio'         => 'nullable|max:255',
            'ip'            => 'nullable|max:255',
            'mac'           => 'nullable|max:255',
            'fecha_ingreso' => 'nullable|date',
            'status'        => 'required',
            'costo'         => 'nullable|numeric',
            'observacion'   => 'nullable',
            'sede_id'       => 'required',
            'ubicacion_id'  => 'required',
        ]);

        $user   = Auth::user();
        $roleId = $user->roles->first()->id;

        $data['user_id'] = $user->id;

        // Lógica de asignación de Owner basada en ID de Rol
        // SUGERENCIA: Cambiar estos IDs fijos (13, 16) por nombres de roles en el futuro.
        if ($roleId == 13) {
            $data['product_maintenance'] = 1;
            $data['owner'] = 'Mantenimiento';
        } elseif ($roleId == 16) {
            $data['owner'] = 'Operaciones';
        }

        Product::create($data);

        return redirect()
            ->route('admin.products.index')
            ->withFlash('Producto guardado');
    }

    public function edit(Product $product)
    {
        $sedes     = Sede::select('id', 'sede')->orderBy('sede')->get();
        $companies = Company::select('id', 'name')->orderBy('name')->get();
        
        // Reutilizamos lógica extraída
        $productos = $this->getContenidoPorRol(UserPrimaryRole::name() ?? '');

        return view('admin.products.edit', compact('product', 'companies', 'productos', 'sedes'));
    }

    public function update(UpdateProduct $request, Product $product)
    {
        $product->update($request->validated());
        return back()->withFlash('Producto actualizado');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->withFlash('Producto eliminado');
    }

    public function aceptaProducto(Request $request)
    {
        try {
            $producto = Product::findOrFail($request->id);
            $producto->update(['acepted_at' => Carbon::now()]);
            
            return 'Se ha actualizado el equipo.';
        } catch (\Throwable $th) {
            // Loguear el error sería ideal aquí: Log::error($th);
            return 'Error al aceptar el equipo.';
        }
    }

    public function revision_auditor(Request $request)
    {
        $assignment = Product::findOrFail($request->id);

        if ($request->serie == $assignment->serie) {
            $assignment->update(['revision' => 1]);
            // Es buena práctica retornar algo tras el update, aunque sea un 200 OK
            return response()->json(['status' => 'success']);
        }
        
        return $request->id;
    }

    public function revision_observacion(Request $request)
    {
        $assignment = Product::findOrFail($request->id);
        
        // Asumiendo que 'review_observations' está en $fillable
        $assignment->update(['review_observations' => $request->observations]);

        return back();
    }

    public function download_inventariocompleto()
    {
        return Excel::download(new InvetarioCompletoExport(), 'Inventario_completo.xlsx');
    }

    /**
     * Refactorizado: Se eliminó el parámetro $id ya que $product ya viene inyectado.
     * Se usa $product->id para la consulta.
     */
    public function show(Product $product)
    {
        $historico = Assignment::where('product_id', $product->id)
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.products.show', compact('historico'));
    }

    public function unassign(Request $request)
    {
        $request->validate(['id' => 'required|exists:products,id']);

        $product = Product::findOrFail($request->id);
        $product->update([
            'employee_id' => null,
            'status' => 'OPERATIVO',
            'date_assignment' => null,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Equipo liberado.']);
    }

    // --- Métodos Privados Auxiliares ---

    /**
     * Centraliza la lógica para obtener CtgContenido basado en el rol.
     * Evita duplicar código en create() y edit().
     */
    private function getContenidoPorRol(string $roleName)
    {
        return CtgContenido::where('ctg_id', '1')
            ->where('area', ProductOwnerCatalog::ctgContenidoAreaForRole($roleName))
            ->get();
    }
}