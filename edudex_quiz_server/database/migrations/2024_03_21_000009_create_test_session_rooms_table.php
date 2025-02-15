<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('test_session_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_shift_id')->constrained()->onDelete('cascade');
            $table->foreignId('test_room_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Một phòng thi chỉ có thể được sử dụng một lần trong một ca thi
            $table->unique(['test_shift_id', 'test_room_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('test_session_rooms');
    }
}; 