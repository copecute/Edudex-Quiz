<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestPaper extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description', 
        'duration',
        'total_questions',
        'subject_id'
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'test_paper_tags')
                    ->withPivot(['num_questions', 'easy_rate', 'medium_rate', 'hard_rate'])
                    ->withTimestamps();
    }

    // Tính tỉ lệ tổng của đề thi
    public function getDifficultyRatesAttribute()
    {
        $totalQuestions = $this->total_questions;
        if ($totalQuestions == 0) return [
            'easy' => 0,
            'medium' => 0,
            'hard' => 0
        ];

        $rates = [
            'easy' => 0,
            'medium' => 0,
            'hard' => 0
        ];

        foreach ($this->tags as $tag) {
            $tagQuestions = $tag->pivot->num_questions;
            $weight = $tagQuestions / $totalQuestions;

            $rates['easy'] += $tag->pivot->easy_rate * $weight;
            $rates['medium'] += $tag->pivot->medium_rate * $weight;
            $rates['hard'] += $tag->pivot->hard_rate * $weight;
        }

        return $rates;
    }

    // Thêm relationship với questions
    public function questions()
    {
        // Lấy câu hỏi thuộc cùng môn học với đề thi
        return $this->hasManyThrough(
            Question::class,
            Subject::class,
            'id', // Khóa ngoại trên bảng subjects
            'subject_id', // Khóa ngoại trên bảng questions
            'subject_id', // Khóa local trên bảng test_papers
            'id' // Khóa primary trên bảng subjects
        );
    }

    public function testSessionSubjects()
    {
        return $this->hasMany(TestSessionSubject::class);
    }
} 