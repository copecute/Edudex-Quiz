<?php

namespace App\Exports;

use App\Models\Subject;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SubjectsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Subject::with('major')->get();
    }

    public function headings(): array
    {
        return [
            'Mã môn học',
            'Tên môn học',
            'Số tín chỉ',
            'Mã ngành',
            'Mô tả'
        ];
    }

    public function map($subject): array
    {
        return [
            $subject->code,
            $subject->name,
            $subject->credits,
            $subject->major->code,
            $subject->description
        ];
    }
} 