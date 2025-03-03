<?php

namespace App\Exports;

use App\Models\ExamPeriod;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExamPeriodsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return ExamPeriod::all();
    }

    public function headings(): array
    {
        return [
            'Tên kỳ thi',
            'Mô tả',
            'Thời gian bắt đầu',
            'Thời gian kết thúc',
            'Trạng thái'
        ];
    }

    public function map($examPeriod): array
    {
        return [
            $examPeriod->name,
            $examPeriod->description,
            $examPeriod->start_time->format('d/m/Y H:i'),
            $examPeriod->end_time->format('d/m/Y H:i'),
            $examPeriod->is_active ? 'Hoạt động' : 'Khóa'
        ];
    }
} 