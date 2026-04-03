<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('activitylog:clean', [
    '--days' => config('activitylog.delete_records_older_than_days', 365),
    '--force',
])->weekly()->sundays()->at('02:00');

Schedule::command('helpdesk:prune-authentication-logs', [
    '--days' => env('HELP_DESK_AUTH_LOG_RETENTION_DAYS', 180),
])->weekly()->sundays()->at('02:15');

Schedule::command('helpdesk:prune-notifications', [
    '--days' => env('HELP_DESK_NOTIFICATIONS_RETENTION_DAYS', 90),
])->weekly()->sundays()->at('02:30');

Schedule::command('helpdesk:notify-password-expiry')->dailyAt('08:00');
