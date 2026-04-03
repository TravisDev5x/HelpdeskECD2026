<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
  use SoftDeletes;
  
  protected $fillable = [
      'user_id', 'employee_id', 'company_id', 'serie', 'name',
      'etiqueta', 'marca', 'modelo', 'medio', 'ip', 'mac',
      'observacion', 'responsiva', 'status', 'costo', 'fecha_ingreso',
      'maintenance', 'maintenance_date', 'last_maintenance_date',
      'date_assignment', 'owner', 'revision', 'review_observations',
      'product_maintenance', 'ubicacion_id', 'acepted_at', 'sede_id',
  ];

  protected $casts = [
      'fecha_ingreso' => 'datetime',
      'maintenance_date' => 'date',
      'last_maintenance_date' => 'date',
      'date_assignment' => 'date',
      'acepted_at' => 'datetime',
  ];

  public function company()
  {
    return $this->belongsTo(Company::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id')->withTrashed();
  }

  public function employee()
  {
    return $this->belongsTo(User::class, 'employee_id')->withTrashed();
  }

  public function assignments()
  {
    return $this->hasMany(Assignment::class, 'product_id');
  }

  // public static function create(array $attributes = [])
  // {
  //
  //   $attributes['user_id'] = auth()->id();
  //
  //   $product = static::query()->create($attributes);
  //
  //   return $product;
  //
  // }

}
