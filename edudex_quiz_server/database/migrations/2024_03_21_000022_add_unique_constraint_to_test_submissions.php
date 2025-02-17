<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('test_submissions', function (Blueprint $table) {
            $table->unique(['test_session_id', 'student_id', 'subject_id'], 'unique_student_subject_per_session');
        });
    }

    public function down()
    {
        Schema::table('test_submissions', function (Blueprint $table) {
            $table->dropUnique('unique_student_subject_per_session');
        });
    }
}; 