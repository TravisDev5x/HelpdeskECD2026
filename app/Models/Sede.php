<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Fase B: archivo renombrado de sede.php → Sede.php (PSR-4).
 */
class Sede extends Model
{
    use SoftDeletes;

    protected $table = 'sedes';

    protected $fillable = ['sede'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'sede_user', 'sede_id', 'user_id')->withTimestamps();
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class, 'sede_id');
    }

    public function inventoryLabels()
    {
        return $this->hasMany(InvLabel::class, 'sede_id');
    }
}
