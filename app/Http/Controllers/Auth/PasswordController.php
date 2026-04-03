<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordController extends Controller
{

    public function showChangeForm()
    {
        $user = Auth::user();
        $token = Password::broker()->createToken($user);

        return view('auth.passwords.change', [
            'token' => $token,
            'email' => $user->email ?? '',
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);

        $user = Auth::user();

        if ($request->email !== $user->email) {
            return redirect()->route('password.change')
                ->with('error', 'El correo no coincide con tu cuenta.');
        }

        $tokenData = DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->first();

        if (! $tokenData) {
            return redirect()->route('password.change')
                ->with('error', 'Token inválido. Solicita un nuevo enlace.');
        }

        if (! Hash::check($request->token, $tokenData->token)) {
            return redirect()->route('password.change')
                ->with('error', 'Token inválido. Solicita un nuevo enlace.');
        }

        if (Carbon::parse($tokenData->created_at)->addMinutes(config('auth.passwords.users.expire'))->isPast()) {
            return redirect()->route('password.change')
                ->with('error', 'El token ha expirado. Solicita uno nuevo.');
        }

        $user->password = $request->password;
        $user->setRememberToken(Str::random(60));
        $user->save();

        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        return redirect(RouteServiceProvider::HOME)->with('success', 'Tu contraseña se ha actualizado.');
    }
}
