<?php

namespace App\Exports;

use App\Models\ExamPeriodSubjectStudent;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class StudentsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    private $subjectId;

    public function __construct($subjectId = null)
    {
        $this->subjectId = $subjectId;
    }

    public function query()
    {
        $query = ExamPeriodSubjectStudent::with('examPeriodSubject')
            ->orderBy('exam_code');

        if ($this->subjectId) {
            $query->where('exam_period_subject_id', $this->subjectId);
        }

        return $query;
    }

    public function map($student): array
    {
        return [
            $student->exam_code,
            $student->student_code,
            $student->full_name,
            $student->examPeriodSubject->examPeriod->name . ' - ' . 
            $student->examPeriodSubject->subject->name,
            $student->phone,
            $student->address,
            optional($student->birthday)->format('d/m/Y'),
            $student->gender ? 'Nam' : 'Nữ',
        ];
    }

    public function headings(): array
    {
        return [
            'Số báo danh',
            'Mã sinh viên',
            'Họ và tên',
            'Môn thi',
            'Số điện thoại',
            'Địa chỉ',
            'Ngày sinh',
            'Giới tính',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // Số báo danh
            'B' => 15, // Mã sinh viên
            'C' => 30, // Họ và tên
            'D' => 40, // Môn thi
            'E' => 15, // Số điện thoại
            'F' => 30, // Địa chỉ
            'G' => 15, // Ngày sinh
            'H' => 10, // Giới tính
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        // Style cho header
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4CAF50'],
            ],
        ]);

        // Style cho toàn bộ bảng
        $sheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Căn giữa cho một số cột
        $sheet->getStyle('A2:A' . $lastRow)->getAlignment()->setHorizontal('center'); // Số báo danh
        $sheet->getStyle('G2:H' . $lastRow)->getAlignment()->setHorizontal('center'); // Ngày sinh và giới tính

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Freeze header row
                $event->sheet->freezePane('A2');

                // Auto filter
                $lastColumn = $event->sheet->getHighestColumn();
                $event->sheet->setAutoFilter('A1:' . $lastColumn . '1');
            },
        ];
    }
} 