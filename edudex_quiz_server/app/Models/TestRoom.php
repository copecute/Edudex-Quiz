<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestRoom extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'capacity',
        'test_location_id',
        'is_active'
    ];

    protected $casts = [
        'capacity' => 'integer',
        'is_active' => 'boolean'
    ];

    public function testLocation()
    {
        return $this->belongsTo(TestLocation::class);
    }

    public function testShifts()
    {
        return $this->belongsToMany(TestShift::class, 'test_session_rooms')
                    ->withTimestamps();
    }

    public function testShiftSubjectRooms()
    {
        return $this->hasMany(TestShiftSubjectRoom::class);
    }
} 