<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestShiftSubject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'test_shift_id',
        'test_session_subject_id'
    ];

    public function testShift()
    {
        return $this->belongsTo(TestShift::class);
    }

    public function testSessionSubject()
    {
        return $this->belongsTo(TestSessionSubject::class);
    }
} 