<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    protected $fillable = [
        'user_id', 'asset_id', 'status',
        'nivel', 'responsiva', 'observations',
    ];

    public function asset()
    {
      return $this->belongsTo(Asset::class);
    }

    public function user()
    {
      return $this->belongsTo(User::class)->withTrashed();
    }
}
