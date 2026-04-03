<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Failure;
use App\Models\Area;
use App\Models\User;
use App\Models\AssetUser;
use App\Http\Requests\UpdateFailure;

class FailuresController extends Controller
{
  public function __construct()
  {
    $this->middleware(['permission:create failure'], ['only' => ['create', 'store']]);
    $this->middleware(['permission:read failures'], ['only' => ['index']]);
    $this->middleware(['permission:update failure'], ['only' => ['edit', 'update']]);
    $this->middleware(['permission:delete failure'], ['only' => ['destroy']]);
  }

  public function index()
  {
    $failures = Failure::with('services')->withTrashed()->get();
    return view('admin.failures.index', compact('failures'));
  }

  public function getFallas(Request $request)
  {
    $datos = Failure::where('area_id', $request->area_id)->get();

    return $datos;
  }

  public function create()
  {
    $wordlist = User::where('certification', '0')->get();
    $wordCount = $wordlist->count();
    $assets = AssetUser::select('user_id')->distinct()->get();
    $users = User::select('id')->where('certification','1')->whereNotIn('id', $assets)->count();
    $areas = Area::pluck('name', 'id');
    return view('admin.failures.create', compact('areas'))->with('count', $wordCount)->with('countChecks', $users);
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'area_id' => 'required',
      'name' => 'required|unique:failures|max:255|min:3',
    ]);

    $failure = Failure::create($data);

    return redirect()->route('admin.failures.index')->withFlash('Falla guardada');
  }

  public function edit(Failure $failure)
  {
    $areas = Area::get();
    return view('admin.failures.edit', compact('failure', 'areas'));
  }

  public function update(UpdateFailure $request, Failure $failure)
  {
    $failure->update($request->validated());

    return back()->withFlash('Falla actualizada');
  }
  
  public function destroy(Failure $failure)
  {
    $failure->delete();
    return back()->withFlash('Falla Suspendida');
  }

  public function restore($id)
  {
      $company = Failure::withTrashed()->findOrFail($id);
      $company->restore();

      return back()->withFlash('Falla Activada');
  }
}
