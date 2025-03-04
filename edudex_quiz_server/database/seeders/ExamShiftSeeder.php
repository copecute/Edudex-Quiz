<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamShift;
use App\Models\ExamPeriod;

class ExamShiftSeeder extends Seeder
{
    public function run(): void
    {
        $examPeriods = ExamPeriod::all();

        foreach ($examPeriods as $period) {
            // Ca sáng
            ExamShift::create([
                'exam_period_id' => $period->id,
                'name' => 'Ca 1 - Sáng',
                'description' => 'Ca thi buổi sáng',
                'start_time' => $period->start_time->copy()->setTime(7, 30),
                'end_time' => $period->start_time->copy()->setTime(9, 30),
                'is_active' => true
            ]);

            // Ca chiều
            ExamShift::create([
                'exam_period_id' => $period->id,
                'name' => 'Ca 2 - Chiều',
                'description' => 'Ca thi buổi chiều',
                'start_time' => $period->start_time->copy()->setTime(13, 30),
                'end_time' => $period->start_time->copy()->setTime(15, 30),
                'is_active' => true
            ]);
        }
    }
} 