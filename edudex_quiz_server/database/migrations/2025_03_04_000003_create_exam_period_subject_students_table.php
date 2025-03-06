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
            $table->foreignId('exam_period_id')->constrained('exam_periods')->onDelete('cascade')->comment('ID kỳ thi');
            $table->foreignId('exam_period_subject_id')->constrained('exam_period_subjects')->onDelete('cascade')->comment('ID môn thi trong kỳ thi');
            $table->string('exam_code')->unique()->comment('Số báo danh');
            $table->string('student_code')->comment('Mã sinh viên');
            $table->string('full_name')->comment('Họ và tên thí sinh');
            $table->string('phone')->nullable()->comment('Số điện thoại');
            $table->text('address')->nullable()->comment('Địa chỉ');
            $table->date('birthday')->nullable()->comment('Ngày sinh');
            $table->boolean('gender')->default(true)->comment('true: Nam, false: Nữ');
            $table->timestamps();

            // Index để tăng tốc tìm kiếm
            $table->index('exam_code');
            $table->index('student_code');
            $table->index('full_name');
            $table->index('exam_period_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_period_subject_students');
    }
};