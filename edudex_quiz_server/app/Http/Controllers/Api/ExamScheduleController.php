<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamPeriod;
use App\Models\ExamPeriodProctor;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\ExamShift;
use App\Models\ExamPeriodRoom;

class ExamScheduleController extends Controller
{
    public function students(Request $request, ExamShift $shift, ExamPeriodRoom $room)
    {
        try {
            // Kiểm tra CBCT có được phân công cho phòng thi này không
            $proctor = $request->user();
            $proctorId = ExamPeriodProctor::where('exam_period_id', $shift->exam_period_id)
                ->where('account_id', $proctor->id)
                ->value('id');

            $isAssigned = DB::table('exam_shift_rooms')
                ->where('exam_shift_id', $shift->id)
                ->where('exam_period_room_id', $room->id)
                ->where('exam_period_proctor_id', $proctorId)
                ->exists();

            if (!$isAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không được phân công coi thi phòng này'
                ], 403);
            }

            // Lấy danh sách thí sinh
            $students = DB::table('exam_period_room_students as eprs')
                ->join('exam_period_subject_students as epss', 'eprs.exam_period_subject_student_id', '=', 'epss.id')
                ->where('eprs.exam_shift_id', $shift->id)
                ->where('eprs.exam_period_room_id', $room->id)
                ->select([
                    'epss.exam_code',
                    'epss.student_code',
                    'epss.full_name',
                    'epss.birthday as date_of_birth',
                    'epss.gender',
                    'epss.phone',
                    'epss.address',
                    'eprs.seat_number'
                ])
                ->orderBy('eprs.seat_number')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $students->map(function($student) {
                    return [
                        'exam_code' => $student->exam_code,
                        'student_code' => $student->student_code,
                        'full_name' => $student->full_name,
                        'date_of_birth' => $student->date_of_birth ? Carbon::parse($student->date_of_birth)->format('d/m/Y') : null,
                        'gender' => $student->gender ? 'Nam' : 'Nữ',
                        'phone' => $student->phone,
                        'address' => $student->address,
                        'seat_number' => $student->seat_number
                    ];
                })
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting room students', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách thí sinh: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exam(Request $request, ExamShift $shift, ExamPeriodRoom $room)
    {
        try {
            // Log input parameters
            \Log::info('Exam info request', [
                'shift_id' => $shift->id,
                'room_id' => $room->id
            ]);

            // Kiểm tra CBCT có được phân công cho phòng thi này không
            $proctor = $request->user();
            $proctorId = ExamPeriodProctor::where('exam_period_id', $shift->exam_period_id)
                ->where('account_id', $proctor->id)
                ->value('id');
            
            \Log::info('Proctor check', [
                'proctor_id' => $proctorId,
                'exam_period_id' => $shift->exam_period_id,
                'account_id' => $proctor->id
            ]);

            $shiftRoom = DB::table('exam_shift_rooms')
                ->where('exam_shift_id', $shift->id)
                ->where('exam_period_room_id', $room->id)
                ->where('exam_period_proctor_id', $proctorId)
                ->first();
            
            \Log::info('Shift room check', [
                'shift_room' => $shiftRoom
            ]);

            if (!$shiftRoom) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không được phân công coi thi phòng này'
                ], 403);
            }

            // Debug step 1: Kiểm tra thông tin môn thi và đề thi
            $examBasicInfo = DB::table('exam_shift_rooms as esr')
                ->join('exam_period_subjects as eps', 'esr.exam_period_subject_id', '=', 'eps.id')
                ->join('subjects as s', 'eps.subject_id', '=', 's.id')
                ->join('exams as e', 'eps.exam_id', '=', 'e.id')
                ->where('esr.exam_shift_id', $shift->id)
                ->where('esr.exam_period_room_id', $room->id)
                ->select([
                    'eps.id as subject_id',
                    's.code as subject_code',
                    's.name as subject_name',
                    'e.id as exam_id',
                    'e.name as exam_name',
                    'e.duration',
                    'e.total_questions',
                    'e.description as exam_description'
                ])
                ->first();

            \Log::info('Basic exam info', [
                'exam_info' => $examBasicInfo
            ]);

            if (!$examBasicInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin môn thi và đề thi'
                ], 404);
            }

            // Debug step 2: Kiểm tra câu hỏi của đề thi
            $questions = DB::table('exam_tags as et')
                ->join('question_tag as qt', 'et.tag_id', '=', 'qt.tag_id')
                ->join('questions as q', 'qt.question_id', '=', 'q.id')
                ->where('et.exam_id', $examBasicInfo->exam_id)
                ->where('q.subject_code', $examBasicInfo->subject_code)
                ->select([
                    'q.id as question_id',
                    'q.content',
                    'q.link_media',
                    'q.difficulty as type',
                    'et.num_questions as score'
                ])
                ->orderBy('q.id')
                ->get();

            \Log::info('Questions info', [
                'exam_id' => $examBasicInfo->exam_id,
                'subject_code' => $examBasicInfo->subject_code,
                'questions_count' => $questions->count()
            ]);

            if ($questions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy câu hỏi cho đề thi'
                ], 404);
            }

            // Format dữ liệu trả về
            $examData = [
                'subject' => [
                    'id' => $examBasicInfo->subject_id,
                    'code' => $examBasicInfo->subject_code,
                    'name' => $examBasicInfo->subject_name
                ],
                'exam' => [
                    'id' => $examBasicInfo->exam_id,
                    'name' => $examBasicInfo->exam_name,
                    'duration' => $examBasicInfo->duration,
                    'total_questions' => $examBasicInfo->total_questions,
                    'description' => $examBasicInfo->exam_description
                ],
                'questions' => $questions->map(function($question) {
                    return [
                        'id' => $question->question_id,
                        'content' => $question->content,
                        'type' => $question->type,
                        'media' => $question->link_media,
                        'score' => $question->score
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'data' => $examData
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting exam info', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy thông tin đề thi: ' . $e->getMessage()
            ], 500);
        }
    }
} 