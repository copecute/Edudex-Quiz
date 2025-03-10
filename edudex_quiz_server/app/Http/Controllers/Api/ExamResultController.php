<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamPeriod;
use App\Models\ExamResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\ExamShift;

class ExamResultController extends Controller
{
    public function store(Request $request, ExamPeriod $examPeriod)
    {
        try {
            // xác thực các trường cần thiết
            $validated = $request->validate([
                'shift_id' => 'required|integer',
                'room_code' => 'required|string',
                'exam_code' => 'required|string',
                'student_code' => 'required|string',
                'correct_answers' => 'required|integer|min:0',
                'total_questions' => 'required|integer|min:1',
                'score' => 'required|numeric|min:0|max:10',
                'log_file' => 'required|string',
                'note' => 'nullable|string'
            ]);

            // tìm kiếm thí sinh và thông tin liên quan
            $student = DB::table('exam_period_subject_students as epss')
                ->join('exam_period_subjects as eps', 'epss.exam_period_subject_id', '=', 'eps.id')
                ->join('exam_shift_rooms as esr', function($join) use ($validated) {
                    $join->on('eps.id', '=', 'esr.exam_period_subject_id')
                        ->where('esr.exam_shift_id', '=', $validated['shift_id']);
                })
                ->join('exam_period_rooms as epr', function($join) use ($validated) {
                    $join->on('esr.exam_period_room_id', '=', 'epr.id')
                        ->where('epr.id', '=', $validated['room_code']);
                })
                ->join('subjects as s', 'eps.subject_id', '=', 's.id')
                ->where('eps.exam_period_id', $examPeriod->id)
                ->where('epss.exam_code', $validated['exam_code'])
                ->where('epss.student_code', $validated['student_code'])
                ->select(
                    'epss.*',
                    'eps.id as subject_id',
                    'eps.exam_id',
                    'esr.exam_period_proctor_id as proctor_id',
                    'epr.id as room_id',
                    's.code as subject_code'
                )
                ->first();

            if (!$student) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy thông tin thí sinh trong phòng thi và ca thi này',
                    'debug' => [
                        'exam_period_id' => $examPeriod->id,
                        'shift_id' => $validated['shift_id'],
                        'room_code' => $validated['room_code'],
                        'exam_code' => $validated['exam_code'],
                        'student_code' => $validated['student_code']
                    ]
                ], 404);
            }

            // Kiểm tra xem ca thi có phải hôm nay không
            $shift = ExamShift::find($validated['shift_id']);
            if (!$shift || $shift->start_time->format('Y-m-d') !== now()->format('Y-m-d')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bạn chỉ có thể nộp bài trong ngày thi.'
                ], 403);
            }

            // kiểm tra xem thí sinh đã nộp bài chưa
            $existingResult = ExamResult::where([
                'exam_period_id' => $examPeriod->id,
                'exam_period_subject_id' => $student->subject_id,
                'exam_period_subject_student_id' => $student->id
            ])->first();

            if ($existingResult) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Đã ghi nhận bài làm của thí sinh này trước đó',
                    'debug' => [
                        'exam_period_id' => $examPeriod->id,
                        'subject_id' => $student->subject_id,
                        'student_id' => $student->id,
                        'submitted_at' => $existingResult->created_at
                    ]
                ], 409); // HTTP 409 Conflict
            }

            // tạo kết quả thi
            ExamResult::create([
                'exam_period_id' => $examPeriod->id,
                'exam_shift_id' => $validated['shift_id'],
                'exam_period_subject_id' => $student->subject_id,
                'exam_id' => $student->exam_id,
                'exam_period_room_id' => $student->room_id,
                'exam_period_proctor_id' => $student->proctor_id,
                'exam_period_subject_student_id' => $student->id,
                
                'exam_period_code' => $examPeriod->id,
                'exam_shift_code' => $validated['shift_id'],
                'exam_subject_code' => $student->subject_code,
                'exam_code' => $student->exam_id,
                'room_code' => $validated['room_code'],
                'proctor_code' => $student->proctor_id,
                'student_code' => $validated['student_code'],
                
                'correct_answers' => $validated['correct_answers'],
                'total_questions' => $validated['total_questions'],
                'score' => $validated['score'],
                'note' => $validated['note'],
                'log_file' => $validated['log_file']
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Đã lưu kết quả thi thành công'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
} 