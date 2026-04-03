<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BitacoraHost extends Model
{
    //
    use SoftDeletes;

    protected $table = 'bitacora_hosts';

    protected $fillable = [
        'user_id', 'host', 'ip', 'bd', 'descripcion',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->withTrashed();
    }

}
