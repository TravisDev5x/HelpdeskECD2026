<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
  use SoftDeletes;
  protected $fillable = ['name', 'area_id'];

  public function users()
  {
    return $this->hasMany(User::class, 'department_id');
  }

  public function area()
  {
    return $this->belongsTo(Area::class, 'area_id')->withTrashed();
  }

  public function positions()
  {
    return $this->hasMany(Position::class, 'department_id');
  }
}
