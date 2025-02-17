<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        // Tắt kiểm tra khóa ngoại
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Xóa dữ liệu cũ
        DB::table('question_tag')->truncate();
        DB::table('tags')->truncate();
        DB::table('answers')->truncate();
        DB::table('questions')->truncate();
        
        // Bật lại kiểm tra khóa ngoại
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Lấy danh sách subject_id
        $subjects = DB::table('subjects')->get();

        foreach ($subjects as $subject) {
            // Tạo tags cho môn học
            $tags = $this->getTagsForSubject($subject->code);
            $tagIds = [];
            
            foreach ($tags as $tag) {
                $tagIds[] = DB::table('tags')->insertGetId([
                    'name' => $tag,
                    'subject_id' => $subject->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Tạo câu hỏi cho môn học
            $questions = $this->getQuestionsForSubject($subject->code);
            
            foreach ($questions as $question) {
                // Thêm câu hỏi
                $questionId = DB::table('questions')->insertGetId([
                    'content' => $question['content'],
                    'subject_id' => $subject->id,
                    'level' => $question['level'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Thêm đáp án
                foreach ($question['answers'] as $answer) {
                    DB::table('answers')->insert([
                        'question_id' => $questionId,
                        'content' => $answer['content'],
                        'is_correct' => $answer['is_correct'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                // Thêm tags cho câu hỏi
                foreach ($question['tags'] as $tagIndex) {
                    DB::table('question_tag')->insert([
                        'question_id' => $questionId,
                        'tag_id' => $tagIds[$tagIndex]
                    ]);
                }
            }
        }
    }

    private function getTagsForSubject(string $subjectCode): array
    {
        $tagsData = [
            'KTPM001' => ['Quy trình phần mềm', 'Mô hình phát triển', 'Yêu cầu phần mềm'],
            'KTPM002' => ['Unit Test', 'Integration Test', 'System Test'],
            'KTPM003' => ['Phân tích yêu cầu', 'Thiết kế hệ thống', 'UML'],
            'HTTT001' => ['Mô hình dữ liệu', 'SQL', 'Chuẩn hóa'],
            'HTTT002' => ['Thống kê', 'Xử lý dữ liệu', 'Trực quan hóa'],
            'HTTT003' => ['Data Warehouse', 'ETL', 'OLAP'],
            'MMT001' => ['TCP/IP', 'OSI', 'Routing'],
            'MMT002' => ['Mã hóa', 'Firewall', 'SSL/TLS'],
            'MMT003' => ['Windows Server', 'Linux', 'Network Services'],
            // Thêm tags cho các môn học khác tương tự...
        ];

        return $tagsData[$subjectCode] ?? ['Chương 1', 'Chương 2', 'Chương 3'];
    }

    private function getQuestionsForSubject(string $subjectCode): array
    {
        $questionsData = [
            'KTPM001' => array_merge(
                [
                    [
                        'content' => 'Mô hình thác nước (Waterfall) có bao nhiêu giai đoạn chính?',
                        'level' => 1, // Dễ
                        'answers' => [
                            ['content' => '3 giai đoạn', 'is_correct' => false],
                            ['content' => '5 giai đoạn', 'is_correct' => true],
                            ['content' => '7 giai đoạn', 'is_correct' => false],
                            ['content' => '9 giai đoạn', 'is_correct' => false]
                        ],
                        'tags' => [0, 1] // Liên kết với 2 tag đầu tiên
                    ],
                    // Thêm câu hỏi khác ở đây...
                ],
                $this->generateAdditionalQuestions('KTPM001')
            ),
            'HTTT001' => [
                [
                    'content' => 'Khóa chính (Primary Key) là gì?',
                    'level' => 1,
                    'answers' => [
                        ['content' => 'Trường duy nhất định danh bản ghi', 'is_correct' => true],
                        ['content' => 'Trường bắt buộc nhập liệu', 'is_correct' => false],
                        ['content' => 'Trường có thể null', 'is_correct' => false],
                        ['content' => 'Trường tự động tăng', 'is_correct' => false]
                    ],
                    'tags' => [0]
                ],
                [
                    'content' => 'Chuẩn hóa dữ liệu 1NF là gì?',
                    'level' => 2,
                    'answers' => [
                        ['content' => 'Loại bỏ dữ liệu trùng lặp', 'is_correct' => false],
                        ['content' => 'Các trường atomic', 'is_correct' => true],
                        ['content' => 'Loại bỏ phụ thuộc bắc cầu', 'is_correct' => false],
                        ['content' => 'Có khóa chính', 'is_correct' => false]
                    ],
                    'tags' => [2]
                ]
            ],
            'MMT001' => [
                [
                    'content' => 'Mô hình OSI có bao nhiêu tầng?',
                    'level' => 1,
                    'answers' => [
                        ['content' => '5 tầng', 'is_correct' => false],
                        ['content' => '6 tầng', 'is_correct' => false],
                        ['content' => '7 tầng', 'is_correct' => true],
                        ['content' => '8 tầng', 'is_correct' => false]
                    ],
                    'tags' => [1]
                ],
                [
                    'content' => 'Giao thức nào hoạt động ở tầng Transport?',
                    'level' => 2,
                    'answers' => [
                        ['content' => 'HTTP', 'is_correct' => false],
                        ['content' => 'IP', 'is_correct' => false],
                        ['content' => 'TCP', 'is_correct' => true],
                        ['content' => 'ARP', 'is_correct' => false]
                    ],
                    'tags' => [0]
                ]
            ]
            // Thêm câu hỏi cho các môn học khác...
        ];

        return $questionsData[$subjectCode] ?? [];
    }

    private function generateAdditionalQuestions(string $subjectCode): array
    {
        $additionalQuestions = [];
        for ($i = 1; $i <= 50; $i++) {
            $additionalQuestions[] = [
                'content' => "Câu hỏi số $i cho môn $subjectCode?",
                'level' => rand(1, 3), // Tạo cấp độ ngẫu nhiên từ 1 đến 3
                'answers' => [
                    ['content' => 'Đáp án A', 'is_correct' => rand(0, 1) == 1],
                    ['content' => 'Đáp án B', 'is_correct' => rand(0, 1) == 1],
                    ['content' => 'Đáp án C', 'is_correct' => rand(0, 1) == 1],
                    ['content' => 'Đáp án D', 'is_correct' => rand(0, 1) == 1]
                ],
                'tags' => [rand(0, 2)] // Liên kết với tag ngẫu nhiên
            ];
        }
        return $additionalQuestions;
    }
} 