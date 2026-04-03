<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvCategory extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'type',
        'prefix',
        'require_specs',
    ];

    protected $casts = [
        'require_specs' => 'boolean',
    ];

    public function assets()
    {
        return $this->hasMany(InvAsset::class, 'category_id');
    }
}