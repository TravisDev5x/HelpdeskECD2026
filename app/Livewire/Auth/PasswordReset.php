<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Notifications\InternalUserNotification;
use Illuminate\Auth\Events\PasswordReset as PasswordResetEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.livewire-bare')]
class PasswordReset extends Component
{
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        if (Auth::check()) {
            $this->redirectIntended(RouteServiceProvider::HOME);
        }

        $this->token = $token;
        $this->email = (string) request()->query('email', '');
    }

    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ], [], [
            'email' => 'correo',
            'password' => 'contraseña',
        ]);

        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
            'token' => $this->token,
        ];

        $response = Password::broker()->reset(
            $credentials,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordResetEvent($user));

                Auth::guard()->login($user);
            }
        );

        if ($response !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($response)],
            ]);
        }

        $request = request();
        $request->session()->regenerate();

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
        $this->password_confirmation = '';

        session()->flash('status', __($response));

        $this->redirect(RouteServiceProvider::HOME);
    }

    private function notifySupportMissingEmail(User $user): void
    {
        $recipients = User::role(['Admin', 'Soporte'])->get();
        if ($recipients->isEmpty()) {
            return;
        }

        $title = 'Usuario sin correo detectado';
        $message = 'El usuario '.$user->name.' ('.$user->usuario.') restableció sesión sin correo registrado. Revisar y completar email.';
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
        return view('livewire.auth.password-reset');
    }
}
