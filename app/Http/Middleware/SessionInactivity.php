<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Cierra la sesión tras 20 minutos de inactividad.
 * Con driver database: mantiene user_id en la tabla sessions para sesión única por usuario.
 */
class SessionInactivity
{
    /** Minutos sin actividad para cerrar sesión */
    const IDLE_MINUTES = 20;

    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        // Sesión única por usuario: marcar esta sesión con user_id (requiere SESSION_DRIVER=database)
        if (config('session.driver') === 'database') {
            DB::table('sessions')
                ->where('id', session()->getId())
                ->update([
                    'user_id' => Auth::id(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'last_activity' => time(),
                ]);
        }

        // Cierre explícito por inactividad desde el frontend (redirección a esta ruta)
        if ($request->routeIs('session.logout-inactivity')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Tu sesión se cerró por inactividad. Vuelve a iniciar sesión.');
        }

        $lastActivity = session('last_activity');
        $limit = self::IDLE_MINUTES * 60; // segundos

        if ($lastActivity !== null && (time() - $lastActivity) > $limit) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesión cerrada por inactividad.'], 401);
            }

            return redirect()->route('login')
                ->with('error', 'Tu sesión se cerró por inactividad. Vuelve a iniciar sesión.');
        }

        session(['last_activity' => time()]);

        return $next($request);
    }
}
