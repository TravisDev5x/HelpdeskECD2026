<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\AssetUser;
use App\Models\User;

class RoleController extends Controller
{
  public function __construct()
  {
    $this->middleware(['permission:create role'], ['only' => ['create', 'store']]);
    $this->middleware(['permission:read roles'], ['only' => ['index']]);
    $this->middleware(['permission:update role'], ['only' => ['edit', 'update']]);
    $this->middleware(['permission:delete role'], ['only' => ['destroy']]);
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
    $users = User::select('id')->where('certification','1')->whereNotIn('id', $assets)->count();
    return view('admin.roles.index')->with('count', $wordCount)->with('countChecks', $users);
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
    $users = User::select('id')->where('certification','1')->whereNotIn('id', $assets)->count();
    return view('admin.roles.create')->with('count', $wordCount)->with('countChecks', $users);
  }

  /**
  * Store a newly created resource in storage.
  *
  * @param  \Illuminate\Http\Request  $request
  * @return \Illuminate\Http\Response
  */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'permissions' => 'nullable|array',
      'permissions.*' => 'integer|exists:permissions,id',
    ]);

    $role = Role::create([
      'name' => $validated['name'],
      'guard_name' => config('auth.defaults.guard', 'web'),
    ]);

    $role->permissions()->sync($request->input('permissions', []));

    return redirect()->route('admin.roles.index', $role->id)
    ->withFlash('Rol guardado con éxito');
  }

  /**
  * Display the specified resource.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function show($id)
  {
    //
  }

  /**
  * Show the form for editing the specified resource.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function edit(Role $role)
  {
    return view('admin.roles.edit', compact('role'));
  }

  /**
  * Update the specified resource in storage.
  *
  * @param  \Illuminate\Http\Request  $request
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function update(Request $request, Role $role)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'permissions' => 'nullable|array',
      'permissions.*' => 'integer|exists:permissions,id',
    ]);

    $role->update(['name' => $validated['name']]);

    $role->permissions()->sync($request->input('permissions', []));
    return redirect()->route('admin.roles.edit', $role->id)
    ->withFlash('Rol actualizado con éxito');
  }

  /**
  * Remove the specified resource from storage.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function destroy(Role $role)
  {
    $role->delete();
    return back()->withFlash('Rol eliminado correctamente');
  }
}
