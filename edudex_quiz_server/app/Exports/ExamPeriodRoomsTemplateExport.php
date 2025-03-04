<?php

namespace App\Exports;

use App\Models\Room;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExamPeriodRoomsTemplateExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    public function collection()
    {
        // Lấy 2 phòng thi đang hoạt động làm mẫu
        return Room::where('is_active', true)
                  ->take(2)
                  ->get();
    }

    public function headings(): array
    {
        return [
            'Mã phòng (*)'
        ];
    }

    public function map($room): array
    {
        return [
            $room->code
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E9ECEF']
                ]
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Thêm ghi chú cho cột mã phòng
                $event->sheet->getComment('A1')
                    ->getText()
                    ->createTextRun('Nhập mã phòng cần phân công. Phòng thi phải đang hoạt động và chưa được phân công trong kỳ thi này.');

                // Thêm validation
                $event->sheet->getCell('A2')->getDataValidation()
                    ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_CUSTOM)
                    ->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setErrorTitle('Lỗi')
                    ->setError('Mã phòng không được để trống')
                    ->setPromptTitle('Thông tin')
                    ->setPrompt('Nhập mã phòng cần phân công');
            }
        ];
    }
} 