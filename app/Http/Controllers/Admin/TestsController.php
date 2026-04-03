<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Test;
use App\Models\Asset;
use App\Models\User;
use App\Models\AssetUser;

class TestsController extends Controller
{

  public function __construct()
  {
    $this->middleware(['permission:create test'], ['only' => ['create', 'store']]);
    $this->middleware(['permission:read tests'], ['only' => ['index']]);
  }

  public function index()
  {
    $wordlist = User::where('certification', '0')->get();
    $wordCount = $wordlist->count();
    $assets = AssetUser::select('user_id')->distinct()->get();
    $users = User::select('id')->where('certification','1')->whereNotIn('id', $assets)->count();
    $tests = Test::with('asset', 'user')->where('nivel',1)->get();
    return view('admin.tests.index')->with('tests', $tests)->with('count', $wordCount)->with('countChecks', $users);
  }

  public function create()
  {
    $wordlist = User::where('certification', '0')->get();
    $wordCount = $wordlist->count();
    $assets = AssetUser::select('user_id')->distinct()->get();
    $users = User::select('id')->where('certification','1')->whereNotIn('id', $assets)->count();
    $assets = Asset::orderBy('name', 'asc')->pluck('name', 'id');
    return view('admin.tests.create')->with('assets', $assets)->with('count', $wordCount)->with('countChecks', $users);
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'asset_id' => 'required|numeric',
      'status' => 'required',
      'test_date' => 'required',
      'nivel' => 'required',
      'observations' => 'required',
    ]);

    $date = new Carbon($request->test_date);

    $data['test_date'] = $date->toDateTimeString();

    $data['user_id'] = auth()->id();

    $test = Test::create($data);

    if($test->status == 'Inactivo')
    {
      return redirect()->route('admin.incidents.create')->withFlash('Prueba guardada, levantar incidencia');
    }

    return redirect()->route('admin.tests.index')->withFlash('Prueba guardada');
  }

  public function nivel2()
  {

    $wordlist = User::where('certification', '0')->get();
    $wordCount = $wordlist->count();
    $assets = AssetUser::select('user_id')->distinct()->get();
    $users = User::select('id')->where('certification','1')->whereNotIn('id', $assets)->count();
    $tests = Test::with('asset', 'user')->where('nivel',2)->get();
    return view('admin.tests.index2')->with('tests', $tests)->with('count', $wordCount)->with('countChecks', $users);

  }
}