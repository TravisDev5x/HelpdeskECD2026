<?php

namespace App\Console\Commands;

use App\Models\AuthenticationLog;
use Illuminate\Console\Command;

/**
 * Fase 4 — Reducir crecimiento de `authentication_logs` (no afecta sesiones activas).
 */
class PruneAuthenticationLogsCommand extends Command
{
    protected $signature = 'helpdesk:prune-authentication-logs
                            {--days= : Días de retención (por defecto env HELP_DESK_AUTH_LOG_RETENTION_DAYS o 180)}';

    protected $description = 'Elimina registros antiguos de authentication_logs según login_at.';

    public function handle(): int
    {
        $days = $this->option('days');
        if ($days === null) {
            $days = env('HELP_DESK_AUTH_LOG_RETENTION_DAYS', 180);
        }
        $days = (int) $days;
        if ($days < 30) {
            $this->error('El mínimo recomendado es 30 días.');

            return self::FAILURE;
        }

        $cutoff = now()->subDays($days);

        $deleted = AuthenticationLog::query()
            ->where('login_at', '<', $cutoff)
            ->delete();

        $this->info("Eliminados {$deleted} registro(s) con login_at anterior a {$cutoff->toDateTimeString()}.");

        return self::SUCCESS;
    }
}
