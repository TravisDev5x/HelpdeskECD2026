<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvLabel extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sede_id',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }

    public function assets()
    {
        return $this->hasMany(InvAsset::class, 'label_id');
    }
}
