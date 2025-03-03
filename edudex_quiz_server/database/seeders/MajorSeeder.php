<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MajorSeeder extends Seeder
{
    public function run(): void
    {
        $majors = [
            [
                'code' => 'CNT',
                'name' => 'Công nghệ thông tin',
                'faculty_id' => 1, // CNTT
                'description' => 'Chuyên ngành đào tạo về công nghệ thông tin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'KTPM',
                'name' => 'Kỹ thuật phần mềm',
                'faculty_id' => 1, // CNTT
                'description' => 'Chuyên ngành đào tạo về kỹ thuật phần mềm',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'HTTT',
                'name' => 'Hệ thống thông tin',
                'faculty_id' => 1, // CNTT
                'description' => 'Chuyên ngành đào tạo về hệ thống thông tin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'DTVT',
                'name' => 'Điện tử viễn thông',
                'faculty_id' => 2, // DTVT
                'description' => 'Chuyên ngành đào tạo về điện tử viễn thông',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'KTDT',
                'name' => 'Kỹ thuật điện tử',
                'faculty_id' => 3, // KTDT
                'description' => 'Chuyên ngành đào tạo về kỹ thuật điện tử',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('majors')->insert($majors);
    }
} 