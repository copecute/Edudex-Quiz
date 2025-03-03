<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            [
                'code' => 'INT1234',
                'name' => 'Lập trình Java',
                'credits' => 3,
                'major_id' => 1, // CNTT
                'description' => 'Môn học về ngôn ngữ lập trình Java',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'INT1235',
                'name' => 'Cơ sở dữ liệu',
                'credits' => 3,
                'major_id' => 1, // CNTT
                'description' => 'Môn học về cơ sở dữ liệu',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'INT1236',
                'name' => 'Lập trình Web',
                'credits' => 3,
                'major_id' => 2, // KTPM
                'description' => 'Môn học về phát triển ứng dụng web',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'INT1237',
                'name' => 'Phân tích thiết kế hệ thống',
                'credits' => 3,
                'major_id' => 3, // HTTT
                'description' => 'Môn học về phân tích và thiết kế hệ thống thông tin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ELE1234',
                'name' => 'Kỹ thuật số',
                'credits' => 3,
                'major_id' => 4, // DTVT
                'description' => 'Môn học về kỹ thuật số trong điện tử',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('subjects')->insert($subjects);
    }
} 