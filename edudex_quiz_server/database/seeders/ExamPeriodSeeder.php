<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamPeriod;

class ExamPeriodSeeder extends Seeder
{
    public function run(): void
    {
        ExamPeriod::create([
            'name' => 'Kỳ thi học kỳ 1 năm học 2023-2025',
            'description' => 'Kỳ thi kết thúc học kỳ 1 năm học 2023-2025',
            'start_time' => '2025-01-01 07:00:00',
            'end_time' => '2025-01-31 17:00:00',
            'is_active' => true
        ]);

        ExamPeriod::create([
            'name' => 'Kỳ thi học kỳ 2 năm học 2023-2025',
            'description' => 'Kỳ thi kết thúc học kỳ 2 năm học 2023-2025',
            'start_time' => '2025-06-01 07:00:00',
            'end_time' => '2025-06-30 17:00:00',
            'is_active' => true
        ]);
    }
} 