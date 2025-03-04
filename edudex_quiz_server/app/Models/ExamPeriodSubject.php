<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamPeriodSubject extends Model
{
    protected $fillable = [
        'exam_period_id',
        'subject_id',
        'exam_id'
    ];

    // Relationship với kỳ thi
    public function examPeriod()
    {
        return $this->belongsTo(ExamPeriod::class);
    }

    // Relationship với môn học
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    // Relationship với đề thi
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    // Relationship với thí sinh
    public function students()
    {
        return $this->hasMany(ExamPeriodSubjectStudent::class);
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