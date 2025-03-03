<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description'
    ];

    public function majors()
    {
        return $this->hasMany(Major::class);
    }
} 