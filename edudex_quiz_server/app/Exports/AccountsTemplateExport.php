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

class AccountsTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithEvents
{
    public function array(): array
    {
        return [
            ['TRƯỜNG CĐ CÔNG NGHỆ BÁCH KHOA HÀ NỘI', '', '', '', '', '', '', '', 'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM'],
            ['', '', '', '', '', '', '', '', 'Độc Lập - Tự Do - Hạnh Phúc'],
            ['DANH SÁCH TÀI KHOẢN', '', '', '', '', '', '', ''],
            ['* Lưu ý: Các trường bắt buộc phải điền, mật khẩu mặc định là 123456', '', '', '', '', '', '', ''],
            [
                'Tên đăng nhập',
                'Email',
                'Vai trò',
                'Họ tên',
                'Ngày sinh',
                'Giới tính',
                'Số điện thoại',
                'Địa chỉ',
                'Trạng thái'
            ],
            [
                'QTV01',
                'qtv@edudex.edu.vn',
                'Admin',
                'Quản trị viên 1',
                '1990-01-01',
                'Nam',
                '0123456789',
                'Hà Nội',
                'Hoạt động'
            ],
            [
                'GV01',
                'teacher@edudex.edu.vn',
                'Giáo viên',
                'Nguyễn Văn B',
                '1992-02-02',
                'Nam',
                '0987654321',
                'Hà Nội',
                'Hoạt động'
            ],
            [
                'NV01',
                'staff@edudex.edu.vn',
                'Cán bộ coi thi',
                'Trần Thị C',
                '1995-03-03',
                'Nữ',
                '0123456789',
                'Hà Nội',
                'Hoạt động'
            ],
        ];
    }

    public function headings(): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Set font mặc định cho toàn bộ sheet
                $event->sheet->getStyle('A1:H' . count($this->array()))->getFont()->setName('Times New Roman');

                // Merge cells cho header
                $event->sheet->mergeCells('A3:H3');
                $event->sheet->mergeCells('A4:H4');

                // Căn giữa cho header
                $event->sheet->getStyle('A1:H4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Căn trái cho dòng ghi chú
                $event->sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                  
                // In đậm header
                $event->sheet->getStyle('A1:H1')->getFont()->setBold(true);
                $event->sheet->getStyle('A5:H5')->getFont()->setBold(true);
                $event->sheet->getStyle('A5:H5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Font size 18 và in đậm cho tiêu đề
                $event->sheet->getStyle('A3')->getFont()->setSize(18)->setBold(true);

                // Style cho tiêu đề cột
                $event->sheet->getStyle('A5:H5')->getFont()->setBold(true);
                $event->sheet->getStyle('A5:H5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Thêm border cho tên cột và dữ liệu
                $event->sheet->getStyle('A5:H' . count($this->array()))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                // Auto-fit columns
                foreach(range('A','H') as $col) {
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