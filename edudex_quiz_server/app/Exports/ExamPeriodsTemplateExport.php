<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ExamPeriodsTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithEvents
{
    public function array(): array
    {
        return [
            [
                'Kỳ thi học kỳ 1 năm 2024',
                'Kỳ thi kết thúc học kỳ 1 năm học 2023-2024',
                '01/06/2024 07:00',
                '30/06/2024 17:00',
                'Hoạt động'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Tên kỳ thi (*)',
            'Mô tả',
            'Thời gian bắt đầu (*)',
            'Thời gian kết thúc (*)',
            'Trạng thái (*)'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getStyle('A1:E1')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ]);

                $event->sheet->getStyle('A1:E2')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);
            },
        ];
    }
} 