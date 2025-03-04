<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ExamPeriod extends Model
{
    protected $fillable = [
        'name',
        'description', 
        'start_time',
        'end_time',
        'is_active'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean'
    ];

    // Scope để tìm kiếm
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where('name', 'like', "%{$search}%");
        }
        return $query;
    }

    // Scope để lọc theo trạng thái
    public function scopeFilterByStatus($query, $status)
    {
        $now = now();
        
        switch ($status) {
            case 'ongoing':
                return $query->where('is_active', true)
                            ->where('start_time', '<=', $now)
                            ->where('end_time', '>=', $now);
            case 'upcoming':
                return $query->where('is_active', true)
                            ->where('start_time', '>', $now);
            case 'completed':
                return $query->where('is_active', true)
                            ->where('end_time', '<', $now);
            case 'locked':
                return $query->where('is_active', false);
            default:
                return $query;
        }
    }

    public function scopeFilterByDateRange($query, $startDate, $endDate)
    {
        if ($startDate) {
            $query->where('start_time', '>=', Carbon::parse($startDate)->startOfDay());
        }
        
        if ($endDate) {
            $query->where('end_time', '<=', Carbon::parse($endDate)->endOfDay());
        }
        
        return $query;
    }

    public function examShifts()
    {
        return $this->hasMany(ExamShift::class);
    }

    public function examPeriodSubjects()
    {
        return $this->hasMany(ExamPeriodSubject::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'exam_period_subjects')
                    ->withPivot('exam_id')
                    ->withTimestamps();
    }

    public function proctors()
    {
        return $this->hasMany(ExamPeriodProctor::class);
    }
} 