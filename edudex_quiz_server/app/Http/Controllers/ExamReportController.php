<?php

namespace App\Http\Controllers;

use App\Models\ExamPeriod;
use App\Models\ExamResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class ExamReportController extends Controller
{
    public function index(ExamPeriod $examPeriod)
    {
        // lấy thống kê cơ bản cho dashboard báo cáo
        $stats = [
            'total_students' => $examPeriod->students()->count(),
            'total_subjects' => $examPeriod->examPeriodSubjects()->count(),
            'total_results' => ExamResult::where('exam_period_id', $examPeriod->id)->count(),
            'avg_score' => ExamResult::where('exam_period_id', $examPeriod->id)
                ->avg(DB::raw('COALESCE(score_after_review, score)')),
            'max_score' => ExamResult::where('exam_period_id', $examPeriod->id)
                ->max(DB::raw('COALESCE(score_after_review, score)')),
            'min_score' => ExamResult::where('exam_period_id', $examPeriod->id)
                ->min(DB::raw('COALESCE(score_after_review, score)')),
        ];

        // lấy danh sách môn thi và phòng thi để hiển thị trong form lọc
        $subjects = $examPeriod->examPeriodSubjects()->with('subject')->get();
        $rooms = $examPeriod->examPeriodRooms()->with('room')->get();

        return view('exam_reports.index', compact('examPeriod', 'stats', 'subjects', 'rooms'));
    }

    public function bySubject(Request $request, ExamPeriod $examPeriod)
    {
        // lấy thống kê theo môn học
        $subjectStats = DB::table('exam_results')
            ->join('exam_period_subjects', 'exam_results.exam_period_subject_id', '=', 'exam_period_subjects.id')
            ->join('subjects', 'exam_period_subjects.subject_id', '=', 'subjects.id')
            ->where('exam_results.exam_period_id', $examPeriod->id)
            ->select(
                'subjects.name as subject_name',
                'subjects.code as subject_code',
                DB::raw('COUNT(*) as total_students'),
                DB::raw('SUM(CASE WHEN exam_results.score IS NOT NULL THEN 1 ELSE 0 END) as completed'),
                DB::raw('AVG(COALESCE(score_after_review, score)) as avg_score'),
                DB::raw('MAX(COALESCE(score_after_review, score)) as max_score'),
                DB::raw('MIN(COALESCE(score_after_review, score)) as min_score')
            )
            ->groupBy('subjects.name', 'subjects.code')
            ->get();

        // chi tiết môn thi được chọn nếu có
        $subjectDetail = null;
        $studentResults = collect();
        
        if ($request->has('subject_id') && $request->subject_id) {
            $subjectDetail = $examPeriod->examPeriodSubjects()
                ->with('subject')
                ->where('id', $request->subject_id)
                ->first();
                
            if ($subjectDetail) {
                $studentResults = ExamResult::where('exam_period_id', $examPeriod->id)
                    ->where('exam_period_subject_id', $request->subject_id)
                    ->with(['student', 'examPeriodRoom.room'])
                    ->orderByDesc(DB::raw('COALESCE(score_after_review, score)'))
                    ->get();
            }
        }
        
        // Nếu là request AJAX, trả về JSON
        if ($request->format === 'json') {
            return response()->json([
                'subject' => $subjectDetail,
                'results' => $studentResults
            ]);
        }

        return view('exam_reports.by_subject', compact(
            'examPeriod', 
            'subjectStats', 
            'subjectDetail',
            'studentResults'
        ));
    }

    public function byRoom(Request $request, ExamPeriod $examPeriod)
    {
        // lấy thống kê theo phòng thi
        $roomStats = DB::table('exam_results')
            ->join('exam_period_rooms', 'exam_results.exam_period_room_id', '=', 'exam_period_rooms.id')
            ->join('rooms', 'exam_period_rooms.room_id', '=', 'rooms.id')
            ->join('facilities', 'rooms.facility_id', '=', 'facilities.id')
            ->where('exam_results.exam_period_id', $examPeriod->id)
            ->select(
                'rooms.name as room_name',
                'rooms.code as room_code',
                'facilities.name as facility_name',
                DB::raw('COUNT(*) as total_students'),
                DB::raw('SUM(CASE WHEN exam_results.score IS NOT NULL THEN 1 ELSE 0 END) as completed'),
                DB::raw('AVG(COALESCE(score_after_review, score)) as avg_score'),
                DB::raw('MAX(COALESCE(score_after_review, score)) as max_score'),
                DB::raw('MIN(COALESCE(score_after_review, score)) as min_score')
            )
            ->groupBy('rooms.name', 'rooms.code', 'facilities.name')
            ->get();

        // chi tiết phòng thi được chọn nếu có
        $roomDetail = null;
        $studentResults = collect();
        
        if ($request->has('room_id') && $request->room_id) {
            $roomDetail = $examPeriod->examPeriodRooms()
                ->with(['room.facility'])
                ->where('id', $request->room_id)
                ->first();
                
            if ($roomDetail) {
                $studentResults = ExamResult::where('exam_period_id', $examPeriod->id)
                    ->where('exam_period_room_id', $request->room_id)
                    ->with(['student', 'examPeriodSubject.subject'])
                    ->orderByDesc(DB::raw('COALESCE(score_after_review, score)'))
                    ->get();
            }
        }

        // Nếu là request AJAX, trả về JSON
        if ($request->format === 'json') {
            return response()->json([
                'room' => $roomDetail,
                'results' => $studentResults
            ]);
        }

        return view('exam_reports.by_room', compact(
            'examPeriod', 
            'roomStats', 
            'roomDetail',
            'studentResults'
        ));
    }

    public function studentRanking(Request $request, ExamPeriod $examPeriod)
    {
        // lấy danh sách môn thi để hiển thị trong form lọc
        $subjects = $examPeriod->examPeriodSubjects()->with('subject')->get();
        
        // lấy kết quả thi xếp theo điểm từ cao xuống thấp
        $rankingQuery = ExamResult::where('exam_period_id', $examPeriod->id)
            ->with([
                'student', 
                'examPeriodRoom.room', 
                'examPeriodSubject.subject'
            ])
            ->orderByRaw('COALESCE(score_after_review, score) DESC');
        
        // lọc theo môn thi nếu có
        if ($request->filled('subject_id')) {
            $rankingQuery->where('exam_period_subject_id', $request->subject_id);
        }
        
        // tìm kiếm theo tên, mã SV
        if ($request->filled('search')) {
            $search = $request->search;
            $rankingQuery->whereHas('student', function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('student_code', 'like', "%{$search}%")
                  ->orWhere('exam_code', 'like', "%{$search}%");
            });
        }
        
        // giới hạn số lượng hiển thị
        if ($request->filled('limit') && is_numeric($request->limit)) {
            $rankings = $rankingQuery->take($request->limit)->get();
        } else {
            // Phân trang nếu không giới hạn
            $rankings = $rankingQuery->paginate(25);
        }
        
        return view('exam_reports.student_ranking', compact('examPeriod', 'subjects', 'rankings'));
    }

    public function completionRate(Request $request, ExamPeriod $examPeriod)
    {
        // thống kê tỷ lệ hoàn thành bài thi
        $completionStats = [
            'total' => ExamResult::where('exam_period_id', $examPeriod->id)->count(),
            'completed' => ExamResult::where('exam_period_id', $examPeriod->id)
                ->whereNotNull('score')
                ->count(),
            'incomplete' => ExamResult::where('exam_period_id', $examPeriod->id)
                ->whereNull('score')
                ->count(),
        ];
        
        // tính tỷ lệ phần trăm
        $completionStats['completion_rate'] = $completionStats['total'] > 0 
            ? round(($completionStats['completed'] / $completionStats['total']) * 100, 2) 
            : 0;
        
        // thống kê theo môn học
        $subjectCompletion = DB::table('exam_results')
            ->join('exam_period_subjects', 'exam_results.exam_period_subject_id', '=', 'exam_period_subjects.id')
            ->join('subjects', 'exam_period_subjects.subject_id', '=', 'subjects.id')
            ->where('exam_results.exam_period_id', $examPeriod->id)
            ->select(
                'subjects.name as subject_name',
                'subjects.code as subject_code',
                DB::raw('COUNT(*) as total_students'),
                DB::raw('SUM(CASE WHEN exam_results.score IS NOT NULL THEN 1 ELSE 0 END) as completed_students'),
                DB::raw('AVG(COALESCE(score_after_review, score)) as avg_score')
            )
            ->groupBy('subjects.name', 'subjects.code')
            ->get();
            
        // thêm tỷ lệ phần trăm
        foreach ($subjectCompletion as $subject) {
            $subject->completion_rate = $subject->total_students > 0 
                ? round(($subject->completed_students / $subject->total_students) * 100, 1) 
                : 0;
        }
        
        // thống kê theo phòng thi
        $roomCompletion = DB::table('exam_results')
            ->join('exam_period_rooms', 'exam_results.exam_period_room_id', '=', 'exam_period_rooms.id')
            ->join('rooms', 'exam_period_rooms.room_id', '=', 'rooms.id')
            ->where('exam_results.exam_period_id', $examPeriod->id)
            ->select(
                'rooms.name as room_name',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN exam_results.score IS NOT NULL THEN 1 ELSE 0 END) as completed'),
                DB::raw('SUM(CASE WHEN exam_results.score IS NULL THEN 1 ELSE 0 END) as incomplete')
            )
            ->groupBy('rooms.name')
            ->get()
            ->map(function($item) {
                $item->completion_rate = $item->total > 0 
                    ? round(($item->completed / $item->total) * 100, 2) 
                    : 0;
                return $item;
            });

        return view('exam_reports.completion_rate', compact(
            'examPeriod', 
            'completionStats', 
            'subjectCompletion',
            'roomCompletion'
        ));
    }

    public function export(Request $request, ExamPeriod $examPeriod, $type)
    {
        // xác định loại báo cáo cần xuất
        $reportType = $request->report_type ?? 'all';
        $subjectId = $request->subject_id;
        $roomId = $request->room_id;
        
        // query cơ bản
        $query = ExamResult::where('exam_period_id', $examPeriod->id)
            ->with(['student', 'examPeriodSubject.subject', 'examPeriodRoom.room', 'examShift']);
            
        // áp dụng bộ lọc
        if ($reportType === 'subject' && $subjectId) {
            $query->where('exam_period_subject_id', $subjectId);
        } elseif ($reportType === 'room' && $roomId) {
            $query->where('exam_period_room_id', $roomId);
        }
        
        // sắp xếp kết quả
        $results = $query->orderBy('exam_period_subject_id')
            ->orderBy('exam_period_room_id')
            ->orderByDesc(DB::raw('COALESCE(score_after_review, score)'))
            ->get();
            
        // xác định tên file
        $filename = "ket-qua-thi-{$examPeriod->id}";
        
        if ($reportType === 'subject' && $subjectId) {
            $subject = $examPeriod->examPeriodSubjects()->find($subjectId);
            if ($subject && $subject->subject) {
                $filename .= "-{$subject->subject->code}";
            }
        } elseif ($reportType === 'room' && $roomId) {
            $room = $examPeriod->examPeriodRooms()->find($roomId);
            if ($room && $room->room) {
                $filename .= "-{$room->room->code}";
            }
        }
        
        // xuất file theo định dạng
        if ($type === 'excel') {
            return $this->exportExcel($results, $filename, $examPeriod);
        } else {
            return $this->exportWord($results, $filename, $examPeriod);
        }
    }

    private function exportExcel($results, $filename, $examPeriod)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Tiêu đề
        $sheet->setCellValue('A1', 'BÁO CÁO KẾT QUẢ THI');
        $sheet->setCellValue('A2', 'Kỳ thi: ' . $examPeriod->name);
        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A1:A2')->getFont()->setBold(true);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal('center');
        
        // Header
        $sheet->setCellValue('A4', 'STT');
        $sheet->setCellValue('B4', 'SBD');
        $sheet->setCellValue('C4', 'Mã SV');
        $sheet->setCellValue('D4', 'Họ tên');
        $sheet->setCellValue('E4', 'Môn thi');
        $sheet->setCellValue('F4', 'Phòng thi');
        $sheet->setCellValue('G4', 'Số câu đúng');
        $sheet->setCellValue('H4', 'Điểm');
        
        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => 'thin']]
        ];
        $sheet->getStyle('A4:H4')->applyFromArray($headerStyle);
        
        // Dữ liệu
        $row = 5;
        foreach ($results as $index => $result) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $result->student->exam_code ?? 'N/A');
            $sheet->setCellValue('C' . $row, $result->student->student_code ?? 'N/A');
            $sheet->setCellValue('D' . $row, $result->student->full_name ?? 'N/A');
            $sheet->setCellValue('E' . $row, $result->examPeriodSubject->subject->name ?? 'N/A');
            $sheet->setCellValue('F' . $row, $result->examPeriodRoom->room->name ?? 'N/A');
            
            // Số câu đúng (sau phúc khảo nếu có)
            $correctAnswers = $result->correct_answers_after_review ?? $result->correct_answers;
            $totalQuestions = $result->total_questions;
            $sheet->setCellValue('G' . $row, $correctAnswers . '/' . $totalQuestions);
            
            // Điểm (sau phúc khảo nếu có)
            $score = $result->score_after_review ?? $result->score;
            $sheet->setCellValue('H' . $row, number_format($score, 2));
            
            $row++;
        }
        
        // Style cho dữ liệu
        $dataStyle = [
            'borders' => ['allBorders' => ['borderStyle' => 'thin']]
        ];
        $sheet->getStyle('A4:H' . ($row - 1))->applyFromArray($dataStyle);
        
        // Tự động điều chỉnh chiều rộng cột
        foreach(range('A','H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Thống kê
        $row += 2;
        $sheet->setCellValue('A' . $row, 'THỐNG KÊ');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        
        $row++;
        $sheet->setCellValue('A' . $row, 'Tổng số thí sinh:');
        $sheet->setCellValue('B' . $row, count($results));
        
        $row++;
        $avgScore = $results->avg(function($result) {
            return $result->score_after_review ?? $result->score;
        });
        $sheet->setCellValue('A' . $row, 'Điểm trung bình:');
        $sheet->setCellValue('B' . $row, number_format($avgScore, 2));
        
        $row++;
        $maxScore = $results->max(function($result) {
            return $result->score_after_review ?? $result->score;
        });
        $sheet->setCellValue('A' . $row, 'Điểm cao nhất:');
        $sheet->setCellValue('B' . $row, number_format($maxScore, 2));
        
        $row++;
        $minScore = $results->min(function($result) {
            return $result->score_after_review ?? $result->score;
        });
        $sheet->setCellValue('A' . $row, 'Điểm thấp nhất:');
        $sheet->setCellValue('B' . $row, number_format($minScore, 2));
        
        // Tạo file Excel
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    private function exportWord($results, $filename, $examPeriod)
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        
        // Tiêu đề
        $section->addText('BÁO CÁO KẾT QUẢ THI', ['bold' => true, 'size' => 16], ['alignment' => 'center']);
        $section->addText('Kỳ thi: ' . $examPeriod->name, ['bold' => true, 'size' => 12], ['alignment' => 'center']);
        $section->addTextBreak();
        
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
        $headers = ['STT', 'SBD', 'Mã SV', 'Họ tên', 'Môn thi', 'Phòng thi', 'Số câu đúng', 'Điểm'];
        foreach ($headers as $header) {
            $table->addCell(null, ['bgColor' => 'D3D3D3'])->addText($header, ['bold' => true], ['alignment' => 'center']);
        }
        
        // Dữ liệu
        foreach ($results as $index => $result) {
            $table->addRow();
            $table->addCell(null)->addText($index + 1, [], ['alignment' => 'center']);
            $table->addCell(null)->addText($result->student->exam_code ?? 'N/A', [], ['alignment' => 'center']);
            $table->addCell(null)->addText($result->student->student_code ?? 'N/A', [], ['alignment' => 'center']);
            $table->addCell(null)->addText($result->student->full_name ?? 'N/A');
            $table->addCell(null)->addText($result->examPeriodSubject->subject->name ?? 'N/A');
            $table->addCell(null)->addText($result->examPeriodRoom->room->name ?? 'N/A');
            
            // Số câu đúng (sau phúc khảo nếu có)
            $correctAnswers = $result->correct_answers_after_review ?? $result->correct_answers;
            $totalQuestions = $result->total_questions;
            $table->addCell(null)->addText(
                $correctAnswers . '/' . $totalQuestions, [], ['alignment' => 'center']
            );
            
            // Điểm (sau phúc khảo nếu có)
            $score = $result->score_after_review ?? $result->score;
            $table->addCell(null)->addText(
                number_format($score, 2), [], ['alignment' => 'center']
            );
        }
        
        // Thêm thống kê
        $section->addTextBreak(2);
        $section->addText('THỐNG KÊ', ['bold' => true]);
        
        // Tổng số thí sinh
        $section->addText('Tổng số thí sinh: ' . count($results));
        
        // Điểm trung bình
        $avgScore = $results->avg(function($result) {
            return $result->score_after_review ?? $result->score;
        });
        $section->addText('Điểm trung bình: ' . number_format($avgScore, 2));
        
        // Điểm cao nhất
        $maxScore = $results->max(function($result) {
            return $result->score_after_review ?? $result->score;
        });
        $section->addText('Điểm cao nhất: ' . number_format($maxScore, 2));
        
        // Điểm thấp nhất
        $minScore = $results->min(function($result) {
            return $result->score_after_review ?? $result->score;
        });
        $section->addText('Điểm thấp nhất: ' . number_format($minScore, 2));
        
        // Tạo file Word
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment;filename="' . $filename . '.docx"');
        header('Cache-Control: max-age=0');
        
        $objWriter->save('php://output');
        exit;
    }
} 