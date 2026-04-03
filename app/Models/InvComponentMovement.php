<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class InvComponentMovement extends Model
{
    protected $table = 'inv_component_movements';

    protected $fillable = [
        'component_id',
        'asset_id',
        'origin_asset_id',
        'admin_id',
        'type',
        'date',
        'notes',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function component()
    {
        return $this->belongsTo(InvComponent::class, 'component_id');
    }

    public function asset()
    {
        return $this->belongsTo(InvAsset::class, 'asset_id')->withTrashed();
    }

    public function originAsset()
    {
        return $this->belongsTo(InvAsset::class, 'origin_asset_id')
                    ->withTrashed();
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id')->withTrashed();
    }
}

