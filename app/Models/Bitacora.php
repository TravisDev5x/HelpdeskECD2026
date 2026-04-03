<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bitacora extends Model
{
    //
    use SoftDeletes;
    protected $table = 'bitacoras';

    protected $fillable = [
        'user_id', 'actividad', 'duracion', 'fecha', 'descripcion',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->withTrashed();
    }
}
