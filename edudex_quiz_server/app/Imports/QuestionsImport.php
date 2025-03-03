<?php

namespace App\Imports;

use App\Models\Question;
use App\Models\Tag;
use App\Models\Answer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\DB;

class QuestionsImport implements ToModel, WithStartRow, WithValidation
{
    public function startRow(): int
    {
        return 6;
    }

    public function model(array $row)
    {
        try {
            DB::beginTransaction();

            // Tạo câu hỏi
            $question = Question::create([
                'content' => $row[0],
                'link_media' => $row[1] ?: null,
                'subject_code' => $row[2],
                'difficulty' => strtolower($row[3]),
            ]);

            // Xử lý tags
            $tagNames = array_map('trim', explode(',', $row[4]));
            foreach ($tagNames as $tagName) {
                $tag = Tag::firstOrCreate([
                    'name' => $tagName,
                    'subject_code' => $row[2]
                ]);
                $question->tags()->attach($tag->id);
            }

            // Tạo các đáp án
            $correctAnswer = (int)$row[13];
            for ($i = 0; $i < 4; $i++) {
                if (!empty($row[5 + $i * 2])) {
                    Answer::create([
                        'question_id' => $question->id,
                        'content' => $row[5 + $i * 2],
                        'link_media' => $row[6 + $i * 2] ?: null,
                        'is_correct' => ($i + 1 == $correctAnswer)
                    ]);
                }
            }

            DB::commit();
            return $question;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function rules(): array
    {
        return [
            '0' => 'required', // content
            '2' => 'required|exists:subjects,code', // subject_code
            '3' => 'required|in:easy,medium,hard', // difficulty
            '4' => 'required', // tags
            '5' => 'required', // answer 1
            '7' => 'required', // answer 2
            '13' => 'required|integer|min:1|max:4', // correct answer
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.required' => 'Nội dung câu hỏi không được để trống',
            '2.required' => 'Mã môn học không được để trống',
            '2.exists' => 'Mã môn học không tồn tại trong hệ thống',
            '3.required' => 'Độ khó không được để trống',
            '3.in' => 'Độ khó phải là một trong các giá trị: easy, medium, hard',
            '4.required' => 'Tags không được để trống',
            '5.required' => 'Đáp án 1 không được để trống',
            '7.required' => 'Đáp án 2 không được để trống',
            '13.required' => 'Đáp án đúng không được để trống',
            '13.integer' => 'Đáp án đúng phải là số nguyên',
            '13.min' => 'Đáp án đúng phải từ 1 đến 4',
            '13.max' => 'Đáp án đúng phải từ 1 đến 4'
        ];
    }
} 