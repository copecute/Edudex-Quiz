<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_period_subject_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_period_subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_shift_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Một môn thi chỉ được phân công một lần trong một ca thi
            $table->unique(['exam_period_subject_id', 'exam_shift_id'], 'unique_subject_shift');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_period_subject_shifts');
    }
}; 