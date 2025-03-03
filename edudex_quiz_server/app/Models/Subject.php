<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'code',
        'name',
        'credits',
        'major_id',
        'description'
    ];

    public function major()
    {
        return $this->belongsTo(Major::class);
    }
} 