<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy danh sách môn học
        $subjects = DB::table('subjects')->get();

        foreach ($subjects as $subject) {
            // Tạo 50 câu hỏi cho mỗi môn học
            for ($i = 1; $i <= 50; $i++) {
                // Độ khó ngẫu nhiên
                $difficulty = ['easy', 'medium', 'hard'][rand(0, 2)];
                
                // Thêm câu hỏi
                $questionId = DB::table('questions')->insertGetId([
                    'content' => "Câu hỏi số {$i} của môn {$subject->name}",
                    'subject_code' => $subject->code,
                    'difficulty' => $difficulty,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Thêm 4 câu trả lời cho mỗi câu hỏi
                $correctAnswer = rand(0, 3); // Chọn ngẫu nhiên đáp án đúng
                for ($j = 0; $j < 4; $j++) {
                    DB::table('answers')->insert([
                        'question_id' => $questionId,
                        'content' => "Đáp án " . chr(65 + $j) . " của câu hỏi {$i}",
                        'is_correct' => ($j === $correctAnswer),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Thêm liên kết với tag
                // Lấy ngẫu nhiên một tag của môn học này
                $tag = DB::table('tags')
                    ->where('subject_code', $subject->code)
                    ->inRandomOrder()
                    ->first();

                if ($tag) {
                    DB::table('question_tag')->insert([
                        'question_id' => $questionId,
                        'tag_id' => $tag->id,
                    ]);
                }
            }
        }
    }
} 