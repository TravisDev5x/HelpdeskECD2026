<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
  public function department()
  {
    return $this->belongsTo(Department::class, 'department_id');
  }

  public function assignment()
  {
    return $this->hasMany(Assignment::class, 'employee_id');
  }

  public function campaign()
  {
      return $this->belongsTo(Campaign::class)->withTrashed();
  }
}
