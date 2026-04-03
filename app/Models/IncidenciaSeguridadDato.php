<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncidenciaSeguridadDato extends Model
{
    use SoftDeletes;

    protected $table = 'incidencia_seguridad_datos';

    public function user()
    {
      return $this->belongsTo(User::class)->withTrashed();
    }

    public function categoria()
    {
      return $this->belongsTo(CtgContenido::class, 'ctg_contenido_id', 'id')->withTrashed();
    }

    public function subcategoria()
    {
      return $this->belongsTo(CtgSubcategoria::class, 'ctg_subcategoria_id', 'id')->withTrashed();
    }
  }
