<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_period_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_period_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Không cho phép một phòng được sử dụng nhiều lần trong cùng một kỳ thi
            $table->unique(['exam_period_id', 'room_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_period_rooms');
    }
}; 