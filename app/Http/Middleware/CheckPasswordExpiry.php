<?php

namespace App\Http\Middleware;

use App\Models\User; // Usa el modelo User correcto
use Closure;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CheckPasswordExpiry
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        // Verificamos si el usuario está logueado
        if ($user) {
            if ($user->password_expires_at === null) {
                return $next($request);
            }

            $passwordExpiresAt = Carbon::parse($user->password_expires_at);
            // lte: incluye “caduca en este instante” (restablecimiento con password_expires_at = now()).
            if ($passwordExpiresAt->lte(Carbon::now())) {
                return redirect()->route('password.change')->with('error', 'Debes actualizar tu contraseña antes de continuar.');
            }
        }

        return $next($request);
    }
}
