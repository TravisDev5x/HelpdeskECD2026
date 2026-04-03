<?php

namespace App\Http\Controllers\Admin;

use App\Models\Assignment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use CArbon\Carbon;
use App\Models\Product;
use App\Models\User;
use App\Models\DescuentoEstado;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Support\Authorization\UserPrimaryRole;
use Illuminate\Support\Str;
use App\Notifications\AssignamentCreated;
use Illuminate\Support\Facades\Notification;


class AssignmentsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:read assignments'], ['only' => [
            'index', 'getAssignments', 'list', 'list2', 'getListAssignments', 'getListAssignments2',
            'store', 'show', 'edit', 'getDesAssignments', 'log', 'getLogAssignments',
            'masiva', 'revisionIndex', 'revisionShow', 'revisionproduct', 'get_revisionproducto',
        ]]);
        $this->middleware(['permission:remove assignments'], ['only' => ['removeAssignments']]);
        $this->middleware(['permission:delete assignment'], ['only' => ['destroy', 'destroyMasiva']]);
    }

    public function index()
    {
        $wordCount = $this->countPendingCertificationUsers();
        $users = $this->countCertifiedUsersWithoutAsset();

        $userSelect = User::query()
            ->select('id', 'name', 'ap_paterno', 'ap_materno', 'usuario')
            ->orderBy('name')
            ->get();

        return view('admin.assignments.index')->with('count', $wordCount)->with('countChecks', $users)->with('userSelect', $userSelect);
    }

    public function getAssignments(Request $request)
    {
        $withCompany = static function ($q) {
            $q->select('id', 'name', 'deleted_at');
        };

        if (UserPrimaryRole::name() === 'Mantenimiento') {
            $products = Product::query()
                ->with(['company' => $withCompany])
                ->whereNull('employee_id')
                ->whereStatus('OPERABLE')
                ->where('owner', 'Mantenimiento');
        } else {
            $products = Product::query()
                ->with(['company' => $withCompany])
                ->whereNull('employee_id')
                ->whereStatus('OPERABLE')
                ->where('owner', 'Sistemas');
        }

        return Datatables::of($products)
            ->make(true);
    }

    public function list()
    {
        return view('admin.assignments.list');
    }

    public function list2()
    {
        return view('admin.assignments.list2')->with('count', $this->countPendingCertificationUsers())->with('countChecks', $this->countCertifiedUsersWithoutAsset());
    }

    public function getListAssignments(Request $request)
    {
        $user = Auth::user();
        $d = Auth::user()->roles->pluck('name');
        $nombreExpr = "CONCAT(
            COALESCE(MAX(users.name), ''),
            ' ',
            COALESCE(MAX(users.ap_paterno), ''),
            ' ',
            COALESCE(MAX(users.ap_materno), '')
        ) as nombre_emple";

        if ($d[0] == 'Admin' || $d[0] == 'Soporte') {
            $products = Product::query()
                ->select('products.employee_id')
                ->selectRaw($nombreExpr)
                ->selectRaw('MAX(users.usuario) as usuario')
                ->selectRaw('MAX(departments.name) as dept_name')
                ->selectRaw('COUNT(*) as cantidad')
                ->leftJoin('users', 'users.id', '=', 'products.employee_id')
                ->leftJoin('departments', 'departments.id', '=', 'users.department_id')
                ->whereNotNull('products.employee_id')
                ->groupBy('products.employee_id');
        } elseif ($d[0] == 'Mantenimiento') {
            $products = Product::query()
                ->select('products.employee_id')
                ->selectRaw($nombreExpr)
                ->selectRaw('MAX(users.usuario) as usuario')
                ->selectRaw('MAX(departments.name) as dept_name')
                ->selectRaw('MAX(products.ubicacion_id) as ubicacion_id')
                ->selectRaw('COUNT(*) as cantidad')
                ->leftJoin('users', 'users.id', '=', 'products.employee_id')
                ->leftJoin('departments', 'departments.id', '=', 'users.department_id')
                ->whereNotNull('products.employee_id')
                ->where('products.owner', 'Mantenimiento')
                ->groupBy('products.employee_id');
        } elseif ($user->position_id == 9 || $user->position_id == 10) {
            $products = Product::query()
                ->select('products.employee_id')
                ->selectRaw($nombreExpr)
                ->selectRaw('MAX(users.usuario) as usuario')
                ->selectRaw('MAX(departments.name) as dept_name')
                ->selectRaw('COUNT(*) as cantidad')
                ->leftJoin('users', 'users.id', '=', 'products.employee_id')
                ->leftJoin('departments', 'departments.id', '=', 'users.department_id')
                ->whereNotNull('products.employee_id')
                ->groupBy('products.employee_id');
        } else {
            $products = Product::query()
                ->select('products.employee_id')
                ->selectRaw($nombreExpr)
                ->selectRaw('MAX(users.usuario) as usuario')
                ->selectRaw('MAX(departments.name) as dept_name')
                ->selectRaw('COUNT(*) as cantidad')
                ->leftJoin('users', 'users.id', '=', 'products.employee_id')
                ->leftJoin('departments', 'departments.id', '=', 'users.department_id')
                ->whereNotNull('products.employee_id')
                ->where('products.employee_id', auth()->id())
                ->groupBy('products.employee_id');
        }

        return Datatables::of($products)
            ->filterColumn('nombre_emple', function ($query, $keyword) {
                $sql = "CONCAT(COALESCE(users.name, ''), ' ', COALESCE(users.ap_paterno, ''), ' ', COALESCE(users.ap_materno, ''))  like ?";
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })
            ->make(true);
    }


    public function getListAssignments2(Request $request)
    {
        $nombreExpr = "CONCAT(
            COALESCE(MAX(users.name), ''),
            ' ',
            COALESCE(MAX(users.ap_paterno), ''),
            ' ',
            COALESCE(MAX(users.ap_materno), '')
        ) as nombre_emple";

        $products = Product::query()
            ->select('products.employee_id', 'products.name as prod_name')
            ->selectRaw($nombreExpr)
            ->selectRaw('MAX(departments.name) as dept_name')
            ->selectRaw('COUNT(*) as cantidad')
            ->leftJoin('users', 'users.id', '=', 'products.employee_id')
            ->leftJoin('departments', 'departments.id', '=', 'users.department_id')
            ->whereNotNull('products.employee_id')
            ->groupBy('products.employee_id', 'products.name');

        return Datatables::of($products)
            ->filterColumn('nombre_emple', function ($query, $keyword) {
                $sql = "CONCAT(COALESCE(users.name, ''), ' ', COALESCE(users.ap_paterno, ''), ' ', COALESCE(users.ap_materno, ''))  like ?";
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })
            ->make(true);
    }

    public function store(Request $request)
    {
        $response = '';
        $data = $request->validate([
            'id' => 'required',
            'employee_id' => [
                'required',
                'numeric'
            ],
            'responsiva' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'estado_equipo' => 'required',
            'costo_estado' => 'required',
            'observations' => 'required',
        ]);

        $data['date_assignment'] = new Carbon;

        $data['ubicacion'] = $request->ubicacion;

        $product = Product::whereId($request->id)->first();

        $assignment = new Assignment;

        try {
            $path = $request->file('responsiva');

            $extension = $path->getClientOriginalExtension();
            $fileName = uniqid() . '_' . Str::slug(pathinfo($path->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $extension;

            $path->storeAs('helpdesk/responsivas', $fileName, 'storage_celer2');
        } catch (Exception $e) {

            $response = 'Error al guardar en storage' . $e;
        }

        try {

            $assignment->create([
                'user_id' => auth()->id(),
                'employee_id' => $data['employee_id'],
                'product_id' => $product->id,
                'assignment' => 'Asignación',
                'observations' => $data['observations'],
                'estado_id' => $data['estado_equipo'],
                'costo_estado' => $data['costo_estado'],
                'responsiva' => $fileName,
            ]);
            $response .= 'Asignación exitosa. ';
        } catch (\Throwable $th) {
            //throw $th;
        }

        $user = User::findOrFail($request->employee_id);
        try {
            Notification::send($user, new AssignamentCreated($product, $user));
            $response .= 'Se ha enviado una notificación al usuario ' . $user->name;
        } catch (Exception $th) {
            $response .= 'No se pudo enviar notificación por correo.';
        }

        try {
            $product->update([
                'employee_id' => $data['employee_id'],
                'date_assignment' => $data['date_assignment'],
                'ubicacion_id' => $data['ubicacion'],
            ]);
        } catch (\Throwable $th) {
            $response .= 'No se realizo la asignación correctamente';
        }

        return redirect()->route('admin.products.index')->withFlash($response);
    }

    public function show($user, $name = null)
    {
        $user = User::withTrashed()->find($user);
        $logeado = Auth::user();

        $equipoAsignado = Product::where('employee_id', $logeado->id)->pluck('id');

        if (UserPrimaryRole::name() === 'Mantenimiento') {
            $assignments = Product::where('employee_id', $user->id)
                ->where('owner', 'Mantenimiento')
                ->orderBy('date_assignment', 'asc')
                ->get();
        } else {
            if (is_null($name)) {
                $assignments = Product::where('employee_id', $user->id)
                    ->orderBy('date_assignment', 'asc')
                    ->get();
            } else {
                $assignments = Product::where('employee_id', $user->id)
                    ->where('name', $name)
                    ->orderBy('date_assignment', 'asc')
                    ->get();
            }
        }

        return view('admin.assignments.show', compact('assignments', 'user', 'equipoAsignado'));
    }

    public function edit(Product $assignment)
    {
        return view('admin.assignments.create', [
            'product' => $assignment,
            'users' => User::select('name', 'ap_paterno', 'ap_materno', 'usuario', 'id')->get(),
            'estadoEquipos' => DescuentoEstado::get(),
        ]);
    }

    public function removeAssignments()
    {
        return view('admin.assignments.remove')
            ->with('count', $this->countPendingCertificationUsers())
            ->with('countChecks', $this->countCertifiedUsersWithoutAsset());
    }

    public function getDesAssignments(Request $request)
    {
        $withCompany = static function ($q) {
            $q->select('id', 'name', 'deleted_at');
        };
        $withEmployee = static function ($q) {
            $q->select('id', 'name', 'ap_paterno', 'ap_materno', 'usuario', 'department_id', 'deleted_at');
        };
        $withDepartment = static function ($q) {
            $q->select('id', 'name', 'deleted_at');
        };

        if (UserPrimaryRole::name() === 'Mantenimiento') {
            $products = Product::query()
                ->with([
                    'company' => $withCompany,
                    'employee' => $withEmployee,
                    'employee.department' => $withDepartment,
                ])
                ->whereNotNull('employee_id')
                ->where('owner', 'Mantenimiento');
        } else {
            $products = Product::query()
                ->with([
                    'company' => $withCompany,
                    'employee' => $withEmployee,
                ])
                ->whereNotNull('employee_id');
        }

        return Datatables::of($products)
            ->make(true);
    }

    public function destroy(Request $request, Product $assignment)
    {
        $userId = Auth::user()->id;
        $data = $request->validate([
            'observations' => 'required',
            'responsiva' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $fileName = null;

        if ($request->hasFile('responsiva')) {
            try {
                $file = $request->file('responsiva');
                $extension = $file->getClientOriginalExtension();
                $fileName = uniqid() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $extension;
                $file->storeAs('helpdesk/responsivas', $fileName, 'storage_celer2');
            } catch (Exception $e) {
                return redirect()->route('admin.products.index')
                    ->withFlash('Error al guardar en storage: ' . $e->getMessage());
            }
        }

        try {
            $desassignment = new Assignment;
            $desassignment->create([
                'user_id' => $userId,
                'employee_id' => $assignment->employee_id,
                'product_id' => $assignment->id,
                'assignment' => 'Desasignación',
                'observations' => $data['observations'],
                'responsiva' => $fileName,
            ]);

            $assignment->employee_id = null;
            $assignment->date_assignment = null;
            $assignment->acepted_at = null;
            $assignment->revision = 0;
            $assignment->ubicacion_id = null;

            $assignment->save();

            return redirect()->route('admin.products.index')->withFlash('Se realizó la desasignación correctamente');
        } catch (\Throwable $th) {
            return redirect()->route('admin.products.index')
                ->withFlash('Error al realizar la desasignación: ' . $th->getMessage());
        }
    }


    public function log()
    {
        return view('admin.assignments.log')
            ->with('count', $this->countPendingCertificationUsers())
            ->with('countChecks', $this->countCertifiedUsersWithoutAsset());
    }

    public function getLogAssignments(Request $request)
    {
        // No usar ->get() dentro de los closures de with(): rompe el eager loading y puede provocar 500.
        // user y employee son BelongsTo al mismo modelo/tabla (users): Yajra necesita alias distintos al ordenar/buscar.
        $query = Assignment::query()
            ->with([
                'user' => function ($q) {
                    $q->withTrashed()->select('id', 'name', 'ap_paterno', 'ap_materno', 'deleted_at');
                },
                'employee' => function ($q) {
                    $q->withTrashed()->select('id', 'name', 'ap_paterno', 'ap_materno', 'usuario', 'deleted_at');
                },
                'product' => function ($q) {
                    $q->select('id', 'name', 'serie', 'marca', 'modelo', 'deleted_at');
                },
            ]);

        return Datatables::of($query)
            ->enableEagerJoinAliases()
            ->make(true);
    }

    public function masiva(Request $request)
    {
        $response = '';

        try {
            $data = $request->validate([
                'id' => 'required',
                'userMasiva' => 'required'
            ]);
        } catch (\Throwable $th) {
            return back()->withErrors('Por favor, selecciona al menos un usuario y un equipo para asignación masiva');
        }
        $date_assignment = new Carbon;
        try {
            foreach ($request->id as $key => $value) {
                $product = Product::findOrFail($value);
                $assignment = new Assignment;
                try {
                    $assignment->create([
                        'user_id' => auth()->id(),
                        'employee_id' => $request->userMasiva,
                        'product_id' => $value,
                        'assignment' => 'Asignación',
                        'observations' => $request->observations,
                    ]);
                } catch (Exception $th) {
                    return back()->withErrors('Error al crear una asignación.' . $th);
                }
                try {
                    $product->update([
                        'user_id' => auth()->id(),
                        'employee_id' => $request->userMasiva,
                        'date_assignment' => $date_assignment
                    ]);
                } catch (\Throwable $th) {
                    return back()->withErrors('Error al actualizar info del producto.');
                }
                try {
                    $user = User::findOrFail($request->userMasiva);
                    Notification::send($user, new AssignamentCreated($product, $user));
                } catch (Exception $th) {
                    return back()->withErrors('No se pudo enviar notificación por correo.');
                }
            }
            $response .= 'Se ha realizado la asignación masiva exitosamente.';
        } catch (\Throwable $th) {
            return back()->withErrors('Error al crear asignación masiva.');
        }
        return redirect()->back()->withFlash($response);
    }

    public function destroyMasiva(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
            ]);
        } catch (\Throwable $th) {
            return back()->withErrors('Por favor, selecciona al menos un equipo para asignación masiva');
        }
        foreach ($request->id as $key => $value) {
            try {
                $date_assignment = new Carbon;
                $product = Product::findOrFail($value);
                $desassignment = new Assignment;
                $desassignment->create([
                    'user_id' => auth()->id(),
                    'employee_id' => $product->employee_id,
                    'product_id' => $product->id,
                    'assignment' => 'Desasignación',
                    'observations' => $request->observations,
                ]);
                $product->employee_id = null;
                $product->date_assignment = null;
                $product->acepted_at = null;
                $product->save();
            } catch (\Throwable $th) {
                return back()->withErrors('Error al desasignar el producto ' . $product->name);
            }
        }
        return back()->withFlash('Se realizo la desasignación correctamente');
    }

    public function revisionIndex()
    {
        return view('admin.assignments.revision_list')
            ->with('count', $this->countPendingCertificationUsers())
            ->with('countChecks', $this->countCertifiedUsersWithoutAsset());
    }

    public function revisionShow($user, $name)
    {
        $user = User::withTrashed()->find($user);
        $assignments = Product::where('employee_id', $user->id)->where('name', $name)
            ->orderBy('date_assignment', 'asc')
            ->get();

        return view(
            'admin.assignments.revision_show',
            compact('assignments', 'user')

        );
    }

    public function revisionproduct()
    {
        return view('admin.assignments.revision_product', compact('assignments'));
    }

    public function get_revisionproducto()
    {
        $assignments = Product::select('products.id', 'users.name as empleado', 'products.name as producto', 'products.marca', 'products.modelo', 'products.serie', 'products.etiqueta', 'revision', 'review_observations')->where('employee_id', '!=', null)->join('users', 'users.id', 'products.employee_id');
        return Datatables::of($assignments)
            ->addColumn('action', 'admin.assignments.action')
            ->rawColumns(['action'])
            ->toJson();
    }

    /** Usuarios con certificación pendiente (mismo criterio que antes, sin cargar filas en memoria). */
    private function countPendingCertificationUsers(): int
    {
        return User::query()->where('certification', '0')->count();
    }

    /**
     * Usuarios certificados que no aparecen en asset_users (subconsulta; evita pluck masivo de IDs).
     */
    private function countCertifiedUsersWithoutAsset(): int
    {
        return User::query()
            ->where('certification', '1')
            ->whereNotExists(function ($q) {
                $q->selectRaw('1')
                    ->from('asset_users')
                    ->whereColumn('asset_users.user_id', 'users.id')
                    ->whereNull('asset_users.deleted_at');
            })
            ->count();
    }
}
