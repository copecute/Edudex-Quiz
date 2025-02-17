<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestSessionSubject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'test_session_id',
        'subject_id',
        'test_paper_id'
    ];

    public function testSession()
    {
        return $this->belongsTo(TestSession::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function testShifts()
    {
        return $this->belongsToMany(TestShift::class, 'test_shift_subjects')
                    ->withTimestamps();
    }

    public function testShiftSubjectRooms()
    {
        return $this->hasMany(TestShiftSubjectRoom::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'test_session_subject_students')
                    ->using(TestSessionSubjectStudent::class)
                    ->withPivot(['exam_code', 'test_shift_subject_room_id'])
                    ->withTimestamps();
    }

    public function testPaper()
    {
        return $this->belongsTo(TestPaper::class);
    }
} 