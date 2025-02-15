<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'start_time',
        'end_time',
        'is_active'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'test_session_subjects')
                    ->withCount(['testShifts' => function($query) {
                        $query->whereHas('testSessionSubjects', function($q) {
                            $q->where('test_session_id', $this->id);
                        });
                    }])
                    ->withTimestamps();
    }

    public function testShifts()
    {
        return $this->hasMany(TestShift::class);
    }

    public function testSessionSubjects()
    {
        return $this->hasMany(TestSessionSubject::class);
    }

    public function testRooms()
    {
        return $this->hasManyThrough(
            TestRoom::class,
            TestShift::class,
            'test_session_id', // Khóa ngoại trên bảng test_shifts
            'id', // Khóa chính trên bảng test_rooms
            'id', // Khóa chính trên bảng test_sessions
            'test_room_id' // Khóa ngoại trên bảng test_session_rooms
        );
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