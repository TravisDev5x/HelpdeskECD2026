<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CtgSubcategoria extends Model
{
    use SoftDeletes;

    protected $table = 'ctg_subcategorias';

    public function ctgContenido()
    {
      return $this->belongsTo(CtgContenido::class)->withTrashed();
    }
  }
