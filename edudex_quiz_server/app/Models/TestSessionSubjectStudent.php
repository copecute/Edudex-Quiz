<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestSessionSubjectStudent extends Pivot
{
    use SoftDeletes;

    protected $table = 'test_session_subject_students';

    // Cho phép các trường này có thể được gán giá trị
    protected $fillable = [
        'exam_code',
        'test_shift_subject_room_id'
    ];

    // Khai báo relationship với TestShiftSubjectRoom
    public function testShiftSubjectRoom()
    {
        return $this->belongsTo(TestShiftSubjectRoom::class);
    }
} 