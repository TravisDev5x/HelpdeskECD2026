<?php

namespace App\Livewire\Admin\Sessions;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\AuthenticationLog; // Verifica si es App\AuthenticationLog o App\Models\AuthenticationLog
use App\Models\User; 
use Livewire\WithPagination;
use Carbon\Carbon;

class ActiveSessions extends Component
{
    use WithPagination;
    
    public $search = '';
    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        abort_unless(Auth::check() && Auth::user()->hasAnyRole(['Admin', 'Soporte']), 403);
    }

    public function deleteSession($sessionId)
    {
        abort_unless(Auth::check() && Auth::user()->hasAnyRole(['Admin', 'Soporte']), 403);

        if ($sessionId === session()->getId()) {
            session()->flash('error', 'No puedes cerrar tu propia sesión.');
            return;
        }
        DB::table('sessions')->where('id', $sessionId)->delete();
        session()->flash('message', 'Sesión terminada exitosamente.');
    }

    public function render()
    {
        // 1. KPIs
        $total_users = User::count();
        $active_now_count = DB::table('sessions')->whereNotNull('user_id')->count();

        // USUARIOS REALMENTE INACTIVOS (>30 días sin loguearse)
        // Buscamos usuarios que NO tengan registros en AuthenticationLog en los últimos 30 días
        $inactive_30_days = User::whereDoesntHave('historicalServices', function($q) {
                $q->where('created_at', '>=', Carbon::now()->subDays(30));
            })
            ->where('updated_at', '<', Carbon::now()->subDays(30))
            ->count();

        // 2. OBTENER SESIONES ACTIVAS (Con buscador)
        $active_sessions = DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('sessions.*', 'users.name', 'roles.name as role_name')
            ->whereNotNull('sessions.user_id')
            ->where('users.name', 'like', '%' . $this->search . '%')
            ->orderBy('sessions.last_activity', 'desc')
            ->get();

        // 3. OBTENER HISTORIAL (Con buscador)
        $history = AuthenticationLog::with('user')
            ->whereHas('user', function($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('login_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.sessions.active-sessions', [
            'active_sessions' => $active_sessions,
            'history' => $history,
            'kpis' => [
                'total' => $total_users,
                'online' => $active_now_count,
                'offline' => $total_users - $active_now_count,
                'abandoned' => $inactive_30_days
            ]
        ])
        ->extends('admin.layout')
        ->section('content');
    }
}