<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Fase B: archivo renombrado de ubicacion.php → Ubicacion.php (PSR-4).
 */
class Ubicacion extends Model
{
    use SoftDeletes;

    protected $table = 'ubicaciones';
    protected $fillable = ['id_sede', 'ubicacion'];

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'id_sede', 'id')->withTrashed();
    }
}
