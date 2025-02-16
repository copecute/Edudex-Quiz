<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('test_papers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('duration'); // Thời gian làm bài (phút)
            $table->integer('total_questions'); // Tổng số câu hỏi
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->decimal('easy_rate', 5, 2)->default(50.00); // Tỉ lệ câu dễ (%)
            $table->decimal('medium_rate', 5, 2)->default(30.00); // Tỉ lệ câu trung bình (%)
            $table->decimal('hard_rate', 5, 2)->default(20.00); // Tỉ lệ câu khó (%)
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('test_paper_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_paper_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->integer('num_questions'); // Số câu hỏi cho tag này
            $table->decimal('easy_rate', 5, 2); // Tỉ lệ câu dễ (%)
            $table->decimal('medium_rate', 5, 2); // Tỉ lệ câu trung bình (%)
            $table->decimal('hard_rate', 5, 2); // Tỉ lệ câu khó (%)
            $table->timestamps();

            $table->unique(['test_paper_id', 'tag_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('test_paper_tags');
        Schema::dropIfExists('test_papers');
    }
}; 
 