<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Bảng tags
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject_code');
            $table->foreign('subject_code')->references('code')->on('subjects')->onDelete('cascade');
            $table->timestamps();
            // Tag trùng tên trong cùng môn học sẽ là 1 tag
            $table->unique(['name', 'subject_code']);
        });

        // Bảng questions
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('content')->comment('Nội dung câu hỏi');
            $table->string('link_media')->nullable()->comment('Link media (hình ảnh/video)');
            $table->string('subject_code');
            $table->foreign('subject_code')->references('code')->on('subjects')->onDelete('cascade');
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->comment('Độ khó');
            $table->timestamps();
        });

        // Bảng pivot question_tag
        Schema::create('question_tag', function (Blueprint $table) {
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->primary(['question_id', 'tag_id']);
        });

        // Bảng answers
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->text('content')->comment('Nội dung đáp án');
            $table->string('link_media')->nullable()->comment('Link media (hình ảnh/video)');
            $table->boolean('is_correct')->default(false)->comment('Là đáp án đúng');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('answers');
        Schema::dropIfExists('question_tag');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('tags');
    }
}; 