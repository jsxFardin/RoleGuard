<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'event',
        'action',
        'route',
        'method',
        'url',
        'ip',
        'user_agent',
        'model_type',
        'model_id',
        'status_code',
        'request',
        'old_values',
        'new_values',
        'meta',
    ];

    protected $casts = [
        'request' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

