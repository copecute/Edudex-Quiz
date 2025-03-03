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
            $table->string('code')->unique()->comment('Mã môn học');
            $table->string('name')->comment('Tên môn học');
            $table->integer('credits')->comment('Số tín chỉ');
            $table->foreignId('major_id')->constrained('majors')->onDelete('cascade');
            $table->text('description')->nullable()->comment('Mô tả');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subjects');
    }
}; 