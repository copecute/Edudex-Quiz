<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Major extends Model
{
    protected $fillable = [
        'code',
        'name',
        'faculty_id',
        'description'
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }
} 