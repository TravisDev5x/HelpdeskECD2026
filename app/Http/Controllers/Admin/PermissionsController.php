<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssetUser;
use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionsController extends Controller
{
  public function __construct()
  {
    $this->middleware(['permission:create permission'], ['only' => ['create', 'store']]);
    $this->middleware(['permission:read permissions'], ['only' => ['index']]);
    $this->middleware(['permission:update permission'], ['only' => ['edit', 'update']]);
    $this->middleware(['permission:delete permission'], ['only' => ['destroy']]);
  }

  public function index()
  {
    $wordlist = User::where('certification', '0')->get();
    $wordCount = $wordlist->count();
    $assets = AssetUser::select('user_id')->distinct()->get();
    $users = User::select('id')->where('certification','1')->whereNotIn('id', $assets)->count();
    $permissionRows = Permission::query()
        ->orderBy('name')
        ->get()
        ->map(function (Permission $permission) {
            $meta = PermissionCatalog::metadata($permission->name);

            return array_merge(
                [
                    'permission' => $permission,
                    'id' => $permission->id,
                    'name' => $permission->name,
                ],
                $meta
            );
        })
        ->sortBy(fn (array $row) => sprintf('%06d-%s', $row['group_sort'], mb_strtolower($row['label'])))
        ->values();

    return view('admin.permissions.index', ['permissionRows' => $permissionRows])->with('count', $wordCount)->with('countChecks', $users);
  }

  public function create()
  {
    $wordlist = User::where('certification', '0')->get();
    $wordCount = $wordlist->count();
    $assets = AssetUser::select('user_id')->distinct()->get();
    $users = User::select('id')->where('certification','1')->whereNotIn('id', $assets)->count();
    return view('admin.permissions.create')->with('count', $wordCount)->with('countChecks', $users);
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
    ]);

    $permission = Permission::create([
      'name' => $validated['name'],
      'guard_name' => config('auth.defaults.guard', 'web'),
    ]);

    return redirect()->route('admin.permissions.edit', $permission->id)
    ->withFlash('Permiso guardado con éxito');
  }

  public function edit(Permission $permission)
  {
    return view('admin.permissions.edit', compact('permission'));
  }

  public function update(Request $request, Permission $permission)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
    ]);

    $permission->update(['name' => $validated['name']]);

    return redirect()->route('admin.permissions.edit', $permission->id)
    ->withFlash('Permisos actualizado con éxito');
  }

  public function destroy(Permission $permission)
  {
    $permission->delete();
    return back()->withFlash('Permiso Suspendido');
  }
}
