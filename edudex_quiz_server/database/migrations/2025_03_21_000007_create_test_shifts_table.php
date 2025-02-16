<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('test_shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');        // Tên ca thi
            $table->text('description')->nullable(); // Mô tả
            $table->dateTime('start_time'); // Thời gian bắt đầu
            $table->dateTime('end_time');   // Thời gian kết thúc
            $table->foreignId('test_session_id')->constrained()->onDelete('cascade'); // Kỳ thi
            $table->boolean('is_active')->default(true); // Trạng thái ca thi
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('test_shifts');
    }
}; 