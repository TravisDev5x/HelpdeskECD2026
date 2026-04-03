<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Reduce crecimiento de la tabla notifications (leídas y no leídas por el mismo umbral de antigüedad).
 */
class PruneNotificationsCommand extends Command
{
    protected $signature = 'helpdesk:prune-notifications
                            {--days=90 : Antigüedad máxima en días (leídas por read_at; no leídas por created_at)}';

    protected $description = 'Elimina notificaciones de panel más antiguas que el umbral (leídas según read_at; no leídas según created_at).';

    public function handle(): int
    {
        $days = max(30, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        if (! DB::getSchemaBuilder()->hasTable('notifications')) {
            $this->warn('La tabla notifications no existe.');

            return self::SUCCESS;
        }

        $deleted = DB::table('notifications')
            ->where(function ($query) use ($cutoff) {
                $query->where(function ($q) use ($cutoff) {
                    $q->whereNotNull('read_at')->where('read_at', '<', $cutoff);
                })->orWhere(function ($q) use ($cutoff) {
                    $q->whereNull('read_at')->where('created_at', '<', $cutoff);
                });
            })
            ->delete();

        $this->info("Eliminadas {$deleted} notificación(es) anteriores a {$cutoff->toDateTimeString()} (leídas por read_at; no leídas por created_at).");

        return self::SUCCESS;
    }
}
