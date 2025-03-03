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

class FacilitiesTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithEvents
{
    public function array(): array
    {
        return [
            ['TRƯỜNG CĐ CÔNG NGHỆ BÁCH KHOA HÀ NỘI', '', '', '', 'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM'],
            ['', '', '', '', 'Độc Lập - Tự Do - Hạnh Phúc'],
            ['DANH SÁCH ĐỊA ĐIỂM', '', '', '', ''],
            ['* Lưu ý: Trạng thái chỉ nhận một trong hai giá trị: Hoạt động hoặc Khóa', '', '', '', ''],
            [
                'Mã cơ sở',
                'Tên cơ sở',
                'Địa chỉ', 
                'Mô tả',
                'Trạng thái'
            ],
            [
                'MĐ',
                'Mỹ Đình',
                'Số 18-20 Nhân Mỹ - Mỹ Đình 1 - Quận Nam Từ Liêm - TP. Hà Nội',
                'Cơ sở 1 khu vực Mỹ Đình',
                'Hoạt động'
            ],
            [
                'TT',
                'Thanh Trì',
                'Km 3 + 350 Đường Phan Trọng Tuệ - Huyện Thanh Trì - TP.Hà Nội',
                'Cơ sở 2 khu vực Thanh Trì',
                'Hoạt động'
            ],
            [
                'HP',
                'Hải Phòng',
                'Số 176 Quán Trữ - Quận Kiến An - TP. Hải Phòng',
                'Cơ sở 3 khu vực Hải Phòng',
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
                $event->sheet->getStyle('A1:E' . count($this->array()))->getFont()->setName('Times New Roman');

                // Merge cells cho header
                $event->sheet->mergeCells('A1:D1');
                $event->sheet->mergeCells('A2:D2');
                $event->sheet->mergeCells('A3:E3');
                $event->sheet->mergeCells('A4:E4');

                // Căn giữa cho header
                $event->sheet->getStyle('A1:E3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Căn trái cho dòng ghi chú
                $event->sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                
                // In đậm header
                $event->sheet->getStyle('A1:E1')->getFont()->setBold(true);
                $event->sheet->getStyle('E2')->getFont()->setBold(true);
                $event->sheet->getStyle('A3')->getFont()->setBold(true);
                
                // Font size 18 cho tiêu đề
                $event->sheet->getStyle('A3')->getFont()->setSize(18);
                
                // Style cho tiêu đề cột
                $event->sheet->getStyle('A5:E5')->getFont()->setBold(true);
                $event->sheet->getStyle('A5:E5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Thêm border cho tên cột và dữ liệu
                $lastRow = count($this->array());
                $event->sheet->getStyle('A5:E'.$lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                // Auto-fit columns
                foreach(range('A','E') as $col) {
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