<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Room extends Model
{
    protected $fillable = [
        'code',
        'name',
        'facility_id',
        'description',
        'capacity',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }
} 