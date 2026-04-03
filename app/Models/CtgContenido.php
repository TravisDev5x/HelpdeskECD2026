<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CtgContenido extends Model
{
    use SoftDeletes;

    protected $table = 'ctg_contenidos';

    public function ctg()
    {
      return $this->belongsTo(Ctg::class)->withTrashed();
    }
  }
