<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamPeriodRoomStudent extends Model
{
    protected $fillable = [
        'exam_period_id',
        'exam_shift_id', 
        'exam_period_room_id',
        'exam_period_subject_id',
        'exam_period_subject_student_id',
        'seat_number'
    ];

    public function examPeriod()
    {
        return $this->belongsTo(ExamPeriod::class);
    }

    public function examShift()
    {
        return $this->belongsTo(ExamShift::class);
    }

    public function examPeriodRoom()
    {
        return $this->belongsTo(ExamPeriodRoom::class);
    }

    public function student()
    {
        return $this->belongsTo(ExamPeriodSubjectStudent::class, 'exam_period_subject_student_id');
    }

    public function examPeriodSubject()
    {
        return $this->belongsTo(ExamPeriodSubject::class);
    }
} 