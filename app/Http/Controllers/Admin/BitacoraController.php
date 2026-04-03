<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bitacora;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AssetUser;
use Carbon\Carbon;
use Exception;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Auth;


class BitacoraController extends Controller
{
    public function __construct()
    {
        // Mismo criterio que BitacoraHost: solo existen `read` y `create bitacoras` en seeders.
        $this->middleware(['permission:read bitacoras'], ['only' => ['index', 'getBitacoras']]);
        $this->middleware(['permission:create bitacoras'], ['only' => ['create', 'store', 'show', 'edit', 'update', 'destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $wordlist = User::where('certification', '0')->get();
        $wordCount = $wordlist->count();
        $assets = AssetUser::select('user_id')->distinct()->get();
        $users = User::select('id')->where('certification', '1')->whereNotIn('id', $assets)->count();
        return view('admin.bitacora.index')->with('count', $wordCount)->with('countChecks', $users);
    }

    public function getBitacoras(Request $request)
    {
        // return $request;
        // if (!$request->fecha_i) {
        //     $fecha_i = Carbon::yesterday();
        // }
        // if (!$request->fecha_f) {
        //     $fecha_f = Carbon::tomorrow();
        // }
        // return $fecha_f;
        $role =  Auth::user()->roles->pluck('name');
            // return $role[0];
        if ($role[0] == 'Admin') {
            $bitacoras = Bitacora::select('bitacoras.*', 'users.name')
                ->leftJoin('users', 'bitacoras.user_id', '=', 'users.id')
                // ->whereBetween('bitacoras.fecha', [$fecha_i, $fecha_f])
                // ->where('bitacoras.user_id', auth()->id())
                ->get();
        } else {
            $bitacoras = Bitacora::select('bitacoras.*', 'users.name')
                ->leftJoin('users', 'bitacoras.user_id', '=', 'users.id')
                // ->whereBetween('bitacoras.fecha', [$fecha_i, $fecha_f])
                ->where('bitacoras.user_id', auth()->id())
                ->get();
        }
        return Datatables::of($bitacoras)
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $wordlist = User::where('certification', '0')->get();
        $wordCount = $wordlist->count();
        $assets = AssetUser::select('user_id')->distinct()->get();
        $users = User::select('id')->where('certification', '1')->whereNotIn('id', $assets)->count();
        return view('admin.bitacora.create')->with('count', $wordCount)->with('countChecks', $users);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $response = '';
        // $request->validate([
        //     'id' => 'required',
        //     'user_id' => [
        //       'required',
        //       'numeric'
        //     ],
        //   ]);
        $id = auth()->id();
        $user = User::find($id)->first();
        //   return $user;
        $bitacora = new Bitacora;

        try {
            $bitacora->create([
                'user_id' => $id,
                'actividad' => $request->actividad,
                'descripcion' => $request->descripcion,
                'fecha' => $request->fecha,
                'duracion' => $request->duracion
            ]);
            $response .= 'Bitácora guardada correctamnete';
        } catch (Exception $e) {
            $response .= 'Error al guardar, intente de nuevo...' . $e;
        }

        //   return $bitacora->id;
        // return $response;
        return back()->withFlash($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Bitacora  $bitacora
     * @return \Illuminate\Http\Response
     */
    public function show(Bitacora $bitacora)
    {
        return view('admin.bitacora.edit', compact('bitacora'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Bitacora  $bitacora
     * @return \Illuminate\Http\Response
     */
    public function edit(Bitacora $bitacora)
    {
        // return $bitacora;
        return view('admin.bitacora.edit', compact('bitacora'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Bitacora  $bitacora
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Bitacora $bitacora)
    {
        //
        // return $bitacora;
        $bitacora->actividad = $request->actividad;
        $bitacora->duracion = $request->duracion;
        $bitacora->fecha = $request->fecha;
        $bitacora->descripcion = $request->descripcion;
        try {
            $bitacora->save();
            $response = 'Se ha actualizado correctamente';
        } catch (\Throwable $th) {
            $response = 'Error al actualizar.';
        }

        return back()->withFlash($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Bitacora  $bitacora
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bitacora $bitacora)
    {
        try {
            $bitacora->delete();
            $response = 'Se ha eliminado correctamente';
        } catch (\Throwable $th) {
            $response = 'Error al eliminar.';
        }

        return back()->withFlash($response);
    }
}
