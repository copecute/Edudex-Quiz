<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Tên kỳ thi');
            $table->text('description')->nullable()->comment('Mô tả');
            $table->dateTime('start_time')->comment('Thời gian bắt đầu');
            $table->dateTime('end_time')->comment('Thời gian kết thúc');
            $table->boolean('is_active')->default(true)->comment('Trạng thái');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_periods');
    }
}; 