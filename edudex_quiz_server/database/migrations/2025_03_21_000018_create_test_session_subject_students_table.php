<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_session_subject_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_session_subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('exam_code')->nullable(); // Số báo danh
            $table->foreignId('test_shift_subject_room_id')->nullable()
                  ->constrained('test_shift_subject_rooms')
                  ->nullOnDelete(); // Phòng thi được phân công
            $table->timestamps();
            $table->softDeletes();

            // Một sinh viên chỉ được đăng ký 1 lần cho 1 môn thi
            $table->unique(['test_session_subject_id', 'student_id'], 'unique_student_subject');
            $table->unique(['test_session_subject_id', 'exam_code'], 'unique_exam_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_session_subject_students');
    }
}; 