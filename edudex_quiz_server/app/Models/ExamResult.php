<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    protected $guarded = [];

    public function examPeriod()
    {
        return $this->belongsTo(ExamPeriod::class);
    }

    public function examShift()
    {
        return $this->belongsTo(ExamShift::class);
    }

    public function examPeriodSubject()
    {
        return $this->belongsTo(ExamPeriodSubject::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function examPeriodRoom()
    {
        return $this->belongsTo(ExamPeriodRoom::class);
    }

    public function proctor()
    {
        return $this->belongsTo(ExamPeriodProctor::class, 'exam_period_proctor_id');
    }

    public function student()
    {
        return $this->belongsTo(ExamPeriodSubjectStudent::class, 'exam_period_subject_student_id');
    }
} 