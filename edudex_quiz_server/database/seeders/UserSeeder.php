<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo 4 admin
        for ($i = 1; $i <= 4; $i++) {
            $userId = DB::table('users')->insertGetId([
                'username' => "admin{$i}",
                'email' => "admin{$i}@edudex.edu.vn",
                'password' => Hash::make('12345678'),
                'role' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('user_infos')->insert([
                'user_id' => $userId,
                'fullName' => "Admin {$i}",
                'birthday' => '2001-01-01',
                'gender' => rand(0, 1),
                'phoneNumber' => "098888953{$i}",
                'address' => 'Hà Nội',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Tạo 5 giáo viên
        for ($i = 1; $i <= 5; $i++) {
            $userId = DB::table('users')->insertGetId([
                'username' => "teacher{$i}",
                'email' => "teacher{$i}@edudex.edu.vn",
                'password' => Hash::make('12345678'),
                'role' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('user_infos')->insert([
                'user_id' => $userId,
                'fullName' => "Giáo viên {$i}",
                'birthday' => '1990-01-01',
                'gender' => rand(0, 1),
                'phoneNumber' => "098888954{$i}",
                'address' => 'Hà Nội',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Tạo 5 cán bộ coi thi
        for ($i = 1; $i <= 5; $i++) {
            $userId = DB::table('users')->insertGetId([
                'username' => "staff{$i}",
                'email' => "staff{$i}@edudex.edu.vn",
                'password' => Hash::make('12345678'),
                'role' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('user_infos')->insert([
                'user_id' => $userId,
                'fullName' => "Cán bộ coi thi {$i}",
                'birthday' => '1995-01-01',
                'gender' => rand(0, 1),
                'phoneNumber' => "098888955{$i}",
                'address' => 'Hà Nội',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
} 