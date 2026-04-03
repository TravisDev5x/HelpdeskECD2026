<?php

namespace App\Http\Controllers\Admin;

use App\Models\BitacoraHost;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AssetUser;
use Carbon\Carbon;
use Exception;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Auth;

class BitacoraHostController extends Controller
{
    public function __construct()
    {
        // Alineado con RolesAndPermissionsSeeder: solo existen `read` y `create bitacorasHost`.
        $this->middleware(['permission:read bitacorasHost'], ['only' => ['index', 'getBitacoras']]);
        $this->middleware(['permission:create bitacorasHost'], ['only' => ['create', 'store', 'show', 'edit', 'update', 'destroy']]);
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
        return view('admin.bitacoraHost.index')->with('count', $wordCount)->with('countChecks', $users);
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
            $bitacoraHost = BitacoraHost::select('bitacora_hosts.*', 'users.name')
                ->leftJoin('users', 'bitacora_hosts.user_id', '=', 'users.id')
                // ->whereBetween('bitacora_hosts.fecha',[$fecha_i,$fecha_f])
                // ->where('bitacoras.user_id', auth()->id())
                ->get();
        } else {
            $bitacoraHost = BitacoraHost::select('bitacora_hosts.*', 'users.name')
                ->leftJoin('users', 'bitacora_hosts.user_id', '=', 'users.id')
                // ->whereBetween('bitacora_hosts.fecha',[$fecha_i,$fecha_f])
                ->where('bitacora_hosts.user_id', auth()->id())
                ->get();
        }

        return Datatables::of($bitacoraHost)
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
        return view('admin.bitacoraHost.create')->with('count', $wordCount)->with('countChecks', $users);
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
        $bitacoraHost = new BitacoraHost;

        try {
            $bitacoraHost->create([
                'user_id' => $id,
                'host' => $request->host,
                'ip' => $request->ip,
                'bd' => $request->bd,
                'descripcion' => $request->descripcion
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
    public function show(BitacoraHost $bitacoraHost)
    {
        return view('admin.bitacoraHost.edit', compact('bitacoraHost'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Bitacora  $bitacora
     * @return \Illuminate\Http\Response
     */
    public function edit(BitacoraHost $bitacoraHost)
    {
        // return $bitacoraHost;
        return view('admin.bitacoraHost.edit', compact('bitacoraHost'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Bitacora  $bitacora
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BitacoraHost $bitacoraHost)
    {
        //
        // return $bitacora;
        $bitacoraHost->host = $request->host;
        $bitacoraHost->ip = $request->ip;
        $bitacoraHost->bd = $request->bd;
        $bitacoraHost->descripcion = $request->descripcion;
        try {
            $bitacoraHost->save();
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
    public function destroy(BitacoraHost $bitacoraHost)
    {
        try {
            $bitacoraHost->delete();
            $response = 'Se ha eliminado correctamente';
        } catch (\Throwable $th) {
            $response = 'Error al eliminar.';
        }

        return back()->withFlash($response);
    }
}
