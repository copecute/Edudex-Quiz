<?php

namespace App\Exports;

use App\Models\Exam;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExamsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Exam::with(['subject', 'examTags.tag'])->get();
    }

    public function headings(): array
    {
        return [
            'Tên đề thi',
            'Mô tả',
            'Thời gian (phút)',
            'Tổng số câu hỏi',
            'Mã môn học',
            'Tỷ lệ dễ (%)',
            'Tỷ lệ trung bình (%)',
            'Tỷ lệ khó (%)',
            'Tags (định dạng: tag_id|num_questions|easy_rate|medium_rate|hard_rate)'
        ];
    }

    public function map($exam): array
    {
        $tags = $exam->examTags->map(function($examTag) {
            return implode('|', [
                $examTag->tag_id,
                $examTag->num_questions,
                $examTag->easy_rate,
                $examTag->medium_rate,
                $examTag->hard_rate
            ]);
        })->implode('; ');

        return [
            $exam->name,
            $exam->description,
            $exam->duration,
            $exam->total_questions,
            $exam->subject_code,
            $exam->easy_rate,
            $exam->medium_rate,
            $exam->hard_rate,
            $tags
        ];
    }
} 