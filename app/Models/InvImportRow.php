<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvImportRow extends Model
{
    protected $fillable = [
        'batch_id',
        'row_number',
        'payload',
        'parsed',
        'errors',
        'warnings',
        'action',
        'status',
        'processed_asset_id',
        'resolved_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'parsed' => 'array',
        'errors' => 'array',
        'warnings' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function batch()
    {
        return $this->belongsTo(InvImportBatch::class, 'batch_id');
    }
}
