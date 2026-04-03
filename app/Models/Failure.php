<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Failure extends Model
{
  use SoftDeletes;
  protected $fillable = ['area_id', 'name'];

  public function services()
  {
    return $this->hasMany(Service::class);
  }

  public function area()
  {
    return $this->belongsTo(Area::class, 'area_id');
  }
}
