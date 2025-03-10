<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_period_room_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_period_id')->constrained('exam_periods')->onDelete('cascade');
            $table->foreignId('exam_period_subject_id')->constrained('exam_period_subjects')->onDelete('cascade');
            $table->foreignId('exam_period_subject_student_id')->constrained('exam_period_subject_students')->onDelete('cascade');
            $table->foreignId('exam_period_room_id')->constrained('exam_period_rooms')->onDelete('cascade');
            $table->foreignId('exam_shift_id')->constrained('exam_shifts')->onDelete('cascade');
            $table->integer('seat_number')->comment('Số ghế trong phòng');
            $table->timestamps();

            // Unique constraint để đảm bảo 1 thí sinh không thể được xếp vào nhiều phòng trong cùng 1 ca thi
            $table->unique(['exam_shift_id', 'exam_period_subject_student_id'], 'unique_student_shift');
            
            // Unique constraint để đảm bảo không có 2 thí sinh ngồi cùng 1 ghế trong 1 phòng
            $table->unique(['exam_period_room_id', 'seat_number', 'exam_shift_id'], 'unique_seat_room_shift');

            // Unique constraint mới để đảm bảo mỗi thí sinh chỉ được phân công 1 lần trong 1 môn thi
            $table->unique(
                ['exam_period_id', 'exam_period_subject_id', 'exam_period_subject_student_id'],
                'unique_student_subject_exam'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_period_room_students');
    }
}; 