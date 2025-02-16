<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();  // Mã môn học
            $table->string('name');           // Tên môn học
            $table->text('description')->nullable(); // Mô tả
            $table->integer('credits')->default(3); // Số tín chỉ
            $table->foreignId('major_id')->constrained()->onDelete('cascade'); // Khóa ngoại liên kết với ngành
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subjects');
    }
}; 