<?php

namespace App\Livewire\Auth;

use App\Actions\Auth\SendLoginSupportAlert;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.livewire-bare')]
class PasswordRequest extends Component
{
    public string $email = '';

    public string $supportIdentifier = '';

    public string $supportReason = 'Olvido de contraseña';

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirectIntended(RouteServiceProvider::HOME);
        }
    }

    public function sendResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
        ], [], [
            'email' => 'correo',
        ]);

        $response = Password::broker()->sendResetLink(
            ['email' => $this->email]
        );

        if ($response === Password::RESET_LINK_SENT) {
            session()->flash('status', __($response));

            return;
        }

        throw ValidationException::withMessages([
            'email' => [__($response)],
        ]);
    }

    public function sendSupportAlert(): void
    {
        $this->validate([
            'supportIdentifier' => ['required', 'string', 'max:255'],
            'supportReason' => ['nullable', 'string', 'max:500'],
        ], [], [
            'supportIdentifier' => 'usuario o correo',
            'supportReason' => 'motivo',
        ]);

        SendLoginSupportAlert::run(
            $this->supportIdentifier,
            $this->supportReason !== '' ? $this->supportReason : null
        );

        session()->flash('message', 'Tu solicitud fue enviada a Administración o Soporte. Te contactarán para restablecer acceso.');
    }

    public function render()
    {
        return view('livewire.auth.password-request');
    }
}
