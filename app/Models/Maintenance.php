<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Maintenance extends Model
{
    protected $fillable = [
        'user_id', 'product_id', 'maintenance',
        'maintenance_date', 'last_maintenance_date',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'last_maintenance_date' => 'date',
    ];

    public function user()
    {
      return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function product()
    {
      return $this->belongsTo(Product::class);
    }

    public function scopegetTableMaintenances($query){
    	return $query->select('maintenances.*', DB::raw("CONCAT(COALESCE(users.name, ''), ' ', COALESCE(users.ap_paterno, ''), ' ', COALESCE(users.ap_materno, '')) as nombre_emple"), 'products.name as product', 'products.serie as serie')
            ->leftJoin('users','users.id','=','maintenances.user_id')
            ->leftJoin('products','products.id','=','maintenances.product_id')->get();

    }
}
