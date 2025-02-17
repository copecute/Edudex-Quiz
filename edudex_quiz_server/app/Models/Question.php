<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'content',
        'type',
        'level',
        'score',
        'explanation',
        'image_url'
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function getCorrectAnswer()
    {
        return $this->answers()->where('is_correct', true)->first();
    }

    public function getLevelText()
    {
        return match($this->level) {
            1 => 'Dễ',
            2 => 'Trung bình',
            3 => 'Khó',
            default => 'Không xác định'
        };
    }
} 