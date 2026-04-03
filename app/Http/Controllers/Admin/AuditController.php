<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Auth;

class AuditController extends Controller
{
    public function __construct()
    {
        // Seguridad: Solo Admin y Soporte
        $this->middleware(['role:Admin|Soporte']);
    }

    public function index()
    {
       $user = Auth::user(); 
    return view('admin.audits.index', compact('user')); // Solo devuelve la vista contenedora
    
    }

    
}