<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestShift extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'start_time',
        'end_time',
        'test_session_id',
        'is_active'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function getTestDateAttribute()
    {
        return $this->start_time;
    }

    public function testSession()
    {
        return $this->belongsTo(TestSession::class);
    }

    public function testRooms()
    {
        return $this->belongsToMany(TestRoom::class, 'test_session_rooms')
                    ->withTimestamps();
    }

    public function testSessionSubjects()
    {
        return $this->belongsToMany(TestSessionSubject::class, 'test_shift_subjects')
                    ->withTimestamps();
    }

    public function subjects()
    {
        return $this->hasManyThrough(
            Subject::class,
            TestSessionSubject::class,
            'id',
            'id',
            'test_session_subject_id',
            'subject_id'
        );
    }

    // Helper method để lấy danh sách tên môn thi
    public function getSubjectNamesAttribute()
    {
        return $this->testSessionSubjects->map(function($testSessionSubject) {
            return $testSessionSubject->subject->name;
        })->join(', ');
    }

    // Helper method để kiểm tra xem ca thi có chứa môn học cụ thể không
    public function hasSubject($subjectId)
    {
        return $this->testSessionSubjects->contains(function($testSessionSubject) use ($subjectId) {
            return $testSessionSubject->subject_id == $subjectId;
        });
    }

    public function getStatusText()
    {
        if (!$this->is_active) {
            return 'Đã khóa';
        }
        
        $now = now();
        if ($now < $this->start_time) {
            return 'Chưa bắt đầu';
        }
        if ($now > $this->end_time) {
            return 'Đã kết thúc';
        }
        return 'Đang diễn ra';
    }

    public function getStatusColor()
    {
        if (!$this->is_active) {
            return 'secondary';
        }
        
        $now = now();
        if ($now < $this->start_time) {
            return 'info';
        }
        if ($now > $this->end_time) {
            return 'danger';
        }
        return 'success';
    }
} 