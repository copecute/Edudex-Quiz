<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('test_shift_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_shift_id')->constrained()->onDelete('cascade');
            $table->foreignId('test_session_subject_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Một môn thi chỉ được thêm một lần trong một ca thi
            $table->unique(['test_shift_id', 'test_session_subject_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('test_shift_subjects');
    }
}; 