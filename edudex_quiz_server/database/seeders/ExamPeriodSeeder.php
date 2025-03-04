<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamPeriod;

class ExamPeriodSeeder extends Seeder
{
    public function run(): void
    {
        ExamPeriod::create([
            'name' => 'Kỳ thi học kỳ 1 năm học 2023-2024',
            'description' => 'Kỳ thi kết thúc học kỳ 1 năm học 2023-2024',
            'start_time' => '2024-01-01 07:00:00',
            'end_time' => '2024-01-31 17:00:00',
            'is_active' => true
        ]);

        ExamPeriod::create([
            'name' => 'Kỳ thi học kỳ 2 năm học 2023-2024',
            'description' => 'Kỳ thi kết thúc học kỳ 2 năm học 2023-2024',
            'start_time' => '2024-06-01 07:00:00',
            'end_time' => '2024-06-30 17:00:00',
            'is_active' => true
        ]);
    }
} 