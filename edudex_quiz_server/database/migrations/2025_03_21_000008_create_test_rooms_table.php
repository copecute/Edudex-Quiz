<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('test_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('code');        // Mã phòng
            $table->string('name');        // Tên phòng
            $table->integer('capacity');   // Số lượng thí sinh có thể thi
            $table->foreignId('test_location_id')->constrained()->onDelete('cascade'); // Thuộc cơ sở nào
            $table->boolean('is_active')->default(true); // Trạng thái
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('test_rooms');
    }
}; 