<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_period_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_period_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();

            // Thêm unique để tránh trùng lặp môn thi trong cùng một kỳ thi
            $table->unique(['exam_period_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_period_subjects');
    }
}; 