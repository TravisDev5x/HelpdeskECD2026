<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asset;
use App\Http\Requests\UpdateAsset;

class AssetsController extends Controller
{
  public function __construct()
  {
    $this->middleware(['permission:create asset'], ['only' => ['create', 'store']]);
    $this->middleware(['permission:read assets'], ['only' => ['index']]);
    $this->middleware(['permission:update asset'], ['only' => ['edit', 'update']]);
    $this->middleware(['permission:delete asset'], ['only' => ['destroy']]);
  }

  public function index()
  {
    $assets = Asset::withTrashed()->get();
    return view('admin.assets.index')->with('assets', $assets);
  }

  public function create()
  {
    return view('admin.assets.create');
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'name' =>
      'required|unique:assets|max:255|min:2',
    ]);

    $assets = Asset::create($data);

    return redirect()->route('admin.assets.index')->withFlash('Activo guardado');
  }

  public function edit(Asset $asset)
  {
    return view('admin.assets.edit', compact('asset'));
  }

  public function update(UpdateAsset $request, Asset $asset)
  {
    $asset->update($request->validated());

    return back()->withFlash('Activo actualizado');
  }

  public function destroy(Asset $asset)
  {
    $asset->delete();
    return back()->withFlash('Suspendido');
  }

  public function restore($id)
  {
      $company = Asset::withTrashed()->findOrFail($id);
      $company->restore();

      return back()->withFlash('Activado');
  }
}
