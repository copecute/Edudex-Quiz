<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ExamPeriodRoom extends Model
{
    protected $fillable = [
        'exam_period_id',
        'room_id'
    ];

    public function examPeriod(): BelongsTo
    {
        return $this->belongsTo(ExamPeriod::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function examShifts(): BelongsToMany
    {
        return $this->belongsToMany(ExamShift::class, 'exam_shift_rooms')
                    ->withPivot('exam_period_proctor_id')
                    ->withTimestamps();
    }

    public function proctors(): BelongsToMany
    {
        return $this->belongsToMany(ExamPeriodProctor::class, 'exam_shift_room_proctors', 'exam_shift_room_id')
                    ->withTimestamps();
    }

    // Relationship với thí sinh trong phòng thi
    public function examPeriodRoomStudents()
    {
        return $this->hasMany(ExamPeriodRoomStudent::class);
    }

    // Relationship với cán bộ coi thi
    public function proctor()
    {
        return $this->belongsTo(ExamPeriodProctor::class, 'exam_period_proctor_id');
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