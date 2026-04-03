<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Request;
use Carbon\Carbon;
use App\Models\AuthenticationLog; // Asegúrate de importar tu modelo
use App\Models\User;
use App\Notifications\InternalUserNotification;
use Illuminate\Support\Facades\Notification;

class LogUserActivity
{
    public function handleLogin(Login $event)
    {
        // Al entrar: Crea el registro
        AuthenticationLog::create([
            'user_id' => $event->user->id,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'login_at' => Carbon::now(),
        ]);

        // Solo administradores reciben (y ven) avisos de inicio de sesión en el panel.
        $admins = User::role('Admin')
            ->where('id', '<>', $event->user->id)
            ->get();

        if ($admins->isNotEmpty()) {
            Notification::send(
                $admins,
                new InternalUserNotification(
                    'Inicio de sesión',
                    'El usuario ' . $event->user->name . ' (' . $event->user->usuario . ') inició sesión.',
                    route('admin.sessions.active'),
                    'user_login'
                )
            );
        }
    }

    public function handleLogout(Logout $event)
    {
        if ($event->user) {
            // Al salir: Cierra el registro abierto
            $log = AuthenticationLog::where('user_id', $event->user->id)
                    ->whereNull('logout_at')
                    ->latest('login_at')
                    ->first();

            if ($log) {
                $log->update(['logout_at' => Carbon::now()]);
            }
        }
    }
}