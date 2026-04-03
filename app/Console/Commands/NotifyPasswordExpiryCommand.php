<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\InternalUserNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Avisa por notificación interna (panel) a usuarios cuya contraseña caduca
 * en los próximos N días (por defecto 5). Una vez al día por usuario.
 */
class NotifyPasswordExpiryCommand extends Command
{
    protected $signature = 'helpdesk:notify-password-expiry';

    protected $description = 'Envía notificaciones de contraseña próxima a vencer (ventana configurable).';

    public function handle(): int
    {
        $warnDays = max(1, (int) config('helpdesk.password_warning_days_before', 5));

        $until = now()->addDays($warnDays)->endOfDay();

        $query = User::query()
            ->whereNull('deleted_at')
            ->whereNotNull('password_expires_at')
            ->where('password_expires_at', '>', now())
            ->where('password_expires_at', '<=', $until);

        $count = 0;
        $query->chunkById(100, function ($users) use (&$count) {
            foreach ($users as $user) {
                if ($this->alreadyNotifiedToday($user)) {
                    continue;
                }

                $expires = $user->password_expires_at;
                $title = 'Contraseña próxima a vencer';
                $message = 'Tu contraseña caduca el '.$expires->format('d/m/Y').'. '
                    .'Cámbiala desde tu perfil para mantener el acceso.';

                $user->notify(new InternalUserNotification(
                    $title,
                    $message,
                    route('profile'),
                    'password_expiring_soon'
                ));
                $count++;
            }
        });

        $this->info("Notificaciones enviadas: {$count}.");

        return self::SUCCESS;
    }

    private function alreadyNotifiedToday(User $user): bool
    {
        $type = 'password_expiring_soon';

        return DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->where('created_at', '>=', now()->startOfDay())
            ->where('data', 'like', '%"type":"'.$type.'"%')
            ->exists();
    }
}
