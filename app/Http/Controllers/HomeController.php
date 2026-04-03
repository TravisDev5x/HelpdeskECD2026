<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Support\DashboardTicketStats;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Contracts\Support\Renderable
   */

  public function index()
  {
    $hoy = Carbon::now();
    $stats = DashboardTicketStats::counts();
    $nombreUsuario = trim((string) Auth::user()?->name);
    $primerNombre = strtok($nombreUsuario, ' ') ?: 'Usuario';
    $horaActual = (int) $hoy->format('H');

    if ($horaActual < 12) {
      $saludo = 'Buenos días';
    } elseif ($horaActual < 19) {
      $saludo = 'Buenas tardes';
    } else {
      $saludo = 'Buenas noches';
    }

    return view('admin.dashboard', array_merge($stats, compact('hoy', 'saludo', 'primerNombre')));
  }

}
