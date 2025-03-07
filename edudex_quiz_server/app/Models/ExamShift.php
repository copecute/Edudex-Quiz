<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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
        return $this->belongsToMany(ExamPeriodSubject::class, 'exam_period_subject_shifts');
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(ExamPeriodRoom::class, 'exam_shift_rooms')
            ->withPivot(['id', 'exam_period_subject_id', 'exam_period_proctor_id'])
            ->with(['room.facility'])
            ->withTimestamps();
    }

    public function proctors()
    {
        return $this->hasManyThrough(
            ExamPeriodProctor::class,
            'exam_shift_rooms',
            'exam_shift_id', // Foreign key on exam_shift_rooms
            'id', // Local key on exam_period_proctors
            'id', // Local key on exam_shifts
            'exam_period_proctor_id' // Foreign key on exam_shift_rooms
        );
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