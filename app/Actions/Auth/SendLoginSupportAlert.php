<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Notifications\InternalUserNotification;
use App\Support\Notifications\InternalNotificationRecipients;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Notifica a Admin/Soporte cuando alguien pide ayuda para entrar (olvido u bloqueo).
 * Centraliza el aviso a Admin/Soporte por problemas de acceso (olvido de contraseña, etc.).
 */
final class SendLoginSupportAlert
{
    public static function run(string $identifier, ?string $reason): void
    {
        $identifier = trim($identifier);

        $user = User::withTrashed()
            ->where('usuario', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        $recipients = InternalNotificationRecipients::withPermission('receive internal notification password support');

        if ($recipients->isEmpty()) {
            return;
        }

        $label = $user ? ($user->name.' ('.$user->usuario.')') : $identifier;
        $message = 'Solicitud desde recuperación de acceso para apoyo de contraseña. Referencia: '.$label.'. '
            .'Motivo: '.trim((string) ($reason !== null && $reason !== '' ? $reason : 'Olvido de contraseña.'));

        $openUrl = ($user && ! $user->trashed())
            ? route('admin.users.edit', $user->id)
            : route('admin.users.index');

        Notification::send($recipients, new InternalUserNotification(
            'Solicitud de restablecimiento',
            Str::limit($message, 240),
            $openUrl,
            'password_support_request'
        ));
    }
}
