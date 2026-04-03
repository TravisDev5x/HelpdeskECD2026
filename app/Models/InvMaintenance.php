<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class InvMaintenance extends Model
{
    // Campos explícitos para asignación masiva segura.
    protected $fillable = [
        'asset_id',
        'supplier_id',
        'origin_id',
        'modality_id',
        'title',
        'diagnosis',
        'solution',
        'cost',
        'attachments',
        'start_date',
        'end_date',
        'logged_by',
    ];

    // CASTING AUTOMÁTICO
    // Esto es lo más importante de este modelo:
    // 1. 'attachments' => 'array' permite guardar ["foto1.jpg", "foto2.jpg"] y leerlo como array en PHP.
    // 2. Las fechas se convierten en objetos Carbon para poder usar ->format('d/m/Y').
    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'cost'        => 'decimal:2',
        'attachments' => 'array', 
    ];

    // --- RELACIONES ---

    // El equipo que está en mantenimiento
    public function asset()
    {
        // withTrashed() permite ver mantenimientos de equipos que ya fueron dados de baja
        return $this->belongsTo(InvAsset::class, 'asset_id')->withTrashed();
    }

    // El usuario (Administrador) que registró el ticket de mantenimiento
    public function logger()
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    public function origin()
    {
        return $this->belongsTo(InvMaintenanceOrigin::class, 'origin_id');
    }

    public function modality()
    {
        return $this->belongsTo(InvMaintenanceModality::class, 'modality_id');
    }
}