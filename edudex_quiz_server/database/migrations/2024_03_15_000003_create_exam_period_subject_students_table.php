<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_period_subject_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_period_subject_id')->constrained('exam_period_subjects')->onDelete('cascade');
            $table->string('exam_code')->unique()->comment('Số báo danh');
            $table->string('student_code')->comment('Mã sinh viên');
            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->date('birthday')->nullable();
            $table->boolean('gender')->default(true)->comment('true: Nam, false: Nữ');
            $table->string('avatar')->nullable();
            $table->timestamps();

            // Index để tăng tốc tìm kiếm
            $table->index('exam_code');
            $table->index('student_code');
            $table->index('full_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_period_subject_students');
    }
}; 