<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class FacultiesTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithEvents
{
    public function array(): array
    {
        return [
            ['TRƯỜNG CĐ CÔNG NGHỆ BÁCH KHOA HÀ NỘI', '', '', 'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM'],
            ['', '', '', 'Độc Lập - Tự Do - Hạnh Phúc'],
            ['DANH SÁCH KHOA', '', ''],
            ['* Lưu ý: Các trường bắt buộc phải điền', '', ''],
            [
                'Mã khoa',
                'Tên khoa',
                'Mô tả'
            ],
            [
                'CNTT',
                'Công nghệ thông tin',
                'Khoa Công nghệ thông tin'
            ],
            [
                'KT',
                'Kế toán',
                'Khoa Kế toán'
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getStyle('A1:D' . (count($this->array())))->getFont()->setName('Times New Roman');
                
                // Merge cells cho header
                $event->sheet->mergeCells('A3:C3');
                $event->sheet->mergeCells('A4:C4');
                
                // Căn giữa và style cho header
                $event->sheet->getStyle('A1:D4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                
                // Style cho tiêu đề
                $event->sheet->getStyle('A3')->getFont()->setSize(18)->setBold(true);
                $event->sheet->getStyle('A5:C5')->getFont()->setBold(true);
                
                // Border cho bảng dữ liệu
                $event->sheet->getStyle('A5:C' . count($this->array()))->applyFromArray([
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