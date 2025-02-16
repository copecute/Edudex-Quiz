<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('test_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');        // Tên cơ sở
            $table->string('address');     // Địa chỉ
            $table->text('description')->nullable(); // Mô tả
            $table->boolean('is_active')->default(true); // Trạng thái
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('test_locations');
    }
}; 