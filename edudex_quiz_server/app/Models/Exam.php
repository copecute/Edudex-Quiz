<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'name',
        'description',
        'duration',
        'total_questions',
        'subject_code',
        'easy_rate',
        'medium_rate',
        'hard_rate'
    ];

    protected $casts = [
        'easy_rate' => 'float',
        'medium_rate' => 'float',
        'hard_rate' => 'float'
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_code', 'code');
    }

    public function examTags()
    {
        return $this->hasMany(ExamTag::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'exam_tags')
            ->withPivot(['num_questions', 'easy_rate', 'medium_rate', 'hard_rate'])
            ->withTimestamps();
    }

    // Helper method để tính toán số câu hỏi theo độ khó cho phần không có tag
    public function calculateRemainingQuestionsByDifficulty()
    {
        // Tổng số câu hỏi đã phân bổ cho các tag
        $assignedQuestions = $this->examTags->sum('num_questions');
        
        // Số câu hỏi còn lại cần phân bổ
        $remainingQuestions = $this->total_questions - $assignedQuestions;

        if ($remainingQuestions <= 0) {
            return [
                'easy' => 0,
                'medium' => 0,
                'hard' => 0
            ];
        }

        // Tính số câu hỏi cho từng độ khó
        return [
            'easy' => round($remainingQuestions * $this->easy_rate / 100),
            'medium' => round($remainingQuestions * $this->medium_rate / 100),
            'hard' => round($remainingQuestions * $this->hard_rate / 100)
        ];
    }
} 