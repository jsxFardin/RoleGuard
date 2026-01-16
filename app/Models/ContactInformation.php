<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class ContactInformation extends Model
{
    use Auditable;

    protected $table = 'contact_information';

    protected $fillable = [
        'type',
        'label',
        'value',
        'is_primary',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
