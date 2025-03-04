<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_period_proctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_period_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Không cho phép một tài khoản làm CBCT nhiều lần trong cùng một kỳ thi
            $table->unique(['exam_period_id', 'account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_period_proctors');
    }
}; 