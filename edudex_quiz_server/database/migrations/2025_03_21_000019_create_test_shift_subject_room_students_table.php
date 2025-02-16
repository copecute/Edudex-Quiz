<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_shift_subject_room_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_shift_subject_room_id')
                  ->constrained('test_shift_subject_rooms', 'id', 'fk_tssr_students')
                  ->cascadeOnDelete();
            $table->foreignId('student_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['test_shift_subject_room_id', 'student_id'], 'unique_tssr_student');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_shift_subject_room_students');
    }
}; 