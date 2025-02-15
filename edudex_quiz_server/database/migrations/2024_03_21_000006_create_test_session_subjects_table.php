<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('test_session_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Một môn chỉ được thêm một lần trong một kỳ thi
            $table->unique(['test_session_id', 'subject_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('test_session_subjects');
    }
}; 