<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('majors', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();  // Mã ngành
            $table->string('name');           // Tên ngành
            $table->text('description')->nullable(); // Mô tả
            $table->foreignId('faculty_id')->constrained()->onDelete('cascade'); // Khóa ngoại liên kết với khoa
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('majors');
    }
}; 