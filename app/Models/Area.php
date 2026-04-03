<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
  use SoftDeletes;
  
  protected $fillable = ['name'];

  public function failure()
  {
    return $this->hasMany(Failure::class);
  }

  public function departments()
  {
    return $this->hasMany(Department::class, 'area_id');
  }

  public function users()
  {
    return $this->hasMany(User::class, 'area_id');
  }

  public function campaigns()
  {
    return $this->hasMany(Campaign::class, 'area_id');
  }
}
