<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvComponent extends Model
{
    
    protected $table = 'components'; 

    protected $fillable = [
        'asset_id',
        'origin_asset_id',
        'name',
        'marca',
        'modelo',
        'serie',
        'capacidad',
        'observacion',
        'status'
    ];

    public function asset()
    {
        return $this->belongsTo(InvAsset::class, 'asset_id', 'id');
    }

    public function originAsset()
    {
        return $this->belongsTo(InvAsset::class, 'origin_asset_id')
                    ->withTrashed();
    }

    public function movements()
    {
        return $this->hasMany(InvComponentMovement::class, 'component_id')->orderBy('date', 'desc');
    }
}