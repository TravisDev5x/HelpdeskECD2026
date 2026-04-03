<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use Illuminate\Http\Request;
use App\Models\Campaign;
use App\Models\User;
use App\Models\AssetUser;
use App\Models\Area;
use App\Models\Did;
use App\Models\Review;
use Carbon\Carbon;
use App\Http\Requests\UpdateCampaign;

class CampaignsController extends Controller
{
  public function __construct()
  {
    $this->middleware(['permission:create campaign'], ['only' => ['create', 'store']]);
    $this->middleware(['permission:read campaigns'], ['only' => ['index']]);
    $this->middleware(['permission:update campaign'], ['only' => ['edit', 'update']]);
    $this->middleware(['permission:delete campaign'], ['only' => ['destroy', 'restore']]);
  }

  public function index()
  {
    return view('admin.campaigns.index');
  }

  public function create()
  {
    $dids = Did::pluck('did', 'id');
    return view('admin.campaigns.create', compact('dids'));
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'name' => 'required|unique:campaigns|max:255|min:3',
      'did_id' => 'nullable',
    ]);

    $campaign = Campaign::create($data);

    return redirect()->route('admin.campaigns.index')->withFlash('Campaña guardada');
  }

  public function edit(Campaign $campaign)
  {
    $dids = Did::orderBy('did')->get();
    return view('admin.campaigns.edit', compact('campaign', 'dids'));
  }

  public function update(UpdateCampaign $request, Campaign $campaign)
  {
    $campaign->update($request->validated());

    return redirect()->route('admin.campaigns.index')->withFlash('Campaña actualizada');
  }

  public function destroy(Campaign $campaign)
  {
    $campaign->delete();
    return back()->withFlash('Campaña Suspendida');
  }

  public function restore($id)
  {
    $company = Campaign::withTrashed()->findOrFail($id);
    $company->restore();

    return back()->withFlash('Campaña Activado');
  }

  public function check_campaña(Request $request)
  {
    Review::create(['campaing_id' => $request->id, 'revision' => $request->revision, 'observaciones' => $request->observaciones, 'user_id' => Auth::user()->id]);
    return back();
  }

  public function get_historial_review(Request $request)
  {
    $reviw = Review::with('user')->where('campaing_id', $request->id);
    return Datatables::of($reviw)
      ->make(true);
  }
}
