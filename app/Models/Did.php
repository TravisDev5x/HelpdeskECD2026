<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Did extends Model
{
  protected $fillable = ['did', 'cuenta', 'proveedor', 'tipo'];
    
}
