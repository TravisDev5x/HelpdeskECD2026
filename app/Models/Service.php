<?php

namespace App\Models;

use App\Support\Tickets\TicketQueryByRole;
use App\Support\Tickets\TicketStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;



class Service extends Model
{
  /** @deprecated Usar TicketStatus::FINALIZADO */
  public const STATUS_FINALIZADO = TicketStatus::FINALIZADO;

  /** @deprecated Usar TicketStatus::TICKET_ERRONEO */
  public const STATUS_TICKET_ERRONEO = TicketStatus::TICKET_ERRONEO;

  protected $fillable = [
      'user_id', 'responsable_id', 'failure_id',
      'description', 'solution', 'observations',
      'status', 'fecha_fin', 'sede_id',
      'fecha_seguimiento', 'comentario_cliente', 'fecha_relanzar',
  ];

  protected $casts = [
      'fecha_fin' => 'datetime',
      'fecha_seguimiento' => 'datetime',
      'fecha_relanzar' => 'datetime',
  ];

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id')->withTrashed();
  }

  public function failure()
  {
    return $this->belongsTo(Failure::class)->withTrashed();
  }

  public function responsable()
  {
    return $this->belongsTo(User::class, 'responsable_id')->withTrashed();
  }

  public function historicalServices()
  {
    return $this->hasMany(HistoricalServices::class, 'service_id');
  }

  public function scopeAllowed($query)
  {
    $user = auth()->user();
    if (! $user) {
      return $query->whereRaw('1 = 0');
    }

    TicketQueryByRole::applyUserVisibilityScope($query, $user);

    return $query;
  }

  // static public function getTableServices($i, $h){
  //   return Service::select('services.id as id', 'services.fecha_fin as fecha_fin', 'services.description as description', 'services.observations as observations', 'services.created_at as fecha',  DB::raw("CONCAT(COALESCE(users.name, ''), ' ', COALESCE(users.ap_paterno, ''), ' ', COALESCE(users.ap_materno, '')) as nombre_solicitante"),  DB::raw("CONCAT(COALESCE(r.name, ''), ' ', COALESCE(r.ap_paterno, ''), ' ', COALESCE(r.ap_materno, '')) as nombre_r"),'failures.name as servicio', 'departments.name as departamento', 'positions.name as posicion', 'campaigns.name as campania')
  //     ->where('services.status', 'Finalizado')->whereBetween('services.created_at', [$i, $h])
  //     ->leftJoin('failures','failures.id','=','services.failure_id')
  //     ->leftJoin('users','users.id','=','services.user_id')
  //     ->leftJoin('users as r','r.id','=','services.responsable_id')
  //     ->leftJoin('departments','departments.id','=','users.department_id')
  //     ->leftJoin('positions','positions.id','=','users.position_id')
  //     ->leftJoin('campaigns','campaigns.id','=','users.campaign_id')
  //     ->get();
  // }

  // static public function getTableServicesUser($i, $h){
  //   //$user = Auth::user()->id;
  //   //dd($user);

  //   return Service::select('services.id as id', 'services.fecha_fin as fecha_fin', 'services.description as description', 'services.observations as observations', 'services.created_at as fecha',  DB::raw("CONCAT(COALESCE(users.name, ''), ' ', COALESCE(users.ap_paterno, ''), ' ', COALESCE(users.ap_materno, '')) as nombre_solicitante"),  DB::raw("CONCAT(COALESCE(r.name, ''), ' ', COALESCE(r.ap_paterno, ''), ' ', COALESCE(r.ap_materno, '')) as nombre_r"),'failures.name as servicio', 'departments.name as departamento', 'positions.name as posicion', 'campaigns.name as campania')
  //     ->where('services.status', 'Finalizado')
  //     ->where(function($q) {
  //       $q->where('user_id', Auth::user()->id)
  //       ->orWhere('responsable_id', Auth::user()->id);
  //     })
  //     ->whereBetween('services.created_at', [$i, $h])
  //     ->leftJoin('failures','failures.id','=','services.failure_id')
  //     ->leftJoin('users','users.id','=','services.user_id')
  //     ->leftJoin('users as r','r.id','=','services.responsable_id')
  //     ->leftJoin('departments','departments.id','=','users.department_id')
  //     ->leftJoin('positions','positions.id','=','users.position_id')
  //     ->leftJoin('campaigns','campaigns.id','=','users.campaign_id')

  //     ->get();
  // }
}
