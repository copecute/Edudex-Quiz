<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestSessionSeeder extends Seeder
{
    public function run(): void
    {
        // Tắt kiểm tra khóa ngoại
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Xóa dữ liệu cũ
        DB::table('test_sessions')->truncate();
        
        // Bật lại kiểm tra khóa ngoại
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Tạo kỳ thi học kỳ 1 năm 2024-2025
        $testSession1 = DB::table('test_sessions')->insertGetId([
            'name' => 'Kỳ thi học kỳ 1 năm 2024-2025',
            'description' => 'Kỳ thi kết thúc học kỳ 1 năm học 2024-2025',
            'start_time' => now()->subMonths(2)->setTime(7, 0, 0),
            'end_time' => now()->subMonths(2)->addDays(11)->setTime(17, 0, 0),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Tạo kỳ thi học kỳ 2 năm 2024-2025
        $testSession2 = DB::table('test_sessions')->insertGetId([
            'name' => 'Kỳ thi học kỳ 2 năm 2024-2025',
            'description' => 'Kỳ thi kết thúc học kỳ 2 năm học 2024-2025',
            'start_time' => now()->subDay()->setTime(7, 0, 0),
            'end_time' => now()->addDays(2)->setTime(17, 0, 0),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Tạo kỳ thi học kỳ phụ năm 2024-2025
        $testSession3 = DB::table('test_sessions')->insertGetId([
            'name' => 'Kỳ thi học kỳ phụ năm 2024-2025',
            'description' => 'Kỳ thi học kỳ phụ dành cho sinh viên thi lại và học cải thiện',
            'start_time' => now()->addMonths(2)->setTime(7, 0, 0),
            'end_time' => now()->addMonths(2)->addDays(14)->setTime(17, 0, 0),
            'is_active' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Thêm môn học vào kỳ thi
        $subjects = DB::table('subjects')->get();
        foreach ($subjects as $subject) {
            // Thêm môn học vào kỳ thi HK1
            if (rand(0, 1)) {
                DB::table('test_session_subjects')->insert([
                    'test_session_id' => $testSession1,
                    'subject_id' => $subject->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Thêm môn học vào kỳ thi HK2
            if (rand(0, 1)) {
                DB::table('test_session_subjects')->insert([
                    'test_session_id' => $testSession2,
                    'subject_id' => $subject->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Thêm môn học vào kỳ thi HK phụ
            if (rand(0, 1)) {
                DB::table('test_session_subjects')->insert([
                    'test_session_id' => $testSession3,
                    'subject_id' => $subject->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
} 