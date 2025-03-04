<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_shift_room_proctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_shift_room_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_period_proctor_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Một CBCT chỉ được phân công một lần trong một phòng thi của một ca thi
            $table->unique(
                ['exam_shift_room_id', 'exam_period_proctor_id'],
                'unique_shift_room_proctor'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_shift_room_proctors');
    }
}; 