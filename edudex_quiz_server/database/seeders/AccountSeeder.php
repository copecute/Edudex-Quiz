<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo 2 tài khoản admin
        for ($i = 1; $i <= 5; $i++) {
            $accountId = DB::table('accounts')->insertGetId([
                'username' => "admin{$i}",
                'email' => "admin{$i}@edudex.edu.vn",
                'password' => Hash::make('123456'),
                'role' => 2, // Admin
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('account_infos')->insert([
                'account_id' => $accountId,
                'fullName' => "Admin {$i}",
                'birthday' => '1985-01-01',
                'gender' => rand(0, 1),
                'phoneNumber' => "098765432{$i}",
                'address' => 'Hà Nội',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Tạo 5 tài khoản giáo viên
        for ($i = 1; $i <= 5; $i++) {
            $accountId = DB::table('accounts')->insertGetId([
                'username' => "giaovien{$i}",
                'email' => "giaovien{$i}@edudex.edu.vn",
                'password' => Hash::make('123456'),
                'role' => 1, // Giáo viên
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('account_infos')->insert([
                'account_id' => $accountId,
                'fullName' => "Giáo viên {$i}",
                'birthday' => '1985-01-01',
                'gender' => rand(0, 1),
                'phoneNumber' => "098765432{$i}",
                'address' => 'Hà Nội',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Tạo 5 tài khoản cán bộ coi thi
        for ($i = 1; $i <= 5; $i++) {
            $accountId = DB::table('accounts')->insertGetId([
                'username' => "canbo{$i}",
                'email' => "canbo{$i}@edudex.edu.vn",
                'password' => Hash::make('123456'),
                'role' => 0, // Cán bộ coi thi
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('account_infos')->insert([
                'account_id' => $accountId,
                'fullName' => "Cán bộ coi thi {$i}",
                'birthday' => '1990-01-01',
                'gender' => rand(0, 1),
                'phoneNumber' => "098765433{$i}",
                'address' => 'Hà Nội',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}