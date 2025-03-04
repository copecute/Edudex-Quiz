<?php

namespace App\Exports;

use App\Models\ExamPeriodRoom;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExamPeriodRoomsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $examPeriodId;

    public function __construct($examPeriodId)
    {
        $this->examPeriodId = $examPeriodId;
    }

    public function query()
    {
        return ExamPeriodRoom::with(['room.facility'])
            ->where('exam_period_id', $this->examPeriodId);
    }

    public function headings(): array
    {
        return [
            'Mã phòng',
            'Tên phòng',
            'Cơ sở',
            'Sức chứa'
        ];
    }

    public function map($examPeriodRoom): array
    {
        return [
            $examPeriodRoom->room->code,
            $examPeriodRoom->room->name,
            $examPeriodRoom->room->facility->name,
            $examPeriodRoom->room->capacity
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center'],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E9ECEF']
                ]
            ]
        ];
    }
} 