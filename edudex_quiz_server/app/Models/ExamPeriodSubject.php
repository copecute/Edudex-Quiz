<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamPeriodSubject extends Model
{
    protected $fillable = [
        'exam_period_id',
        'subject_id',
        'exam_id'
    ];

    // Relationship với kỳ thi
    public function examPeriod(): BelongsTo
    {
        return $this->belongsTo(ExamPeriod::class);
    }

    // Relationship với môn học
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    // Relationship với đề thi
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    // Relationship với thí sinh
    public function students(): HasMany
    {
        return $this->hasMany(ExamPeriodSubjectStudent::class);
    }

    // Relationship với ca thi
    public function examShifts(): BelongsToMany
    {
        return $this->belongsToMany(ExamShift::class, 'exam_period_subject_shifts')
                    ->withTimestamps();
    }

    public function exams()
    {
        return $this->hasMany(Exam::class, 'exam_period_subject_id');
    }

    // Scope để tìm kiếm
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->whereHas('subject', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
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