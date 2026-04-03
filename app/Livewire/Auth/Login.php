<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Notifications\InternalUserNotification;
use App\Support\Notifications\InternalNotificationRecipients;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.livewire-bare')]
class Login extends Component
{
    public string $usuario = '';

    public string $password = '';

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower(trim($this->usuario)).'|'.request()->ip());
    }

    public function mount(): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            $target = $user instanceof User && $user->can('read services')
                ? route('admin')
                : route('profile');
            $this->redirectIntended($target);
        }
    }

    public function authenticate(): void
    {
        $this->validate([
            'usuario' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $request = request();
        $limiter = app(RateLimiter::class);
        $key = $this->throttleKey();

        if ($limiter->tooManyAttempts($key, 5)) {
            event(new Lockout($request));

            $seconds = $limiter->availableIn($key);
            throw ValidationException::withMessages([
                'usuario' => [trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ])],
            ])->status(Response::HTTP_TOO_MANY_REQUESTS);
        }

        $login = trim($this->usuario);
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'usuario';
        $credentials = [
            $field => $login,
            'password' => $this->password,
        ];

        if (! Auth::attempt($credentials, false)) {
            $limiter->hit($key, 60);

            throw ValidationException::withMessages([
                'usuario' => [trans('auth.failed')],
            ]);
        }

        $request->session()->regenerate();
        $limiter->clear($key);

        if ($request->hasSession()) {
            $request->session()->put('auth.password_confirmed_at', time());
        }

        $user = Auth::user();
        if ($user instanceof User && blank($user->email)) {
            $this->notifySupportMissingEmail($user);
        }

        if (config('session.driver') === 'database' && $user instanceof User) {
            $sessionId = session()->getId();
            DB::table('sessions')
                ->where('id', $sessionId)
                ->update([
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', $sessionId)
                ->delete();
        }

        $this->password = '';

        $home = $user instanceof User && $user->can('read services')
            ? route('admin')
            : route('profile');

        $this->redirectIntended($home);
    }

    private function notifySupportMissingEmail(User $user): void
    {
        $recipients = InternalNotificationRecipients::withPermission('receive internal notification user missing email');
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

    public function render()
    {
        return view('livewire.auth.login');
    }
}
