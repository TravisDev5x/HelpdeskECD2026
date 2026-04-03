<?php

namespace App\Livewire\Auth;

use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('admin.layout')]
#[Title(' | Confirmar contraseña')]
class PasswordConfirm extends Component
{
    public string $password = '';

    public function mount(): void
    {
        abort_unless(Auth::check(), 403);
    }

    public function confirm(): void
    {
        $this->validate([
            'password' => ['required', 'current_password:web'],
        ], [], [
            'password' => 'contraseña',
        ]);

        request()->session()->put('auth.password_confirmed_at', time());

        $this->password = '';

        $this->redirectIntended(RouteServiceProvider::HOME);
    }

    public function render()
    {
        return view('livewire.auth.password-confirm');
    }
}
