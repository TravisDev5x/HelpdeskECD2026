<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvAssetImage extends Model
{
    

    protected $table = 'inv_asset_images';

    protected $fillable = [
        'inv_asset_id',
        'path',
        'type'
    ];

    public function asset()
    {
        return $this->belongsTo(InvAsset::class, 'inv_asset_id');
    }
}