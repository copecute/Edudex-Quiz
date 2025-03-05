<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamPeriod;
use App\Models\ExamResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExamResultController extends Controller
{
    public function store(Request $request, ExamPeriod $examPeriod)
    {
        // Kiểm tra request data
        \Log::info('Request data:', $request->all());

        // Định nghĩa messages lỗi
        $messages = [
            'required' => 'Trường :attribute là bắt buộc',
            'string' => 'Trường :attribute phải là chuỗi',
            'integer' => 'Trường :attribute phải là số nguyên',
            'numeric' => 'Trường :attribute phải là số',
            'min' => 'Trường :attribute tối thiểu là :min',
            'max' => 'Trường :attribute tối đa là :max',
        ];

        // Định nghĩa tên các trường
        $attributes = [
            'exam_code' => 'Số báo danh',
            'room_code' => 'Mã phòng thi',
            'student_code' => 'Mã sinh viên',
            'shift_id' => 'ID ca thi',
            'subject_code' => 'Mã môn thi',
            'exam_id' => 'ID đề thi',
            'proctor_code' => 'ID cán bộ coi thi',
            'correct_answers' => 'Số câu đúng',
            'total_questions' => 'Tổng số câu',
            'score' => 'Điểm số',
            'note' => 'Ghi chú',
            'log_file' => 'File log'
        ];

        $validated = $request->validate([
            'exam_code' => 'required|string',
            'room_code' => 'required|string',
            'student_code' => 'required|string',
            'shift_id' => 'required|integer',
            'subject_code' => 'required|string',
            'exam_id' => 'required|integer',
            'proctor_code' => 'required|integer',
            'correct_answers' => 'required|integer|min:0',
            'total_questions' => 'required|integer|min:1',
            'score' => 'required|numeric|min:0|max:10',
            'note' => 'nullable|string',
            'log_file' => 'required|string'
        ], $messages, $attributes);

        try {
            // Kiểm tra trạng thái và thời gian của kỳ thi
            $now = now();
            if (!$examPeriod->is_active) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kỳ thi đã bị khóa',
                    'errors' => [
                        'exam_period' => ['Kỳ thi ' . $examPeriod->name . ' đã bị khóa']
                    ]
                ], 422);
            }

            if ($now->lt($examPeriod->start_time)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kỳ thi chưa bắt đầu',
                    'errors' => [
                        'exam_period' => ['Kỳ thi ' . $examPeriod->name . ' sẽ bắt đầu lúc ' . 
                            $examPeriod->start_time->format('H:i d/m/Y')]
                    ]
                ], 422);
            }

            if ($now->gt($examPeriod->end_time)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kỳ thi đã kết thúc',
                    'errors' => [
                        'exam_period' => ['Kỳ thi ' . $examPeriod->name . ' đã kết thúc lúc ' . 
                            $examPeriod->end_time->format('H:i d/m/Y')]
                    ]
                ], 422);
            }

            // Kiểm tra ca thi có tồn tại không
            if (!$examPeriod->examShifts()->where('id', $validated['shift_id'])->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ca thi không tồn tại',
                    'errors' => [
                        'shift_id' => ['Ca thi với ID ' . $validated['shift_id'] . ' không tồn tại trong kỳ thi này']
                    ]
                ], 422);
            }

            // Tìm thông tin thí sinh và phòng thi
            $student = $examPeriod->examPeriodSubjectStudents()
                ->where('exam_code', $validated['exam_code'])
                ->where('student_code', $validated['student_code'])
                ->firstOrFail();

            $room = $examPeriod->examPeriodRooms()
                ->whereHas('room', function($query) use ($validated) {
                    $query->where('code', $validated['room_code']);
                })
                ->firstOrFail();

            // Tìm ca thi, môn thi, đề thi và giám thị
            $shift = $examPeriod->examShifts()
                ->where('id', $validated['shift_id'])
                ->firstOrFail();

            $subject = $examPeriod->examPeriodSubjects()
                ->whereHas('subject', function($query) use ($validated) {
                    $query->where('code', $validated['subject_code']);
                })
                ->firstOrFail();

            // Tìm đề thi theo ID và subject_id
            $exam = $subject->exam()
                ->where('id', $validated['exam_id'])
                ->firstOrFail();

            // Tìm giám thị theo ID thay vì code
            $proctor = $examPeriod->proctors()
                ->where('id', $validated['proctor_code'])
                ->firstOrFail();

            // Kiểm tra xem thí sinh đã nộp bài môn này chưa
            $existingResult = ExamResult::where([
                'exam_period_id' => $examPeriod->id,
                'exam_period_subject_student_id' => $student->id,
                'exam_period_subject_id' => $subject->id
            ])->first();

            if ($existingResult) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Thí sinh đã nộp bài môn này',
                    'errors' => [
                        'student' => ['Thí sinh ' . $student->exam_code . ' đã nộp bài môn ' . $subject->subject->name]
                    ]
                ], 422);
            }

            // Tạo kết quả thi
            ExamResult::create([
                'exam_period_id' => $examPeriod->id,
                'exam_shift_id' => $shift->id,
                'exam_period_subject_id' => $student->exam_period_subject_id,
                'exam_id' => $exam->id,
                'exam_period_room_id' => $room->id,
                'exam_period_proctor_id' => $proctor->id,
                'exam_period_subject_student_id' => $student->id,
                'exam_period_code' => $examPeriod->code ?? 'KT' . $examPeriod->id,
                'exam_shift_code' => 'CA' . $shift->id,
                'exam_subject_code' => $subject->subject->code,
                'exam_code' => 'DT' . $exam->id,
                'room_code' => $room->room->code,
                'proctor_code' => 'CBCT' . $proctor->id,
                'student_code' => $student->student_code,
                'correct_answers' => $validated['correct_answers'],
                'total_questions' => $validated['total_questions'],
                'score' => $validated['score'],
                'note' => $validated['note'],
                'log_file' => $validated['log_file']  // Lưu trực tiếp base64 string
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Nộp bài thành công'
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            // Bắt lỗi vi phạm unique constraint
            if ($e->errorInfo[1] === 1062) { // MySQL duplicate entry error
                return response()->json([
                    'status' => 'error',
                    'message' => 'Thí sinh đã nộp bài môn này',
                    'errors' => [
                        'student' => ['Không thể nộp bài nhiều lần cho cùng một môn thi']
                    ]
                ], 422);
            }
            throw $e;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Xử lý lỗi không tìm thấy dữ liệu
            $message = match(true) {
                str_contains($e->getMessage(), 'ExamPeriodSubjectStudent') => 'Không tìm thấy thông tin thí sinh',
                str_contains($e->getMessage(), 'ExamPeriodRoom') => 'Không tìm thấy phòng thi',
                str_contains($e->getMessage(), 'ExamShift') => 'Không tìm thấy ca thi',
                str_contains($e->getMessage(), 'ExamPeriodSubject') => 'Không tìm thấy môn thi',
                str_contains($e->getMessage(), 'Exam') => 'Không tìm thấy đề thi',
                str_contains($e->getMessage(), 'ExamPeriodProctor') => 'Không tìm thấy cán bộ coi thi',
                default => 'Không tìm thấy dữ liệu yêu cầu'
            };
            return response()->json([
                'status' => 'error',
                'message' => $message
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error submitting exam result:', [
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi nộp bài thi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 