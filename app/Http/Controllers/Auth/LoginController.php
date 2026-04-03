<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\InternalUserNotification;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Importante para la limpieza directa
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * La pantalla de login es el componente Livewire {@see \App\Livewire\Auth\Login}.
     * Este método solo se conserva por compatibilidad con el trait (p. ej. referencias internas).
     */
    public function showLoginForm()
    {
        return redirect()->route('login');
    }

    /**
     * 
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Sesión única por usuario: al iniciar sesión se cierran las demás sesiones
     * del mismo usuario (en otros equipos/navegadores). Requiere SESSION_DRIVER=database.
     */
    protected function authenticated(Request $request, $user)
    {
        if ($user instanceof User && blank($user->email)) {
            $this->notifySupportMissingEmail($user);
        }

        if (config('session.driver') === 'database') {
            $sessionId = session()->getId();
            // Marcar la sesión actual con el user_id para poder identificar sesiones por usuario
            DB::table('sessions')
                ->where('id', $sessionId)
                ->update([
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            // Cerrar cualquier otra sesión del mismo usuario (otro equipo/navegador)
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', $sessionId)
                ->delete();
        }

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Permite login con usuario o correo usando el mismo campo.
     */
    protected function credentials(Request $request): array
    {
        $login = trim((string) $request->input('usuario'));
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'usuario';

        return [
            $field => $login,
            'password' => (string) $request->input('password'),
        ];
    }

    
    public function username()
    {
        return 'usuario';
    }

    /**
     * Si el usuario no tiene correo, notificar a Soporte para regularizar la cuenta.
     */
    private function notifySupportMissingEmail(User $user): void
    {
        $recipients = User::role(['Admin', 'Soporte'])->get();
        if ($recipients->isEmpty()) {
            return;
        }

        $title = 'Usuario sin correo detectado';
        $message = 'El usuario '.$user->name.' ('.$user->usuario.') inició sesión sin correo registrado. Revisar y completar email.';
        $url = route('admin.users.edit', $user->id);

        Notification::send($recipients, new InternalUserNotification(
            $title,
            Str::limit($message, 240),
            $url,
            'user_missing_email'
        ));
    }

    /**
     * Límite de intentos de login por usuario/IP (seguridad: fuerza bruta).
     * Compatible con ThrottlesLogins (laravel/ui).
     */
    protected function maxAttempts()
    {
        return 5;
    }

    protected function decayMinutes()
    {
        return 1;
    }
}