<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
    public function examPeriod(): BelongsTo
    {
        return $this->belongsTo(ExamPeriod::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(ExamPeriodSubject::class, 'exam_period_subject_shifts')
                    ->withTimestamps();
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(ExamPeriodRoom::class, 'exam_shift_rooms')
                    ->withPivot('id')
                    ->withTimestamps();
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