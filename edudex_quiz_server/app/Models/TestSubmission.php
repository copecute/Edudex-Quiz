<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestSubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'test_paper_id', 
        'test_session_subject_id',
        'test_session_id',
        'subject_id',
        'score',
        'submission_file',
        'started_at',
        'submitted_at',
        'notes'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'score' => 'decimal:2'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function testPaper()
    {
        return $this->belongsTo(TestPaper::class, 'test_paper_id');
    }

    public function testSessionSubject()
    {
        return $this->belongsTo(TestSessionSubject::class, 'test_session_subject_id');
    }

    public function testSession()
    {
        return $this->belongsTo(TestSession::class, 'test_session_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
} 