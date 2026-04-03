<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'user_id', 'campaing_id', 'revision', 'observaciones',
    ];
    
    public function user()
    {
      return $this->belongsTo(User::class)->withTrashed();
    }

    public function campaign()
    {
    	 return $this->belongsTo(Campaign::class);
    }
}
