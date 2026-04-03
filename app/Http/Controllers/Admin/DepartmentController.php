<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Position;
use App\Models\Department;
use App\Models\User;
use App\Models\AssetUser;
use App\Http\Requests\UpdateDepartment;


class DepartmentController extends Controller
{
  public function __construct()
  {
    $this->middleware(['permission:create department'], ['only' => ['create', 'store']]);
    $this->middleware(['permission:read departments'], ['only' => ['index']]);
    $this->middleware(['permission:update department'], ['only' => ['edit', 'update']]);
    $this->middleware(['permission:delete department'], ['only' => ['destroy', 'restore']]);
  }

  public function index()
  {
    return view('admin.departments.index');
  }

  public function getPositions(Request $request)
  {
    $datos = Position::where('department_id', $request->department_id)->get();

    return $datos;
  }

  public function create()
  {
    return view('admin.departments.create');
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'name' =>
      'required|unique:departments|max:255|min:3',
    ]);

    $department = Department::create($data);

    return redirect()->route('admin.departments.index')->withFlash('Departamento guardado');
  }

  public function edit(Department $department)
  {
    return view('admin.departments.edit', compact('department'));
  }

  public function update(UpdateDepartment $request, Department $department)
  {

    $department->update($request->validated());

    return back()->withFlash('Departamento actualizado');
  }

  public function destroy(Department $department)
  {
    $department->delete();
    return back()->withFlash('Departamento Suspendido');
  }

  public function restore($id)
  {
      $company = Department::withTrashed()->findOrFail($id);
      $company->restore();

      return back()->withFlash('Departamento Activado');
  }
}