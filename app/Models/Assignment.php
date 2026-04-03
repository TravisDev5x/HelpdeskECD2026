<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
  protected $fillable = [
      'user_id', 'employee_id', 'product_id',
      'assignment', 'responsiva', 'observations',
      'revision', 'costo_estado', 'estado_id',
  ];

  public function product()
  {
    return $this->belongsTo(Product::class, 'product_id', 'id')->withTrashed();
  }

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id')->withTrashed();
  }

  public function employee()
  {
    return $this->belongsTo(User::class, 'employee_id')->withTrashed();
  }

}
