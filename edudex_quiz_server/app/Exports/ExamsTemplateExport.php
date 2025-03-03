<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ExamsTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithEvents
{
    public function array(): array
    {
        return [
            ['TRƯỜNG CĐ CÔNG NGHỆ BÁCH KHOA HÀ NỘI', '', '', '', '', '', '', '', '', 'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM'],
            ['', '', '', '', '', '', '', '', '', 'Độc Lập - Tự Do - Hạnh Phúc'],
            ['DANH SÁCH ĐỀ THI', '', '', '', '', '', '', '', ''],
            ['* Lưu ý: Các trường bắt buộc phải điền', '', '', '', '', '', '', '', ''],
            [
                'Tên đề thi (*)',
                'Mô tả',
                'Thời gian (phút) (*)',
                'Tổng số câu hỏi (*)',
                'Mã môn học (*)',
                'Tỷ lệ dễ (%) (*)',
                'Tỷ lệ trung bình (%) (*)',
                'Tỷ lệ khó (%) (*)',
                'Tags (tag_id|num_questions|easy_rate|medium_rate|hard_rate)'
            ],
            [
                'Đề thi giữa kỳ LTCB',
                'Đề thi giữa kỳ môn Lập trình cơ bản',
                '60',
                '30',
                'LTCB',
                '40',
                '40',
                '20',
                '1|15|50|30|20; 2|10|30|50|20'
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Style cho header
                $event->sheet->getStyle('A1:J2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 13
                    ],
                    'alignment' => [
                        'horizontal' => 'center'
                    ]
                ]);

                // Style cho tiêu đề
                $event->sheet->getStyle('A3')->getFont()->setSize(18)->setBold(true);
                $event->sheet->getStyle('A5:I5')->getFont()->setBold(true);
                
                // Border cho bảng dữ liệu
                $event->sheet->getStyle('A5:I' . count($this->array()))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Merge cells cho header
                $event->sheet->mergeCells('A1:I1');
                $event->sheet->mergeCells('J1:J2');
                $event->sheet->mergeCells('A3:I3');
                $event->sheet->mergeCells('A4:I4');

                // Auto-fit columns
                foreach(range('A','I') as $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }

    public function headings(): array
    {
        return [];
    }
} 