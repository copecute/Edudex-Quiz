<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamShift extends Model
{
    protected $fillable = [
        'exam_period_id',
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

    // Relationship với kỳ thi
    public function examPeriod()
    {
        return $this->belongsTo(ExamPeriod::class);
    }

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

    // Scope để lọc theo kỳ thi
    public function scopeByPeriod($query, $periodId = null)
    {
        if ($periodId) {
            return $query->where('exam_period_id', $periodId);
        }
        return $query;
    }
} 