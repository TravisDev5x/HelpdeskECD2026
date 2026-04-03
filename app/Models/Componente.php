<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\InvAsset; 

class Componente extends Model
{
    use SoftDeletes;

    protected $table = 'components';

    protected $fillable = [
        'producto_id', // Old inventory
        'asset_id',    // New inventory
        'name',
        'serie',
        'marca',
        'modelo',
        'capacidad',
        'observacion',
        'costo',
        'status',
        'fecha_ingreso',
        'owner',
        'company_id',
        'user_id',
    ];

    
    public function equipo()
    {
        
        return $this->belongsTo(\App\Models\Product::class, 'producto_id', 'id')->withTrashed();
    }

    
    public function asset()
    {
        return $this->belongsTo(InvAsset::class, 'asset_id');
    }
}