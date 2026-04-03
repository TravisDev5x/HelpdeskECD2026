<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthenticationLog extends Model
{
    // Permitimos que estos campos se llenen masivamente
    protected $fillable = [
        'user_id', 
        'ip_address', 
        'user_agent', 
        'login_at', 
        'logout_at'
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
    ];

    // Relación: Un log pertenece a un Usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}