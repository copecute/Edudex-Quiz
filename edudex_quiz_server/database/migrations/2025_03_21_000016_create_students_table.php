<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Mã sinh viên
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->date('birthday')->nullable();
            $table->boolean('gender')->default(1); // 1: Nam, 0: Nữ
            $table->boolean('status')->default(1); // 1: Đang học, 0: Đã nghỉ
            $table->string('avatar')->nullable(); // Thêm trường avatar
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('student_major', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('major_id')->constrained()->onDelete('cascade');
            $table->boolean('is_main')->default(false); // Ngành chính hay phụ
            $table->timestamps();

            $table->unique(['student_id', 'major_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_major');
        Schema::dropIfExists('students');
    }
}; 