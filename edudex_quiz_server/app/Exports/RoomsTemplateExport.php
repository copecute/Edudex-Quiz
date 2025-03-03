<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RoomsTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithEvents
{
    public function array(): array
    {
        return [
            ['TRƯỜNG CĐ CÔNG NGHỆ BÁCH KHOA HÀ NỘI', '', '', '', '', 'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM'],
            ['', '', '', '', '', 'Độc Lập - Tự Do - Hạnh Phúc'],
            ['DANH SÁCH PHÒNG THI', '', '', '', ''],
            ['* Lưu ý: Trạng thái chỉ nhận một trong hai giá trị: Hoạt động hoặc Khóa', '', '', '', ''],
            [
                'Mã phòng thi',
                'Tên phòng thi',
                'Mã cơ sở',
                'Mô tả',
                'Sức chứa',
                'Trạng thái'
            ],
            [
                'P001',
                'Phòng thi 403',
                'MĐ',
                'Phòng thi tầng 4',
                '20',
                'Hoạt động'
            ],
            [
                'P002',
                'Phòng thi 502',
                'MĐ',
                'Phòng thi tầng 5',
                '25',
                'Khóa'
            ],
            [
                'P003',
                'Phòng thi 205',
                'TT',
                'Phòng thi tầng 2',
                '30',
                'Hoạt động'
            ],
            [
                'P004',
                'Phòng thi 502',
                'TT',
                'Phòng thi tầng 5',
                '32',
                'Khóa'
            ]
        ];
    }

    public function headings(): array
    {
        return []; // Return empty array since headers are included in array() method
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Set font mặc định cho toàn bộ sheet
                $event->sheet->getStyle('A1:F' . count($this->array()))->getFont()->setName('Times New Roman');

                // Merge cells cho header
                $event->sheet->mergeCells('A1:D1');
                $event->sheet->mergeCells('A2:D2');
                $event->sheet->mergeCells('A3:F3');
                $event->sheet->mergeCells('A4:F4');

                // Căn giữa cho header
                $event->sheet->getStyle('A1:F3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Căn trái cho dòng ghi chú
                $event->sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                
                // In đậm header
                $event->sheet->getStyle('A1:F1')->getFont()->setBold(true);
                $event->sheet->getStyle('F2')->getFont()->setBold(true);
                $event->sheet->getStyle('A3')->getFont()->setBold(true);
                
                // Font size 18 cho tiêu đề
                $event->sheet->getStyle('A3')->getFont()->setSize(18);
                
                // Style cho tiêu đề cột
                $event->sheet->getStyle('A5:F5')->getFont()->setBold(true);
                $event->sheet->getStyle('A5:F5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Thêm border cho tên cột và dữ liệu
                $lastRow = count($this->array());
                $event->sheet->getStyle('A5:F'.$lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                // Auto-fit columns
                foreach(range('A','F') as $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                }
            }
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }
} 