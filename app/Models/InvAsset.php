<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\User;
use App\Models\Company;
use App\Models\Sede;
use App\Models\Ubicacion;

class InvAsset extends Model
{
    use SoftDeletes;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'internal_tag', 'serial', 'category_id', 'status_id', 'condition',
                'label_id',
                'company_id', 'sede_id', 'ubicacion_id', 'current_user_id',
                'cost', 'purchase_date', 'warranty_expiry', 'specs', 'notes',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'uuid',
        'internal_tag',
        'serial',
        'name',
        'category_id',
        'status_id',
        'label_id',
        'company_id',
        'condition',
        'sede_id',
        'ubicacion_id',
        'specs',
        'cost',
        'purchase_date',
        'warranty_expiry',
        'supplier',
        'invoice_number',
        'current_user_id',
        'image_path',
        'notes',
    ];

    protected $casts = [
        'specs'           => 'array',
        'purchase_date'   => 'date',
        'warranty_expiry' => 'date',
        'cost'            => 'decimal:2'
    ];

    // --- RELACIONES ---

    public function category()
    {
        return $this->belongsTo(InvCategory::class, 'category_id');
    }

    public function status()
    {
        return $this->belongsTo(InvStatus::class, 'status_id');
    }

    public function label()
    {
        return $this->belongsTo(InvLabel::class, 'label_id');
    }

    // [NUEVO] Relación Directa con Empresa
    public function company()
    {
        // Si tu modelo está en App\Company, quita 'Models\'
        if (class_exists(\App\Models\Company::class)) {
            return $this->belongsTo(\App\Models\Company::class, 'company_id');
        }
        return $this->belongsTo(InvStatus::class, 'status_id')->whereRaw('1 = 0'); // Fallback
    }

    public function sede()
    {
        if (class_exists(\App\Models\Sede::class)) {
            return $this->belongsTo(\App\Models\Sede::class, 'sede_id');
        }
        return $this->belongsTo(InvStatus::class, 'status_id')->whereRaw('1 = 0');
    }

    public function ubicacion()
    {
        if (class_exists(\App\Models\Ubicacion::class)) {
            return $this->belongsTo(\App\Models\Ubicacion::class, 'ubicacion_id');
        } 
        return $this->belongsTo(InvStatus::class, 'status_id')->whereRaw('1 = 0'); 
    }

    public function currentUser()
    {
        return $this->belongsTo(User::class, 'current_user_id');
    }

    public function movements()
    {
        return $this->hasMany(InvMovement::class, 'asset_id')->orderBy('date', 'desc');
    }

    public function maintenances()
    {
        return $this->hasMany(InvMaintenance::class, 'asset_id')->orderBy('start_date', 'desc');
    }

    public function images()
    {
    return $this->hasMany(InvAssetImage::class, 'inv_asset_id');
    }

   public function components()
    {
        // 1er parámetro: El Modelo de los componentes
        // 2do parámetro: El nombre EXACTO de la columna en la tabla 'components' (según tu foto es 'asset_id')
        // 3er parámetro: El ID local (usualmente 'id')
        return $this->hasMany(InvComponent::class, 'asset_id', 'id');
    }

    public function extractedComponents()
    {
        return $this->hasMany(InvComponent::class, 'origin_asset_id');
    }
}