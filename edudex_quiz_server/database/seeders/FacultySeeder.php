<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacultySeeder extends Seeder
{
    public function run(): void
    {
        $faculties = [
            [
                'code' => 'CNTT',
                'name' => 'Công nghệ thông tin',
                'description' => 'Khoa đào tạo các chuyên ngành về công nghệ thông tin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'DTVT',
                'name' => 'Điện tử viễn thông',
                'description' => 'Khoa đào tạo các chuyên ngành về điện tử và viễn thông',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'KTDT',
                'name' => 'Kỹ thuật điện tử',
                'description' => 'Khoa đào tạo các chuyên ngành về kỹ thuật điện tử',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('faculties')->insert($faculties);
    }
} 