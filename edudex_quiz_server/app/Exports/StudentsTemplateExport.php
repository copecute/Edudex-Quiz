<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class StudentsTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithEvents
{
    public function array(): array
    {
        return [
            ['TRƯỜNG CĐ CÔNG NGHỆ BÁCH KHOA HÀ NỘI', '', '', '', '', '', 'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM'],
            ['', '', '', '', '', '', 'Độc Lập - Tự Do - Hạnh Phúc'],
            ['DANH SÁCH THÍ SINH', '', '', '', '', ''],
            ['* Lưu ý: Các trường bắt buộc phải điền', '', '', '', '', ''],
            [
                'Mã sinh viên (*)',
                'Họ và tên (*)',
                'Số điện thoại',
                'Địa chỉ',
                'Ngày sinh (YYYY-MM-DD)',
                'Giới tính (1: Nam, 0: Nữ) (*)'
            ],
            [
                'SV001',
                'Nguyễn Văn A',
                '0123456789',
                'Hà Nội',
                '2000-01-01',
                '1'
            ],
            [
                'SV002',
                'Trần Thị B',
                '0987654321',
                'Hồ Chí Minh',
                '2000-02-02',
                '0'
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Style cho header
                $event->sheet->getStyle('A1:G2')->applyFromArray([
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
                $event->sheet->getStyle('A5:F5')->getFont()->setBold(true);
                
                // Border cho bảng dữ liệu
                $event->sheet->getStyle('A5:F' . count($this->array()))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Merge cells cho header
                $event->sheet->mergeCells('A1:F1');
                $event->sheet->mergeCells('G1:G2');
                $event->sheet->mergeCells('A3:F3');
                $event->sheet->mergeCells('A4:F4');

                // Auto-fit columns
                foreach(range('A','F') as $col) {
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