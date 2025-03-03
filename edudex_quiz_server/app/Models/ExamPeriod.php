<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
            return $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
        }
        return $query;
    }

    // Scope để lọc theo trạng thái
    public function scopeActive($query, $status = null)
    {
        if ($status !== null) {
            return $query->where('is_active', $status);
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