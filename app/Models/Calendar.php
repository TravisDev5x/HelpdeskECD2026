<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Calendar extends Model
{
    use SoftDeletes;

    public const SCOPE_PERSONAL = 'personal';

    public const SCOPE_TEAM = 'team';

    protected $fillable = [
        'user_id',
        'scope',
        'actividad',
        'descripcion',
        'status',
        'start_date',
        'end_date',
        'hora_end',
        'hora',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isTeam(): bool
    {
        return $this->scope === self::SCOPE_TEAM;
    }

    public function isPersonal(): bool
    {
        return $this->scope === self::SCOPE_PERSONAL || $this->scope === null || $this->scope === '';
    }
}
