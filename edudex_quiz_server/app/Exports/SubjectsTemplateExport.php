<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class SubjectsTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithEvents
{
    public function array(): array
    {
        return [
            ['TRƯỜNG CĐ CÔNG NGHỆ BÁCH KHOA HÀ NỘI', '', '', '', '', 'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM'],
            ['', '', '', '', '', 'Độc Lập - Tự Do - Hạnh Phúc'],
            ['DANH SÁCH MÔN HỌC', '', '', '', ''],
            ['* Lưu ý: Các trường bắt buộc phải điền', '', '', '', ''],
            [
                'Mã môn học',
                'Tên môn học',
                'Số tín chỉ',
                'Mã ngành',
                'Mô tả'
            ],
            [
                'LTCB',
                'Lập trình cơ bản',
                '3',
                'CNTT',
                'Môn học lập trình cơ bản'
            ],
            [
                'CSDL',
                'Cơ sở dữ liệu',
                '4',
                'CNTT',
                'Môn học cơ sở dữ liệu'
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getStyle('A1:F' . (count($this->array())))->getFont()->setName('Times New Roman');
                
                // Merge cells cho header
                $event->sheet->mergeCells('A3:E3');
                $event->sheet->mergeCells('A4:E4');
                
                // Căn giữa và style cho header
                $event->sheet->getStyle('A1:F4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                
                // Style cho tiêu đề
                $event->sheet->getStyle('A3')->getFont()->setSize(18)->setBold(true);
                $event->sheet->getStyle('A5:E5')->getFont()->setBold(true);
                
                // Border cho bảng dữ liệu
                $event->sheet->getStyle('A5:E' . count($this->array()))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);
            },
        ];
    }

    public function headings(): array
    {
        return [];
    }
} 