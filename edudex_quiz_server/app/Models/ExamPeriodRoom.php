<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamPeriodRoom extends Model
{
    protected $fillable = [
        'exam_period_id',
        'room_id'
    ];

    public function examPeriod()
    {
        return $this->belongsTo(ExamPeriod::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // Scope để tìm kiếm
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->whereHas('room', function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhereHas('facility', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        return $query;
    }

    // Scope để lọc theo cơ sở
    public function scopeByFacility($query, $facilityId = null) 
    {
        if ($facilityId) {
            return $query->whereHas('room', function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            });
        }
        return $query;
    }
} 