<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvStatus extends Model
{
    protected $fillable = [
        'name',
        'badge_class',
        'assignable',
    ];

    protected $casts = [
        'assignable' => 'boolean',
    ];

    // Helper para saber si un activo en este estado se puede asignar
    public function isAssignable()
    {
        return $this->assignable;
    }

    public function assets()
    {
        return $this->hasMany(InvAsset::class, 'status_id');
    }
}