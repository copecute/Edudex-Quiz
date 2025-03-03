<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Tạo bảng faculties (khoa)
        Schema::create('faculties', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Mã khoa');
            $table->string('name')->comment('Tên khoa');
            $table->text('description')->nullable()->comment('Mô tả');
            $table->timestamps();
        });

        // Tạo bảng majors (ngành)
        Schema::create('majors', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Mã ngành');
            $table->string('name')->comment('Tên ngành');
            $table->foreignId('faculty_id')->constrained('faculties')->onDelete('cascade');
            $table->text('description')->nullable()->comment('Mô tả');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('majors');
        Schema::dropIfExists('faculties');
    }
}; 