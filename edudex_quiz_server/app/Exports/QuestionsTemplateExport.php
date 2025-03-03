<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class QuestionsTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithEvents
{
    public function array(): array
    {
        return [
            ['TRƯỜNG CĐ CÔNG NGHỆ BÁCH KHOA HÀ NỘI', '', '', '', '', '', '', '', '', '', '', '', '', '', 'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM'],
            ['', '', '', '', '', '', '', '', '', '', '', '', '', '', 'Độc Lập - Tự Do - Hạnh Phúc'],
            ['DANH SÁCH CÂU HỎI', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['* Lưu ý: Các trường bắt buộc phải điền', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            [
                'Nội dung câu hỏi',
                'Link media',
                'Mã môn học',
                'Độ khó',
                'Tags',
                'Đáp án 1',
                'Link media 1',
                'Đáp án 2',
                'Link media 2',
                'Đáp án 3',
                'Link media 3',
                'Đáp án 4',
                'Link media 4',
                'Đáp án đúng'
            ],
            [
                'Câu hỏi mẫu 1?',
                'https://i.imgur.com/pOLBCYC.png',
                'LTCB',
                'easy',
                'Biến, Kiểu dữ liệu',
                'Đáp án A',
                '',
                'Đáp án B',
                '',
                'Đáp án C',
                '',
                'Đáp án D',
                '',
                '1'
            ],
            [
                'Câu hỏi mẫu 2?',
                '',
                'LTCB',
                'medium',
                'Vòng lặp',
                'Đáp án A',
                '',
                'Đáp án B',
                '',
                'Đáp án C',
                '',
                'Đáp án D',
                '',
                '2'
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getStyle('A1:O' . (count($this->array())))->getFont()->setName('Times New Roman');
                
                // Merge cells cho header
                $event->sheet->mergeCells('A3:N3');
                $event->sheet->mergeCells('A4:N4');
                
                // Căn giữa và style cho header
                $event->sheet->getStyle('A1:O4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                
                // Style cho tiêu đề
                $event->sheet->getStyle('A3')->getFont()->setSize(18)->setBold(true);
                $event->sheet->getStyle('A5:N5')->getFont()->setBold(true);
                
                // Border cho bảng dữ liệu
                $event->sheet->getStyle('A5:N' . count($this->array()))->applyFromArray([
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