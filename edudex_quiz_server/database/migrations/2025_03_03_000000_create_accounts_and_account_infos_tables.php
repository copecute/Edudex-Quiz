<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tạo bảng 'accounts' lưu trữ thông tin tài khoản
        Schema::create('accounts', function (Blueprint $table) {
            $table->id(); // Khóa chính tự động tăng
            $table->string('username')->unique()->comment('Tên người dùng');
            $table->string('email')->unique()->comment('Địa chỉ email');
            $table->string('password')->comment('Mật khẩu');
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('role')->default(0)->comment('0: CBCT, 1: Giáo viên, 2: Admin');
            $table->timestamps();  // Thời gian tạo và cập nhật tự động
        });

        // Tạo bảng 'account_infos' để lưu trữ thông tin của tài khoản người dùng
        Schema::create('account_infos', function (Blueprint $table) {
            $table->id(); // Khóa chính tự động tăng
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade'); // Khóa ngoại tham chiếu bảng 'accounts', xóa tài khoản thì xóa thông tin chi tiết
            $table->string('fullName')->comment('Họ và tên=');
            $table->date('birthday')->nullable()->comment('Ngày sinh');
            $table->string('avatar')->nullable()->comment('Ảnh đại diện');
            $table->boolean('gender')->comment('Giới tính');
            $table->string('phoneNumber')->comment('Số điện thoại');
            $table->string('address')->comment('Địa chỉ');
            $table->timestamps(); // Thời gian tạo và cập nhật tự động
        });

        // tạo tài khoản admin
        $accountId = DB::table('accounts')->insertGetId([
            'username' => 'admin',
            'email' => 'admin@minhgiang.pro',
            'password' => bcrypt('123'),
            'role' => 2,
        ]);

        // Tự động thêm thông tin cho tài khoản vừa tạo
        DB::table('account_infos')->insert([
            'account_id' => $accountId,
            'fullName' => 'Đàm Minh Giang',
            'birthday' => '2001-10-29',
            'gender' => true,
            'phoneNumber' => '0333332444',
            'address' => 'Hà Nội',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_infos');
        Schema::dropIfExists('accounts');
    }
};
