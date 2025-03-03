<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tạo bảng exams
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('duration')->comment('Thời gian làm bài (phút)');
            $table->integer('total_questions');
            $table->string('subject_code');
            $table->foreign('subject_code')->references('code')->on('subjects');
            $table->decimal('easy_rate', 5, 2)->default(0);
            $table->decimal('medium_rate', 5, 2)->default(0);
            $table->decimal('hard_rate', 5, 2)->default(0);
            $table->timestamps();
        });

        // Tạo bảng exam_tags
        Schema::create('exam_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->integer('num_questions');
            $table->decimal('easy_rate', 5, 2)->default(0);
            $table->decimal('medium_rate', 5, 2)->default(0);
            $table->decimal('hard_rate', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_tags');
        Schema::dropIfExists('exams');
    }
}; 