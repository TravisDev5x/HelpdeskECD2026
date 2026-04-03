<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Did;


class DidController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:modulo.did']);
    }

  public function index()
  {

    return view('admin.did.index');
  }

  public function create()
  {
    return view('admin.did.create');
  }

  public function get_did()
  {
    $dids = Did::orderBy('did')->get();
    return Datatables::of($dids)
      ->make(true);
  }

  public function store(Request $request)
  {
    $did = Did::create($request->except('_token'));
    return redirect(route('did'));
  }

  public function show($id)
  {
    $did = Did::find($id);
    return view('admin.did.show', compact('did'));
  }

  public function detalle(Request $request)
  {
    return Did::find($request->id);
  }

  public function update(Request $request, $id)
  {
    $did = Did::find($id);
    $did->update($request->except('_token'));

    return back();
  }
}