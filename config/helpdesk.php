<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Vigencia de contraseña (días desde el último cambio)
    |--------------------------------------------------------------------------
    */
    'password_validity_days' => (int) env('HELP_PASSWORD_VALIDITY_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Aviso previo (días antes del vencimiento): notificación interna + banner
    |--------------------------------------------------------------------------
    */
    'password_warning_days_before' => (int) env('HELP_PASSWORD_WARNING_DAYS', 5),

];
