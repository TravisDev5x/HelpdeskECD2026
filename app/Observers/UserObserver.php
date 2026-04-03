<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Al cambiar la contraseña, reinicia la vigencia salvo que en el mismo guardado
     * se haya fijado `password_expires_at` (p. ej. admin: forzar cambio al iniciar sesión).
     */
    public function saving(User $user): void
    {
        if (! $user->isDirty('password')) {
            return;
        }

        if ($user->isDirty('password_expires_at')) {
            return;
        }

        $days = (int) config('helpdesk.password_validity_days', 30);
        $user->password_expires_at = now()->addDays($days)->endOfDay();
    }

    public function created(User $user)
    {
        // COMENTADO: Ya lo hace Spatie automáticamente desde el Modelo
        // $user->auditAction('User Created', [], $user->getSnapshot());
    }

    public function updated(User $user)
    {
        // COMENTADO: Esto era lo que causaba el duplicado y el log de salida.
        // Al quitarlo, Spatie usará la configuración "Anti-Ruido" de User.php
        
        /* if ($user->wasChanged('remember_token') && count($user->getChanges()) === 1) { return; }
        if ($user->wasChanged('updated_at') && count($user->getChanges()) === 1) { return; }
        if (!$user->isDirty()) { return; }

        $before = $user->getOriginal();
        $after  = $user->getChanges();
        unset($after['updated_at']);

        if (empty($after)) { return; }

        $user->auditAction('User Updated', $before, $after);
        */
    }

    public function deleted(User $user)
    {
        // COMENTADO
        // $user->auditAction('User Deleted', $user->getSnapshot(), []);
    }

    public function restored(User $user)
    {
        // COMENTADO
        // $user->auditAction('User Restored', [], $user->getSnapshot());
    }
}