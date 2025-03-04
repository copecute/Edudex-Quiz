<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_period_id')->constrained('exam_periods')->onDelete('cascade');
            $table->string('name')->comment('Tên ca thi');
            $table->text('description')->nullable()->comment('Mô tả');
            $table->dateTime('start_time')->comment('Thời gian bắt đầu');
            $table->dateTime('end_time')->comment('Thời gian kết thúc');
            $table->boolean('is_active')->default(true)->comment('Trạng thái');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_shifts');
    }
}; 