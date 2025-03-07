<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamShiftRoom extends Model
{
    protected $fillable = [
        'exam_shift_id',
        'exam_period_room_id',
        'exam_period_subject_id',
        'exam_period_proctor_id'
    ];

    public function examPeriodRoom()
    {
        return $this->belongsTo(ExamPeriodRoom::class);
    }

    public function examPeriodSubject()
    {
        return $this->belongsTo(ExamPeriodSubject::class);
    }

    public function examShift()
    {
        return $this->belongsTo(ExamShift::class);
    }
} 