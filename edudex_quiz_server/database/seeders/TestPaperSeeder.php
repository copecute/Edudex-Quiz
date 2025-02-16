<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestPaperSeeder extends Seeder
{
    public function run(): void
    {
        // Tắt kiểm tra khóa ngoại
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Xóa dữ liệu cũ
        DB::table('test_paper_tags')->truncate();
        DB::table('test_papers')->truncate();
        
        // Bật lại kiểm tra khóa ngoại
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Lấy danh sách môn học
        $subjects = DB::table('subjects')->get();

        foreach ($subjects as $subject) {
            // Tạo 3 đề thi cho mỗi môn học
            for ($i = 1; $i <= 3; $i++) {
                // Tạo đề thi
                $testPaperId = DB::table('test_papers')->insertGetId([
                    'name' => "Đề thi {$i} - {$subject->name}",
                    'description' => "Đề thi số {$i} môn {$subject->name}",
                    'duration' => 60, // 60 phút
                    'total_questions' => 40,
                    'subject_id' => $subject->id,
                    'easy_rate' => 50.00,
                    'medium_rate' => 30.00,
                    'hard_rate' => 20.00,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Lấy tags của môn học
                $tags = DB::table('tags')
                    ->where('subject_id', $subject->id)
                    ->get();

                // Tổng số câu hỏi đã phân bổ
                $totalAllocated = 0;
                $lastTagIndex = count($tags) - 1;

                // Phân bổ số câu hỏi cho từng tag
                foreach ($tags as $index => $tag) {
                    // Nếu là tag cuối, lấy số câu hỏi còn lại
                    $numQuestions = ($index == $lastTagIndex) 
                        ? 40 - $totalAllocated 
                        : rand(10, 15);

                    // Cập nhật tổng số câu hỏi đã phân bổ
                    $totalAllocated += $numQuestions;

                    // Tạo tỉ lệ ngẫu nhiên có tổng = 100
                    $rates = $this->generateRandomRates();

                    // Thêm vào bảng test_paper_tags
                    DB::table('test_paper_tags')->insert([
                        'test_paper_id' => $testPaperId,
                        'tag_id' => $tag->id,
                        'num_questions' => $numQuestions,
                        'easy_rate' => $rates['easy'],
                        'medium_rate' => $rates['medium'],
                        'hard_rate' => $rates['hard'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
    }

    private function generateRandomRates(): array
    {
        // Tạo tỉ lệ easy (40-60%)
        $easy = rand(40, 60);
        
        // Tạo tỉ lệ medium (20-40%)
        $medium = rand(20, min(40, 100 - $easy));
        
        // Hard là phần còn lại để đủ 100%
        $hard = 100 - $easy - $medium;

        return [
            'easy' => $easy,
            'medium' => $medium,
            'hard' => $hard
        ];
    }
} 