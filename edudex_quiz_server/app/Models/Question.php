<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'content',
        'link_media',
        'subject_code',
        'difficulty'
    ];

    protected $with = ['answers', 'tags']; // Eager load mặc định

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_code', 'code');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    // Helper method để lấy đáp án đúng
    public function getCorrectAnswer()
    {
        return $this->answers()->where('is_correct', true)->first();
    }

    // Scope để lọc theo độ khó
    public function scopeByDifficulty($query, $difficulty)
    {
        if ($difficulty) {
            return $query->where('difficulty', $difficulty);
        }
        return $query;
    }

    // Scope để tìm kiếm
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where('content', 'like', "%{$search}%");
        }
        return $query;
    }
} 