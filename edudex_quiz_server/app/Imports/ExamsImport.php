<?php

namespace App\Imports;

use App\Models\Exam;
use App\Models\Tag;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\DB;

class ExamsImport implements ToModel, WithStartRow, WithValidation
{
    public function startRow(): int
    {
        return 6;
    }

    public function model(array $row)
    {
        DB::beginTransaction();
        try {
            // Tạo đề thi
            $exam = Exam::create([
                'name' => $row[0],
                'description' => $row[1],
                'duration' => $row[2],
                'total_questions' => $row[3],
                'subject_code' => $row[4],
                'easy_rate' => $row[5],
                'medium_rate' => $row[6],
                'hard_rate' => $row[7]
            ]);

            // Xử lý tags nếu có
            if (!empty($row[8])) {
                $tagStrings = explode(';', $row[8]);
                foreach ($tagStrings as $tagString) {
                    $tagData = explode('|', trim($tagString));
                    if (count($tagData) === 5) {
                        $exam->examTags()->create([
                            'tag_id' => $tagData[0],
                            'num_questions' => $tagData[1],
                            'easy_rate' => $tagData[2],
                            'medium_rate' => $tagData[3],
                            'hard_rate' => $tagData[4]
                        ]);
                    }
                }
            }

            DB::commit();
            return $exam;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function rules(): array
    {
        return [
            '0' => 'required|string|max:255',
            '1' => 'nullable|string',
            '2' => 'required|integer|min:1',
            '3' => 'required|integer|min:1',
            '4' => 'required|exists:subjects,code',
            '5' => 'required|numeric|min:0|max:100',
            '6' => 'required|numeric|min:0|max:100',
            '7' => 'required|numeric|min:0|max:100',
            '8' => [
                'nullable',
                function($attribute, $value, $fail) {
                    if (!empty($value)) {
                        $tagStrings = explode(';', $value);
                        foreach ($tagStrings as $tagString) {
                            $tagData = explode('|', trim($tagString));
                            if (count($tagData) !== 5) {
                                $fail('Định dạng tag không hợp lệ');
                                return;
                            }
                            if (!Tag::find($tagData[0])) {
                                $fail('Tag ID không tồn tại: ' . $tagData[0]);
                                return;
                            }
                            if ($tagData[2] + $tagData[3] + $tagData[4] != 100) {
                                $fail('Tổng tỷ lệ độ khó của tag phải bằng 100%');
                                return;
                            }
                        }
                    }
                }
            ]
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.required' => 'Tên đề thi không được để trống',
            '2.required' => 'Thời gian làm bài không được để trống',
            '2.integer' => 'Thời gian làm bài phải là số nguyên',
            '2.min' => 'Thời gian làm bài phải lớn hơn 0',
            '3.required' => 'Tổng số câu hỏi không được để trống',
            '3.integer' => 'Tổng số câu hỏi phải là số nguyên',
            '3.min' => 'Tổng số câu hỏi phải lớn hơn 0',
            '4.required' => 'Mã môn học không được để trống',
            '4.exists' => 'Mã môn học không tồn tại trong hệ thống',
            '5.required' => 'Tỷ lệ độ khó dễ không được để trống',
            '6.required' => 'Tỷ lệ độ khó trung bình không được để trống',
            '7.required' => 'Tỷ lệ độ khó khó không được để trống'
        ];
    }
} 