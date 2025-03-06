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
    public function index(Request $request)
    {
        try {
            // Lấy thông tin cán bộ coi thi đang đăng nhập
            $proctor = $request->user();
            
            \Log::info('Proctor schedule request', [
                'proctor' => $proctor->username
            ]);

            // Lấy các kỳ thi mà CBCT được phân công
            $examPeriods = ExamPeriod::whereHas('proctors', function($query) use ($proctor) {
                $query->where('account_id', $proctor->id);
            })
            ->where(function($query) {
                $now = Carbon::now();
                $query->where('is_active', true)
                    ->where('start_time', '<=', $now)
                    ->where('end_time', '>=', $now);
            })
            ->get();

            // Nếu không có kỳ thi nào
            if ($examPeriods->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bạn chưa được phân công coi thi cho kỳ thi nào.',
                    'data' => []
                ]);
            }

            \Log::info('Found exam periods', [
                'count' => $examPeriods->count(),
                'now' => Carbon::now()->toDateTimeString(),
                'periods' => $examPeriods->map(function($period) {
                    return [
                        'id' => $period->id,
                        'name' => $period->name,
                        'start_time' => $period->start_time,
                        'end_time' => $period->end_time,
                        'is_active' => $period->is_active
                    ];
                })
            ]);

            $schedules = [];
            
            foreach ($examPeriods as $examPeriod) {
                // Lấy ID của exam_period_proctor
                $proctorId = ExamPeriodProctor::where('exam_period_id', $examPeriod->id)
                    ->where('account_id', $proctor->id)
                    ->value('id');

                // Lấy thông tin các phòng thi được phân công
                $rooms = $examPeriod->examShifts()
                    ->with([
                        'rooms' => function($query) use ($proctorId) {
                            $query->where('exam_shift_rooms.exam_period_proctor_id', $proctorId);
                        },
                        'rooms.room.facility',
                        'subjects.subject',
                        'subjects.exam'
                    ])
                    ->get()
                    ->filter(function($shift) {
                        return $shift->rooms->isNotEmpty();
                    });

                if ($rooms->isEmpty()) {
                    continue;
                }

                $examSchedule = [
                    'exam_period' => [
                        'id' => $examPeriod->id,
                        'name' => $examPeriod->name,
                        'start_time' => $examPeriod->start_time->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s'),
                        'end_time' => $examPeriod->end_time->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s')
                    ],
                    'shifts' => []
                ];

                foreach ($rooms as $shift) {
                    $shiftInfo = [
                        'id' => $shift->id,
                        'name' => $shift->name,
                        'start_time' => $shift->start_time->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s'),
                        'end_time' => $shift->end_time->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s'),
                        'rooms' => []
                    ];

                    foreach ($shift->rooms as $room) {
                        $subject = null;
                        if ($room->pivot->exam_period_subject_id) {
                            $subject = $shift->subjects->firstWhere('id', $room->pivot->exam_period_subject_id);
                        }

                        $shiftInfo['rooms'][] = [
                            'id' => $room->id,
                            'code' => $room->room->code,
                            'name' => $room->room->name,
                            'facility' => $room->room->facility->name,
                            'capacity' => $room->room->capacity,
                            'subject' => [
                                'id' => $subject ? $subject->subject_id : null,
                                'name' => $subject ? $subject->subject->name : null,
                                'exam' => $subject ? [
                                    'id' => $subject->exam_id,
                                    'name' => $subject->exam ? $subject->exam->name : null,
                                    'duration' => $subject->exam ? $subject->exam->duration : null,
                                    'total_questions' => $subject->exam ? $subject->exam->total_questions : null
                                ] : null
                            ]
                        ];
                    }

                    $examSchedule['shifts'][] = $shiftInfo;
                }

                $schedules[] = $examSchedule;
            }

            // Nếu không có ca thi nào được phân công
            if (empty($schedules)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bạn chưa được phân công phòng thi cho kỳ thi nào.',
                    'data' => []
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $schedules
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting proctor schedule', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy lịch coi thi: ' . $e->getMessage()
            ], 500);
        }
    }

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
} 