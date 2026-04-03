<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
  use SoftDeletes;
  protected $fillable = ['name', 'area_id', 'sede_id'];

  public function users()
  {
    return $this->hasMany(User::class, 'campaign_id');
  }

  public function did()
  {
  	 return $this->belongsTo(Did::class);
  }

  public function area()
  {
    return $this->belongsTo(Area::class, 'area_id')->withTrashed();
  }

  public function sede()
  {
    return $this->belongsTo(Sede::class, 'sede_id')->withTrashed();
  }
}
