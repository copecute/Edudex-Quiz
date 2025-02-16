<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * chạy migration
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->tinyInteger('role')->default(2); // 0: quản trị viên, 1: giáo viên, 2: cán bộ coi thi
            $table->timestamps();
        });

        Schema::create('user_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('fullName');
            $table->date('birthday')->nullable();
            $table->string('avatar')->nullable();
            $table->boolean('gender'); // true: nam, false: nữ
            $table->string('phoneNumber');
            $table->string('address');
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Tạo tài khoản admin mặc định
        $userId = DB::table('users')->insertGetId([
            'username' => 'admin',
            'email' => 'admin@edudex.edu.vn', 
            'password' => Hash::make('12345678'),
            'role' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Tạo thông tin admin mặc định
        DB::table('user_infos')->insert([
            'user_id' => $userId,
            'fullName' => 'Nguyễn Văn A',
            'birthday' => '2001-01-01',
            'gender' => true,
            'phoneNumber' => '0888889530',
            'address' => 'Hà Nội',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * hoàn tác migration
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('user_infos');
        Schema::dropIfExists('users');
    }
};
