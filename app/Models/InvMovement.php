<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class InvMovement extends Model
{
    protected $fillable = [
        'asset_id',
        'type',
        'user_id',
        'previous_user_id',
        'admin_id',
        'responsiva_path',
        'notes',
        'reason',
        'batch_uuid',
        'metadata',
        'date',
    ];

    // Casteamos la fecha para poder usar $mov->date->diffForHumans()
    protected $casts = [
        'date' => 'datetime',
        'metadata' => 'array',
    ];

    // El Activo que se movió
    public function asset()
    {
        return $this->belongsTo(InvAsset::class, 'asset_id')->withTrashed(); // Incluye activos borrados
    }

    // El Empleado involucrado (quien recibió o entregó)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function previousUser()
    {
        return $this->belongsTo(User::class, 'previous_user_id')->withTrashed();
    }

    // El Administrador que registró el movimiento (Tú)
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}