<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'credits',
        'major_id'
    ];

    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    public function testSessions()
    {
        return $this->belongsToMany(TestSession::class, 'test_session_subjects')
                    ->withTimestamps();
    }

    public function testSessionSubjects()
    {
        return $this->hasMany(TestSessionSubject::class);
    }

    public function testShifts()
    {
        return $this->hasManyThrough(
            TestShift::class,
            TestSessionSubject::class,
            'subject_id',
            'id'
        )->whereHas('testSessionSubjects');
    }

    public function testShiftSubjectRooms()
    {
        return $this->hasManyThrough(
            TestShiftSubjectRoom::class,
            TestSessionSubject::class,
            'subject_id',
            'test_session_subject_id'
        );
    }
} 