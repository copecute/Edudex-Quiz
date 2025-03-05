<?php

namespace App\Http\Controllers;

use App\Models\ExamPeriod;
use App\Models\ExamResult;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class ExamResultController extends Controller
{
    public function index(Request $request, ExamPeriod $examPeriod)
    {
        // Load relationships cho filter
        $examPeriod->load([
            'examPeriodSubjects.subject',
            'examShifts',
            'examPeriodRooms.room'
        ]);

        $results = ExamResult::where('exam_period_id', $examPeriod->id)
            ->with([
                'examShift',
                'examPeriodSubject.subject',
                'exam',
                'examPeriodRoom.room',
                'proctor.account.accountInfo',
                'student'
            ])
            ->when($request->search, function($query, $search) {
                $query->whereHas('student', function($q) use ($search) {
                    $q->where('exam_code', 'like', "%{$search}%")
                      ->orWhere('student_code', 'like', "%{$search}%")
                      ->orWhere('full_name', 'like', "%{$search}%");
                });
            })
            ->when($request->subject_id, function($query, $subjectId) {
                $query->where('exam_period_subject_id', $subjectId);
            })
            ->when($request->shift_id, function($query, $shiftId) {
                $query->where('exam_shift_id', $shiftId);
            })
            ->when($request->room_id, function($query, $roomId) {
                $query->where('exam_period_room_id', $roomId);
            })
            ->paginate(10);

        // Tính toán thống kê với điểm sau phúc khảo
        $stats = [
            'total' => $results->total(),
            'avg_score' => $results->avg(function($result) {
                return $result->score_after_review ?? $result->score;
            }),
            'max_score' => $results->max(function($result) {
                return $result->score_after_review ?? $result->score;
            }),
            'min_score' => $results->min(function($result) {
                return $result->score_after_review ?? $result->score;
            })
        ];

        return view('exam_results.index', compact('examPeriod', 'results', 'stats'));
    }

    public function review(Request $request, ExamPeriod $examPeriod, ExamResult $result)
    {
        $validated = $request->validate([
            'correct_answers_after_review' => 'required|integer|min:0',
            'score_after_review' => 'required|numeric|min:0|max:10',
            'review_note' => 'required|string'
        ], [], [
            'correct_answers_after_review' => 'Số câu đúng sau phúc khảo',
            'score_after_review' => 'Điểm số sau phúc khảo',
            'review_note' => 'Ghi chú phúc khảo'
        ]);

        try {
            $result->update([
                'correct_answers_after_review' => $validated['correct_answers_after_review'],
                'score_after_review' => $validated['score_after_review'],
                'review_note' => $validated['review_note'],
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id()
            ]);

            return back()->with('success', 'Đã cập nhật kết quả phúc khảo');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật kết quả phúc khảo');
        }
    }

    public function export(Request $request, ExamPeriod $examPeriod)
    {
        // Query kết quả thi (tương tự như index nhưng không phân trang)
        $results = ExamResult::where('exam_period_id', $examPeriod->id)
            ->with([
                'examShift',
                'examPeriodSubject.subject',
                'exam',
                'examPeriodRoom.room',
                'proctor.account.accountInfo',
                'student'
            ])
            ->when($request->student_id, function($query, $studentId) {
                $query->where('exam_period_subject_student_id', $studentId);
            })
            ->when($request->search, function($query, $search) {
                $query->whereHas('student', function($q) use ($search) {
                    $q->where('exam_code', 'like', "%{$search}%")
                      ->orWhere('student_code', 'like', "%{$search}%")
                      ->orWhere('full_name', 'like', "%{$search}%");
                });
            })
            ->when($request->subject_id, function($query, $subjectId) {
                $query->where('exam_period_subject_id', $subjectId);
            })
            ->when($request->shift_id, function($query, $shiftId) {
                $query->where('exam_shift_id', $shiftId);
            })
            ->when($request->room_id, function($query, $roomId) {
                $query->where('exam_period_room_id', $roomId);
            })
            ->get();

        // Thêm tên thí sinh vào filename nếu xuất cho 1 thí sinh
        if ($request->student_id && $results->count() === 1) {
            $student = $results->first()->student;
            $filename = "ket-qua-thi-{$student->exam_code}-{$student->student_code}-" . date('YmdHis');
        } else {
            $filename = "ket-qua-thi-{$examPeriod->code}-" . date('YmdHis');
        }

        $format = $request->format ?? 'excel';

        if ($format === 'excel') {
            return $this->exportExcel($results, $filename);
        } else {
            return $this->exportWord($results, $filename);
        }
    }

    private function exportExcel($results, $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Tiêu đề các cột
        $sheet->setCellValue('A1', 'SBD');
        $sheet->setCellValue('B1', 'Mã SV');
        $sheet->setCellValue('C1', 'Họ tên');
        $sheet->setCellValue('D1', 'Môn thi');
        $sheet->setCellValue('E1', 'Mã đề');
        $sheet->setCellValue('F1', 'Số câu đúng');
        $sheet->setCellValue('G1', 'Tổng số câu');
        $sheet->setCellValue('H1', 'Điểm');
        $sheet->setCellValue('I1', 'Ghi chú');
        $sheet->setCellValue('J1', 'Phúc khảo');

        // Đổ dữ liệu
        $row = 2;
        foreach ($results as $result) {
            $sheet->setCellValue('A'.$row, $result->student->exam_code);
            $sheet->setCellValue('B'.$row, $result->student->student_code);
            $sheet->setCellValue('C'.$row, $result->student->full_name);
            $sheet->setCellValue('D'.$row, $result->examPeriodSubject->subject->name);
            $sheet->setCellValue('E'.$row, $result->exam_code);
            $sheet->setCellValue('F'.$row, $result->correct_answers_after_review ?? $result->correct_answers);
            $sheet->setCellValue('G'.$row, $result->total_questions);
            $sheet->setCellValue('H'.$row, $result->score_after_review ?? $result->score);
            $sheet->setCellValue('I'.$row, $result->note);
            $sheet->setCellValue('J'.$row, $result->review_note);
            $row++;
        }

        // Auto size columns
        foreach(range('A','J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    private function exportWord($results, $filename)
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        
        // Tiêu đề
        $section->addText('DANH SÁCH KẾT QUẢ THI', ['bold' => true, 'size' => 16], ['alignment' => 'center']);
        
        // Tạo bảng
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'width' => 100 * 50,
            'unit' => 'pct',
            'alignment' => 'center'
        ]);
        
        // Header
        $table->addRow();
        $headers = ['SBD', 'Mã SV', 'Họ tên', 'Môn thi', 'Mã đề', 'Số câu đúng', 'Điểm', 'Ghi chú'];
        foreach ($headers as $header) {
            $table->addCell(null, ['bgColor' => 'D3D3D3'])->addText($header, ['bold' => true], ['alignment' => 'center']);
        }
        
        // Data
        foreach ($results as $result) {
            $table->addRow();
            $table->addCell(null)->addText($result->student->exam_code, [], ['alignment' => 'center']);
            $table->addCell(null)->addText($result->student->student_code, [], ['alignment' => 'center']);
            $table->addCell(null)->addText($result->student->full_name);
            $table->addCell(null)->addText($result->examPeriodSubject->subject->name);
            $table->addCell(null)->addText($result->exam_code, [], ['alignment' => 'center']);
            $table->addCell(null)->addText(
                ($result->correct_answers_after_review ?? $result->correct_answers) . '/' . $result->total_questions
            , [], ['alignment' => 'center']);
            $table->addCell(null)->addText(
                number_format($result->score_after_review ?? $result->score, 2)
            , [], ['alignment' => 'center']);
            $table->addCell(null)->addText(
                $result->review_note ? 'Đã phúc khảo: ' . $result->review_note : $result->note
            );
        }

        // Thêm thông tin thống kê nếu có nhiều kết quả
        if ($results->count() > 1) {
            $section->addTextBreak();
            $section->addText('Thống kê:', ['bold' => true]);
            $section->addText('- Số thí sinh: ' . $results->count());
            $section->addText('- Điểm trung bình: ' . number_format($results->avg(function($result) {
                return $result->score_after_review ?? $result->score;
            }), 2));
            $section->addText('- Điểm cao nhất: ' . number_format($results->max(function($result) {
                return $result->score_after_review ?? $result->score;
            }), 2));
            $section->addText('- Điểm thấp nhất: ' . number_format($results->min(function($result) {
                return $result->score_after_review ?? $result->score;
            }), 2));
        }

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment;filename="' . $filename . '.docx"');
        header('Cache-Control: max-age=0');

        $objWriter->save('php://output');
        exit;
    }
} 