<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_shift_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_shift_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_period_room_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_period_subject_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('exam_period_proctor_id')->nullable()
                  ->constrained()
                  ->onDelete('set null');
            $table->timestamps();

            // một phòng thi chỉ được phân công một lần trong một ca thi
            $table->unique(['exam_shift_id', 'exam_period_room_id'], 'unique_shift_room');
            
            // unique constraint để CBCT không thể coi nhiều phòng trong cùng ca thi
            $table->unique(['exam_shift_id', 'exam_period_proctor_id'], 'unique_proctor_per_shift');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_shift_rooms');
    }
}; 