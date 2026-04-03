<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Area;
use App\Models\User;
use App\Models\AssetUser;
use App\Http\Requests\UpdateArea;

class AreasController extends Controller
{
  public function __construct()
  {
    $this->middleware(['permission:create area'], ['only' => ['create', 'store']]);
    $this->middleware(['permission:read areas'], ['only' => ['index']]);
    $this->middleware(['permission:update area'], ['only' => ['edit', 'update']]);
    $this->middleware(['permission:delete area'], ['only' => ['destroy', 'restore']]);
  }

  public function index()
  {
    return view('admin.areas.index');
  }

  public function create()
  {
    return view('admin.areas.create');
  }
 
  public function store(Request $request)
  {
    $data = $request->validate([
      'name' =>
      'required|unique:areas|max:255|min:3',
    ]);

    $area = Area::create($data);

    return redirect()->route('admin.areas.index')->withFlash('Area guardada');
  }

  public function edit(Area $area)
  {
    return view('admin.areas.edit', compact('area'));
  }

  public function update(UpdateArea $request, Area $area)
  {
    $area->update($request->validated());

    return back()->withFlash('Area actualizada');
  }

  public function destroy(Area $area)
  {
    $area->delete();
    return back()->withFlash('Area suspendida');
  }

  public function restore($id)
  {
      $company = Area::withTrashed()->findOrFail($id);
      $company->restore();

      return back()->withFlash('Area Activada');
  }
}
