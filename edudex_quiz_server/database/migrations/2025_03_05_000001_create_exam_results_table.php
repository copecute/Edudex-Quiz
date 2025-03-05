<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_period_id')->constrained('exam_periods')->comment('ID kỳ thi');
            $table->foreignId('exam_shift_id')->constrained('exam_shifts')->comment('ID ca thi');
            $table->foreignId('exam_period_subject_id')->constrained('exam_period_subjects')->comment('ID môn thi');
            $table->foreignId('exam_id')->constrained('exams')->comment('ID đề thi');
            $table->foreignId('exam_period_room_id')->constrained('exam_period_rooms')->comment('ID phòng thi');
            $table->foreignId('exam_period_proctor_id')->constrained('exam_period_proctors')->comment('ID giám thị');
            $table->foreignId('exam_period_subject_student_id')->constrained('exam_period_subject_students')->comment('ID thí sinh');
            $table->string('exam_period_code')->comment('Mã kỳ thi');
            $table->string('exam_shift_code')->comment('Mã ca thi');
            $table->string('exam_subject_code')->comment('Mã môn thi');
            $table->string('exam_code')->comment('Mã đề thi');
            $table->string('room_code')->comment('Mã phòng');
            $table->string('proctor_code')->comment('Mã người coi thi');
            $table->string('student_code')->comment('Mã sinh viên');
            $table->integer('correct_answers')->comment('Số câu trả lời đúng');
            $table->integer('correct_answers_after_review')->nullable()->comment('Số câu đúng sau phúc khảo');
            $table->integer('total_questions')->comment('Tổng số câu hỏi');
            $table->decimal('score', 5, 2)->comment('Điểm số');
            $table->decimal('score_after_review', 5, 2)->nullable()->comment('Điểm sau phúc khảo');
            $table->text('note')->nullable()->comment('Ghi chú');
            $table->string('log_file')->comment('File log .edudex');
            $table->timestamps();

            // Index để tăng tốc tìm kiếm
            $table->index(['exam_period_id', 'exam_shift_id']);
            $table->index('exam_period_subject_student_id');
            $table->index('exam_period_code');
            $table->index('student_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_results');
    }
}; 