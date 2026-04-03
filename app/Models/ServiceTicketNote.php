<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceTicketNote extends Model
{
    public const VIS_INTERNAL = 'internal';

    public const VIS_REQUESTER_VISIBLE = 'requester_visible';

    protected $fillable = [
        'service_id',
        'user_id',
        'body',
        'visibility',
        'notify_support',
    ];

    protected function casts(): array
    {
        return [
            'notify_support' => 'boolean',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }
}
