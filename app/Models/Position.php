<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
  use SoftDeletes;
  protected $fillable = ['name', 'area', 'extension', 'department_id'];

  public function users()
  {
    return $this->hasMany(User::class, 'position_id');
  }

  public function department()
  {
    return $this->belongsTo(Department::class, 'department_id')->withTrashed();
  }
}
