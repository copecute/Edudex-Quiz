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

    /**
     * Đếm số câu hỏi khả dụng thỏa mãn yêu cầu của đề thi
     * @return int
     */
    public function getAvailableQuestionsCount()
    {
        $availableQuestions = 0;
        
        foreach ($this->tags as $tag) {
            $query = Question::where('subject_id', $this->subject_id)
                ->whereHas('tags', function ($q) use ($tag) {
                    $q->where('tags.id', $tag->id);
                });

            // Đếm số câu hỏi theo độ khó cho mỗi tag
            $easyCount = (int) ($tag->pivot->num_questions * $tag->pivot->easy_rate / 100);
            $mediumCount = (int) ($tag->pivot->num_questions * $tag->pivot->medium_rate / 100);
            $hardCount = $tag->pivot->num_questions - $easyCount - $mediumCount;

            // Sử dụng level thay vì difficulty
            $easyAvailable = $query->where('level', 1)->count();    // level 1 = dễ
            $mediumAvailable = $query->where('level', 2)->count();  // level 2 = trung bình  
            $hardAvailable = $query->where('level', 3)->count();    // level 3 = khó

            // Lấy số câu hỏi nhỏ nhất có thể đáp ứng được
            $availableForTag = min([
                $easyCount > 0 ? (int)($easyAvailable / $easyCount) : PHP_INT_MAX,
                $mediumCount > 0 ? (int)($mediumAvailable / $mediumCount) : PHP_INT_MAX,
                $hardCount > 0 ? (int)($hardAvailable / $hardCount) : PHP_INT_MAX
            ]) * $tag->pivot->num_questions;

            $availableQuestions += $availableForTag;
        }

        return $availableQuestions;
    }

    /**
     * Lấy thông tin chi tiết về số câu hỏi khả dụng
     * @return array
     */
    public function getAvailableQuestionsDetail()
    {
        $details = [];
        $totalTagQuestions = 0; // Tổng số câu hỏi từ các tag
        $totalAvailable = 0;
        
        // Đếm tổng số câu hỏi theo tag
        foreach ($this->tags as $tag) {
            $totalTagQuestions += $tag->pivot->num_questions;
        }
        
        // Số câu hỏi random không cần tag
        $randomQuestions = $this->total_questions - $totalTagQuestions;
        
        foreach ($this->tags as $tag) {
            // Lấy tất cả câu hỏi cho tag này một lần
            $questions = Question::where('subject_id', $this->subject_id)
                ->whereHas('tags', function ($q) use ($tag) {
                    $q->where('tags.id', $tag->id);
                })
                ->get();

            // Đếm số câu hỏi theo độ khó
            $easyAvailable = $questions->where('level', 1)->count();
            $mediumAvailable = $questions->where('level', 2)->count();
            $hardAvailable = $questions->where('level', 3)->count();

            // Đếm số câu hỏi cần thiết theo độ khó cho tag
            $easyRequired = (int) ($tag->pivot->num_questions * $tag->pivot->easy_rate / 100);
            $mediumRequired = (int) ($tag->pivot->num_questions * $tag->pivot->medium_rate / 100);
            $hardRequired = $tag->pivot->num_questions - $easyRequired - $mediumRequired;

            // Tính số câu hỏi khả dụng thực tế cho mỗi độ khó
            $easyActual = min($easyAvailable, $easyRequired);
            $mediumActual = min($mediumAvailable, $mediumRequired);
            $hardActual = min($hardAvailable, $hardRequired);

            // Tổng số câu hỏi khả dụng cho tag này
            $availableForTag = $easyActual + $mediumActual + $hardActual;
            $totalAvailable += $availableForTag;

            $details[$tag->name] = [
                'total_required' => $tag->pivot->num_questions,
                'total_available' => $availableForTag,
                'easy' => [
                    'required' => $easyRequired,
                    'available' => min($easyAvailable, $easyRequired)
                ],
                'medium' => [
                    'required' => $mediumRequired,
                    'available' => min($mediumAvailable, $mediumRequired)
                ],
                'hard' => [
                    'required' => $hardRequired,
                    'available' => min($hardAvailable, $hardRequired)
                ]
            ];
        }

        // Đếm số câu hỏi có sẵn cho phần random
        if ($randomQuestions > 0) {
            $randomAvailable = Question::where('subject_id', $this->subject_id)->count();
            $totalAvailable += min($randomQuestions, $randomAvailable);
            
            // Thêm thông tin về câu hỏi random
            $details['Random'] = [
                'total_required' => $randomQuestions,
                'total_available' => min($randomQuestions, $randomAvailable),
                'easy' => ['required' => 0, 'available' => 0],
                'medium' => ['required' => 0, 'available' => 0],
                'hard' => ['required' => 0, 'available' => 0]
            ];
        }

        return [
            'total_available' => $totalAvailable,
            'total_required' => $this->total_questions,
            'tags' => $details
        ];
    }
} 