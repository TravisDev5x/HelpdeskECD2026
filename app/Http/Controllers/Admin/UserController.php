<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssetUser;
use App\Models\Bitacora;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Datos para el modal de vista rápida (sin contraseñas ni tokens).
     */
    public function summary(User $user)
    {
        $user->load(['department', 'position', 'campaign', 'roles']);

        $nombreCompleto = trim(implode(' ', array_filter([
            $user->name,
            $user->ap_paterno,
            $user->ap_materno,
        ])));

        return response()->json([
            'id' => $user->id,
            'usuario' => $user->usuario,
            'nombre_completo' => $nombreCompleto !== '' ? $nombreCompleto : ($user->name ?? '—'),
            'email' => $user->email,
            'phone' => $user->phone,
            'certification' => (bool) $user->certification,
            'department' => $user->department?->name,
            'position' => $user->position?->name,
            'position_area' => $user->position?->area,
            'campaign' => $user->campaign?->name,
            'sede' => $user->sede,
            'sedes' => $user->relationLoaded('sedes')
                ? $user->sedes->pluck('sede')->values()->all()
                : $user->sedes()->pluck('sedes.sede')->values()->all(),
            'roles' => $user->roles->pluck('name')->values()->all(),
            'created_at' => $user->created_at?->format('d/m/Y H:i'),
            'avatar_url' => $user->avatar
                ? asset('uploads/avatars/'.$user->avatar)
                : asset('uploads/avatars/default.png'),
            'edit_url' => route('admin.users.edit', $user),
            'profile_url' => route('admin.users.show', $user),
        ]);
    }

    public function countCertifactedNoCheck()
    {
        $assets = AssetUser::select('user_id')->distinct()->get();
        $users = User::with('department')->with('position')
            ->with('campaign')
            ->with('roles')
            ->leftJoin('asset_users', function ($join) {
                $join->on('users.id', '=', 'asset_users.user_id')
                    ->distinct();
            })
            ->whereNotnull('users.id')
            ->whereNotIn('users.id', $assets)
            ->where('certification', 1)
            ->get();

        return $users;
    }

    public function updateCertification(User $user)
    {
        $user->update(['certification' => 1]);

        return back()->withFlash('Certificación actualizada.');
    }

    public function prueba(Request $request)
    {
        if (! $request->fecha_i) {
            $fecha_i = Carbon::yesterday();
        } else {
            $fecha_i = $request->fecha_i;
        }

        if (! $request->fecha_f) {
            $fecha_f = Carbon::tomorrow();
        } else {
            $fecha_f = $request->fecha_f;
        }

        $bitacoras = Bitacora::select('bitacoras.*', 'users.name')
            ->leftJoin('users', 'bitacoras.user_id', '=', 'users.id')
            ->whereBetween('bitacoras.fecha', [$fecha_i, $fecha_f])
            ->where('bitacoras.user_id', auth()->id())
            ->get();

        return $bitacoras;
    }

    public function certificacion_masiva(Request $request)
    {
        try {
            $request->validate(['id' => 'required']);
        } catch (\Throwable $th) {
            return back()->withErrors('Por favor, selecciona al menos un usuario');
        }
        $response = '';
        try {
            foreach ($request->id as $key => $value) {
                $user = User::findOrFail($value);
                try {
                    $user->update(['certification' => 1]);
                } catch (Exception $th) {
                    return back()->withErrors('Error al actualizar.'.$th);
                }
            }
            $response .= 'Se ha realizado la asignación masiva exitosamente.';
        } catch (\Throwable $th) {
            return back()->withErrors('Error al crear asignación masiva.');
        }

        return redirect()->back()->withFlash($response);
    }
}
