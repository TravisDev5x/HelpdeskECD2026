<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Support\DashboardTicketStats;
use App\Models\AssetUser;
use App\Models\User;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:read services'], ['only' => ['indicadores_solos']]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function index()
    {
        return redirect('home');
    }

    public function indicadores_solos(){
      $stats = DashboardTicketStats::counts();
      $assets = AssetUser::select('user_id')->distinct()->get();
      $hoy = Carbon::now();
    	return view('admin.indicadores', array_merge($stats, compact('assets', 'hoy')));
    }
}
