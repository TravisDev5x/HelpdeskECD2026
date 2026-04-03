<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    protected $fillable = [
        'user_id', 'tipo', 'sistema', 'causa', 'responsable',
        'criticidad', 'acciones', 'observations', 'notas',
        'disqualification_date', 'enablement_date',
    ];

    protected $casts = [
        'disqualification_date' => 'datetime',
        'enablement_date' => 'datetime',
    ];
}
