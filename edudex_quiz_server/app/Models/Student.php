<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', // Mã sinh viên
        'name',
        'email',
        'phone',
        'address',
        'birthday',
        'gender', // 1: Nam, 0: Nữ
        'status', // 1: Đang học, 0: Đã nghỉ
        'avatar'
    ];

    protected $casts = [
        'birthday' => 'date',
        'gender' => 'boolean',
        'status' => 'boolean',
    ];

    public function majors()
    {
        return $this->belongsToMany(Major::class, 'student_major')
                    ->withTimestamps()
                    ->withPivot('is_main'); // Ngành chính hay phụ
    }

    // Thêm accessor để lấy URL ảnh avatar
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('upload/avatar/students/' . $this->avatar);
        }
        return asset('images/default-avatar.jpg');
    }

    public function testSessionSubjects()
    {
        return $this->belongsToMany(TestSessionSubject::class, 'test_session_subject_students')
                    ->using(TestSessionSubjectStudent::class)
                    ->withPivot(['exam_code', 'test_shift_subject_room_id'])
                    ->withTimestamps();
    }
} 