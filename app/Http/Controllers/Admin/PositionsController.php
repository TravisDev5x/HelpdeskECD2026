<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Position;
use App\Models\Department;
use App\Models\User;
use App\Models\AssetUser;
use App\Http\Requests\UpdatePosition;

class PositionsController extends Controller
{

  public function __construct()
  {
    $this->middleware(['permission:create position'], ['only' => ['create', 'store']]);
    $this->middleware(['permission:read positions'], ['only' => ['index']]);
    $this->middleware(['permission:update position'], ['only' => ['edit', 'update']]);
    $this->middleware(['permission:delete position'], ['only' => ['destroy', 'restore']]);
  }
 
  public function index()
  {
    return view('admin.positions.index');
  }

  public function create()
  {
    return view('admin.positions.create');
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'name' => 'required|unique:positions|max:255|min:3',
      'area' => 'required|max:255|min:3',
      'extension' => 'nullable|numeric',
    ]);

    $position = Position::create($data);

    return redirect()->route('admin.positions.index')->withFlash('Puesto guardado');
  }

  public function edit(Position $position)
  {
    $departments = Department::get();
    return view('admin.positions.edit', compact('position', 'departments'));
  }

  public function update(UpdatePosition $request, Position $position)
  {
    $position->update($request->validated());

    return back()->withFlash('Puesto actualizado');
  }

  public function destroy(Position $position)
  {
    $position->delete();
    return back()->withFlash('Puesto Suspendido');
  }

  public function restore($id)
  {
      $company = Position::withTrashed()->findOrFail($id);
      $company->restore();

      return back()->withFlash('Puesto Activado');
  }
}
