<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class HistoricalServices extends Model
{
    protected $fillable = [
        'service_id', 'user_id', 'responsable_id', 'failure_id',
        'event_type', 'previous_failure_id', 'escalation_reason',
        'description', 'solution', 'observations', 'status',
        'fecha_fin', 'sede_id', 'fecha_seguimiento',
        'comentario_cliente', 'fecha_relanzar',
    ];

    protected $casts = [
        'fecha_fin' => 'datetime',
        'fecha_seguimiento' => 'datetime',
        'fecha_relanzar' => 'datetime',
    ];

    public function service()
    {
      return $this->belongsTo(Service::class, 'service_id');
    }

    public function responsable()
    {
      return $this->belongsTo(User::class, 'responsable_id');
    }

    public function failure()
    {
        return $this->belongsTo(Failure::class, 'failure_id')->withTrashed();
    }

    public function previousFailure()
    {
        return $this->belongsTo(Failure::class, 'previous_failure_id')->withTrashed();
    }

    public function scopegetTablehistorical($query, $id){
        return $query->select('historical_services.solution as solution', 'historical_services.created_at as fecha', DB::raw("CONCAT(COALESCE(r.name, ''), ' ', COALESCE(r.ap_paterno, ''), ' ', COALESCE(r.ap_materno, '')) as nombre_r"), 'observations', 'description', 'comentario_cliente', 'fecha_relanzar', 'fecha_fin', 'status')->where('service_id', $id)
            ->leftJoin('users as r','r.id','=','historical_services.responsable_id')->get();
    }
}
