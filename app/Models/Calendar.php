<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Calendar extends Model
{
	 use SoftDeletes;
     protected $fillable = [
         'user_id', 'actividad', 'descripcion', 'status',
         'start_date', 'end_date', 'hora_end', 'hora',
     ];
}
