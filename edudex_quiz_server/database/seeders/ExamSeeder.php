<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        // Thêm đề thi
        $exams = [
            [
                'name' => 'Đề thi giữa kỳ Java',
                'description' => 'Đề thi giữa kỳ môn Lập trình Java',
                'duration' => 60,
                'total_questions' => 30,
                'subject_code' => 'INT1234',
                'easy_rate' => 40.00,
                'medium_rate' => 40.00,
                'hard_rate' => 20.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Đề thi cuối kỳ CSDL',
                'description' => 'Đề thi cuối kỳ môn Cơ sở dữ liệu',
                'duration' => 90,
                'total_questions' => 40,
                'subject_code' => 'INT1235',
                'easy_rate' => 30.00,
                'medium_rate' => 50.00,
                'hard_rate' => 20.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Đề thi giữa kỳ Web',
                'description' => 'Đề thi giữa kỳ môn Lập trình Web',
                'duration' => 45,
                'total_questions' => 25,
                'subject_code' => 'INT1236',
                'easy_rate' => 50.00,
                'medium_rate' => 30.00,
                'hard_rate' => 20.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($exams as $exam) {
            $examId = DB::table('exams')->insertGetId($exam);

            // Thêm exam tags cho đề thi Java
            if ($exam['subject_code'] === 'INT1234') {
                DB::table('exam_tags')->insert([
                    [
                        'exam_id' => $examId,
                        'tag_id' => 1, // OOP
                        'num_questions' => 15,
                        'easy_rate' => 40.00,
                        'medium_rate' => 40.00,
                        'hard_rate' => 20.00,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'exam_id' => $examId,
                        'tag_id' => 2, // Collection
                        'num_questions' => 15,
                        'easy_rate' => 40.00,
                        'medium_rate' => 40.00,
                        'hard_rate' => 20.00,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            }
            // Thêm exam tags cho đề thi CSDL
            else if ($exam['subject_code'] === 'INT1235') {
                DB::table('exam_tags')->insert([
                    [
                        'exam_id' => $examId,
                        'tag_id' => 3, // SQL
                        'num_questions' => 20,
                        'easy_rate' => 30.00,
                        'medium_rate' => 50.00,
                        'hard_rate' => 20.00,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'exam_id' => $examId,
                        'tag_id' => 4, // Normalization
                        'num_questions' => 20,
                        'easy_rate' => 30.00,
                        'medium_rate' => 50.00,
                        'hard_rate' => 20.00,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            }
            // Thêm exam tags cho đề thi Web
            else if ($exam['subject_code'] === 'INT1236') {
                DB::table('exam_tags')->insert([
                    [
                        'exam_id' => $examId,
                        'tag_id' => 5, // HTML
                        'num_questions' => 15,
                        'easy_rate' => 50.00,
                        'medium_rate' => 30.00,
                        'hard_rate' => 20.00,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'exam_id' => $examId,
                        'tag_id' => 6, // CSS
                        'num_questions' => 10,
                        'easy_rate' => 50.00,
                        'medium_rate' => 30.00,
                        'hard_rate' => 20.00,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            }
        }
    }
} 