<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | Supported: "bcrypt", "argon", "argon2id"
    |
    */

    'driver' => env('HASH_DRIVER', 'bcrypt'),

    /*
    |--------------------------------------------------------------------------
    | Bcrypt Options
    |--------------------------------------------------------------------------
    */

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 10),
        // false: permite verificar hashes legados (p. ej. Argon2) hasta rehash_on_login; true exige bcrypt.
        'verify' => env('HASH_VERIFY', false),
        'limit' => env('BCRYPT_LIMIT', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Argon Options
    |--------------------------------------------------------------------------
    */

    'argon' => [
        'memory' => env('ARGON_MEMORY', 1024),
        'threads' => env('ARGON_THREADS', 2),
        'time' => env('ARGON_TIME', 2),
        'verify' => env('HASH_VERIFY', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rehash On Login (Laravel 11+)
    |--------------------------------------------------------------------------
    |
    | Requiere mutador de password compatible (ver App\Models\User).
    |
    */

    'rehash_on_login' => env('HASH_REHASH_ON_LOGIN', true),

];
