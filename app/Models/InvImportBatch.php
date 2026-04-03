<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvImportBatch extends Model
{
    protected $fillable = [
        'user_id',
        'file_name',
        'mode',
        'defaults',
        'summary',
        'status',
    ];

    protected $casts = [
        'defaults' => 'array',
        'summary' => 'array',
    ];

    public function rows()
    {
        return $this->hasMany(InvImportRow::class, 'batch_id');
    }
}
