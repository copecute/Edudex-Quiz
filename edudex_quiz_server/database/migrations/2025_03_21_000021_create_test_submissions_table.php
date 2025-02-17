<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('test_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('test_paper_id')->constrained('test_papers');
            $table->foreignId('test_session_subject_id')->constrained('test_session_subjects');
            $table->foreignId('test_session_id')->constrained('test_sessions');
            $table->foreignId('subject_id')->constrained('subjects');
            $table->decimal('score', 5, 2)->nullable(); // Điểm từ 0-10, 2 số thập phân
            $table->string('submission_file')->nullable(); // Đường dẫn đến file .edudex
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->text('notes')->nullable(); // Ghi chú về bài làm
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('test_submissions');
    }
}; 