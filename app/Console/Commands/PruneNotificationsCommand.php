<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Reduce crecimiento de la tabla notifications (solo lecturas ya marcadas como leídas).
 */
class PruneNotificationsCommand extends Command
{
    protected $signature = 'helpdesk:prune-notifications
                            {--days=90 : Eliminar notificaciones leídas más antiguas que estos días}';

    protected $description = 'Elimina notificaciones de panel marcadas como leídas y más antiguas que el umbral.';

    public function handle(): int
    {
        $days = max(30, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        if (! DB::getSchemaBuilder()->hasTable('notifications')) {
            $this->warn('La tabla notifications no existe.');

            return self::SUCCESS;
        }

        $deleted = DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('read_at', '<', $cutoff)
            ->delete();

        $this->info("Eliminadas {$deleted} notificación(es) leídas anteriores a {$cutoff->toDateTimeString()}.");

        return self::SUCCESS;
    }
}
