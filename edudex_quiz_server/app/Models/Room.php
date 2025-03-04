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

    // Thêm relationship với ExamPeriodRoom
    public function examPeriodRooms()
    {
        return $this->hasMany(ExamPeriodRoom::class);
    }

    // Thêm relationship với ExamPeriod thông qua bảng trung gian
    public function examPeriods()
    {
        return $this->belongsToMany(ExamPeriod::class, 'exam_period_rooms')
                    ->withTimestamps();
    }

    // Scope để tìm kiếm
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhereHas('facility', function($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
        }
        return $query;
    }

    // Scope để lọc theo cơ sở
    public function scopeByFacility($query, $facilityId = null)
    {
        if ($facilityId) {
            return $query->where('facility_id', $facilityId);
        }
        return $query;
    }

    // Scope để lọc các phòng chưa được phân công trong kỳ thi
    public function scopeNotAssignedToExamPeriod($query, $examPeriodId)
    {
        return $query->whereDoesntHave('examPeriodRooms', function($q) use ($examPeriodId) {
            $q->where('exam_period_id', $examPeriodId);
        });
    }
} 