<?php

namespace App\Exports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class QuestionsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Question::with(['subject', 'tags', 'answers'])->get();
    }

    public function headings(): array
    {
        return [
            'Nội dung câu hỏi',
            'Link media',
            'Mã môn học',
            'Độ khó',
            'Tags',
            'Đáp án 1',
            'Link media 1',
            'Đáp án 2',
            'Link media 2',
            'Đáp án 3',
            'Link media 3',
            'Đáp án 4',
            'Link media 4',
            'Đáp án đúng'
        ];
    }

    public function map($question): array
    {
        $answers = $question->answers->toArray();
        $correctAnswerIndex = 0;

        foreach ($answers as $index => $answer) {
            if ($answer['is_correct']) {
                $correctAnswerIndex = $index + 1;
                break;
            }
        }

        return [
            $question->content,
            $question->link_media,
            $question->subject_code,
            $question->difficulty,
            $question->tags->pluck('name')->implode(', '),
            $answers[0]['content'] ?? '',
            $answers[0]['link_media'] ?? '',
            $answers[1]['content'] ?? '',
            $answers[1]['link_media'] ?? '',
            $answers[2]['content'] ?? '',
            $answers[2]['link_media'] ?? '',
            $answers[3]['content'] ?? '',
            $answers[3]['link_media'] ?? '',
            $correctAnswerIndex
        ];
    }
} 