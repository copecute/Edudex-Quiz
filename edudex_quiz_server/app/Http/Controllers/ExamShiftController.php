<?php

namespace App\Http\Controllers;

use App\Models\ExamShift;
use App\Models\ExamPeriod;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\ExamPeriodRoomStudent;
use App\Models\ExamPeriodRoom;

class ExamShiftController extends Controller
{
    public function index(ExamPeriod $examPeriod)
    {
        $shifts = $examPeriod->examShifts()
            ->search(request('search'))
            ->orderBy('start_time')
            ->paginate(10);

        return view('exam_shifts.index', compact('examPeriod', 'shifts'));
    }

    public function create(ExamPeriod $examPeriod)
    {
        return view('exam_shifts.create', compact('examPeriod'));
    }

    public function store(Request $request, ExamPeriod $examPeriod)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'exam_date' => 'required|date|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        // Kết hợp ngày và giờ
        $startTime = Carbon::createFromFormat('Y-m-d H:i', $validated['exam_date'] . ' ' . $validated['start_time']);
        $endTime = Carbon::createFromFormat('Y-m-d H:i', $validated['exam_date'] . ' ' . $validated['end_time']);

        // Kiểm tra thời gian nằm trong kỳ thi
        if ($startTime->lt($examPeriod->start_time) || $endTime->gt($examPeriod->end_time)) {
            return back()
                ->withInput()
                ->withErrors(['exam_date' => 'Thời gian ca thi phải nằm trong thời gian kỳ thi']);
        }

        $examPeriod->examShifts()->create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        return redirect()
            ->route('exam-shifts.index', $examPeriod)
            ->with('success', 'Thêm ca thi thành công!');
    }

    public function edit(ExamPeriod $examPeriod, ExamShift $examShift)
    {
        return view('exam_shifts.edit', compact('examPeriod', 'examShift'));
    }

    public function update(Request $request, ExamPeriod $examPeriod, ExamShift $examShift)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'exam_date' => 'required|date|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        // Kết hợp ngày và giờ
        $startTime = Carbon::createFromFormat('Y-m-d H:i', $validated['exam_date'] . ' ' . $validated['start_time']);
        $endTime = Carbon::createFromFormat('Y-m-d H:i', $validated['exam_date'] . ' ' . $validated['end_time']);

        // Kiểm tra thời gian nằm trong kỳ thi
        if ($startTime->lt($examPeriod->start_time) || $endTime->gt($examPeriod->end_time)) {
            return back()
                ->withInput()
                ->withErrors(['exam_date' => 'Thời gian ca thi phải nằm trong thời gian kỳ thi']);
        }

        $examShift->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        return redirect()
            ->route('exam-shifts.index', $examPeriod)
            ->with('success', 'Cập nhật ca thi thành công!');
    }

    public function destroy(ExamPeriod $examPeriod, ExamShift $examShift)
    {
        try {
            $examShift->delete();
            return back()->with('success', 'Xóa ca thi thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi xóa ca thi!');
        }
    }

    public function toggleStatus(ExamPeriod $examPeriod, ExamShift $examShift)
    {
        $examShift->update([
            'is_active' => !$examShift->is_active
        ]);

        $message = $examShift->is_active ? 'Mở khóa ca thi thành công!' : 'Khóa ca thi thành công!';
        return back()->with('success', $message);
    }

    public function exportStudents(ExamShift $shift, ExamPeriodRoom $room)
    {
        try {
            // Lấy danh sách thí sinh
            $students = ExamPeriodRoomStudent::where('exam_shift_id', $shift->id)
                ->where('exam_period_room_id', $room->id)
                ->with(['student', 'examPeriodSubject.subject'])
                ->orderBy('seat_number')
                ->get();

            // Tạo spreadsheet mới
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Thiết lập header
            $sheet->setCellValue('A1', 'DANH SÁCH THÍ SINH');
            $sheet->mergeCells('A1:E1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

            // Thông tin phòng thi
            $sheet->setCellValue('A2', 'Phòng thi: ' . $room->room->name);
            $sheet->mergeCells('A2:E2');
            $sheet->setCellValue('A3', 'Ca thi: ' . $shift->name);
            $sheet->mergeCells('A3:E3');

            // Header cho bảng
            $sheet->setCellValue('A4', 'Số ghế');
            $sheet->setCellValue('B4', 'Số báo danh');
            $sheet->setCellValue('C4', 'Mã sinh viên');
            $sheet->setCellValue('D4', 'Họ và tên');
            $sheet->setCellValue('E4', 'Môn thi');

            // Style cho header
            $headerStyle = [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center'],
                'borders' => [
                    'allBorders' => ['borderStyle' => 'thin']
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => 'E9ECEF']
                ]
            ];
            $sheet->getStyle('A4:E4')->applyFromArray($headerStyle);

            // Đổ dữ liệu
            $row = 5;
            foreach ($students as $student) {
                $sheet->setCellValue('A'.$row, $student->seat_number);
                $sheet->setCellValue('B'.$row, $student->student->exam_code);
                $sheet->setCellValue('C'.$row, $student->student->student_code);
                $sheet->setCellValue('D'.$row, $student->student->full_name);
                $sheet->setCellValue('E'.$row, $student->examPeriodSubject->subject->name);
                $row++;
            }

            // Style cho bảng dữ liệu
            $tableStyle = [
                'borders' => [
                    'allBorders' => ['borderStyle' => 'thin']
                ]
            ];
            $sheet->getStyle('A4:E'.($row-1))->applyFromArray($tableStyle);

            // Auto-size columns
            foreach(range('A','E') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Tạo file Excel
            $writer = new Xlsx($spreadsheet);
            $filename = 'danh_sach_thi_sinh_' . $room->room->name . '_' . date('YmdHis') . '.xlsx';

            // Headers để download file
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi xuất file: ' . $e->getMessage());
        }
    }
} 