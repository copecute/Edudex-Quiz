<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamTag extends Model
{
    protected $fillable = [
        'exam_id',
        'tag_id',
        'num_questions',
        'easy_rate',
        'medium_rate',
        'hard_rate'
    ];

    protected $casts = [
        'easy_rate' => 'float',
        'medium_rate' => 'float',
        'hard_rate' => 'float'
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }

    // Helper method để tính số câu hỏi theo độ khó
    public function getQuestionsByDifficulty()
    {
        return [
            'easy' => round($this->num_questions * $this->easy_rate / 100),
            'medium' => round($this->num_questions * $this->medium_rate / 100),
            'hard' => round($this->num_questions * $this->hard_rate / 100)
        ];
    }
} 