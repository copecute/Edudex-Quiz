<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamPeriodSubjectStudent extends Model
{
    protected $fillable = [
        'exam_period_id',
        'exam_period_subject_id',
        'account_id',
        'exam_code',
        'student_code',
        'full_name',
        'phone',
        'address',
        'birthday',
        'gender',
        'avatar'
    ];

    protected $casts = [
        'birthday' => 'date',
        'gender' => 'boolean'
    ];

    // Relationship với môn thi
    public function examPeriodSubject()
    {
        return $this->belongsTo(ExamPeriodSubject::class);
    }

    // Relationship với kỳ thi
    public function examPeriod()
    {
        return $this->belongsTo(ExamPeriod::class);
    }

    // Relationship với kỳ thi
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    // Scope để tìm kiếm
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function($q) use ($search) {
                $q->where('exam_code', 'like', "%{$search}%")
                  ->orWhere('student_code', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    // Scope để lọc theo môn thi
    public function scopeBySubject($query, $subjectId = null)
    {
        if ($subjectId) {
            return $query->whereHas('examPeriodSubject', function($q) use ($subjectId) {
                $q->where('id', $subjectId);
            });
        }
        return $query;
    }

    // Tự động sinh số báo danh
    public static function generateExamCode($examPeriodSubjectId)
    {
        // Lấy thông tin môn thi và kiểm tra
        $examPeriodSubject = ExamPeriodSubject::find($examPeriodSubjectId);
        
        if (!$examPeriodSubject) {
            throw new \Exception("Không tìm thấy môn thi với ID: " . $examPeriodSubjectId);
        }

        // Debug chi tiết thông tin
        \Log::info('ExamPeriodSubject detail:', [
            'id' => $examPeriodSubject->id,
            'exam_period_id' => $examPeriodSubject->exam_period_id,
            'subject_id' => $examPeriodSubject->subject_id,
            'raw' => $examPeriodSubject->toArray()
        ]);
        
        // Lấy số thứ tự lớn nhất hiện tại
        $lastStudent = self::where('exam_period_subject_id', $examPeriodSubjectId)
            ->orderBy('exam_code', 'desc')
            ->first();

        if (!$lastStudent) {
            $nextNumber = 1;
        } else {
            // Lấy 3 số cuối của mã cũ
            $lastNumber = (int) substr($lastStudent->exam_code, -3);
            $nextNumber = $lastNumber + 1;
        }

        // Format: {kỳ thi ID 2 số}{môn học ID 2 số}{số thứ tự 3 số}
        $examCode = sprintf(
            "%02d%02d%03d",
            $examPeriodSubject->exam_period_id,
            $examPeriodSubject->subject_id,
            $nextNumber
        );
        
        return $examCode;
    }
} 