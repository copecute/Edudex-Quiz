<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ExamPeriod;
use App\Models\ExamPeriodProctor;
use App\Models\ExamShift;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Đăng nhập và tạo token
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        // Kiểm tra thông tin đăng nhập
        if (!Auth::attempt($request->only('username', 'password'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Thông tin đăng nhập không chính xác'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        // Kiểm tra tài khoản có bị khóa không
        if (!$user->is_active) {
            Auth::logout();
            return response()->json([
                'status' => 'error',
                'message' => 'Tài khoản đã bị khóa'
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            // Kiểm tra phân công coi thi
            $examPeriods = ExamPeriod::whereHas('proctors', function($query) use ($user) {
                $query->where('account_id', $user->id);
            })
            ->where(function($query) {
                $now = Carbon::now();
                $query->where('is_active', true)
                    ->where('start_time', '<=', $now)
                    ->where('end_time', '>=', $now);
            })
            ->get();

            // Lấy lịch thi
            $schedules = [];
            foreach ($examPeriods as $examPeriod) {
                $proctorId = ExamPeriodProctor::where('exam_period_id', $examPeriod->id)
                    ->where('account_id', $user->id)
                    ->first();

                if (!$proctorId) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Không tìm thấy thông tin CBCT'
                    ], 404);
                }

                $rooms = $examPeriod->examShifts()
                    ->with([
                        'rooms' => function($query) use ($proctorId) {
                            $query->where('exam_shift_rooms.exam_period_proctor_id', $proctorId->id);
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
                    // kiểm tra ca thi có khoá không
                    if (!$shift->is_active) {
                        continue;
                    }

                    // kiểm tra ca thi có phải hôm nay không
                    if ($shift->start_time->format('Y-m-d') !== Carbon::now()->format('Y-m-d')) {
                        continue;
                    }

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

            // Kiểm tra nếu không có phân công phòng thi nào
            if (empty($schedules) || empty($schedules[0]['shifts'])) {
                Auth::logout();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bạn chưa được phân công phòng thi cho kỳ thi nào.'
                ], Response::HTTP_FORBIDDEN);
            }

            // Tạo token và trả về thông tin
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Đăng nhập thành công',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $proctorId->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                    'info' => [
                        'full_name' => $user->accountInfo->fullName,
                        'date_of_birth' => $user->accountInfo->birthday ? $user->accountInfo->birthday->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s') : null,
                        'gender' => $user->accountInfo->gender,
                        'phone' => $user->accountInfo->phoneNumber,
                        'address' => $user->accountInfo->address,
                        'avatar' => $user->accountInfo->avatar,
                    ],
                    'schedule' => $schedules
                ]
            ]);

        } catch (\Exception $e) {
            Auth::logout();
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi đăng nhập: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Đăng xuất và xóa token
     */
    public function logout(Request $request)
    {
        try {
            // Xóa token hiện tại
            if ($request->user() && $request->user()->currentAccessToken()) {
                $request->user()->currentAccessToken()->delete();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Đăng xuất thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi đăng xuất'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Lấy thông tin người dùng đang đăng nhập
     */
    public function profile(Request $request)
    {
        try {
            $user = $request->user();
            return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                    'info' => [
                        'full_name' => $user->accountInfo->fullName,
                        'date_of_birth' => $user->accountInfo->birthday,
                        'gender' => $user->accountInfo->gender,
                        'phone' => $user->accountInfo->phoneNumber,
                        'address' => $user->accountInfo->address,
                        'avatar' => $user->accountInfo->avatar,
                    ]
                ]
            ]);
        } catch (AuthenticationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Chưa đăng nhập'
            ], Response::HTTP_UNAUTHORIZED);
        }
    }

    // Thêm method mới để lấy lịch thi
    private function getScheduleData($user)
    {
        $examPeriods = ExamPeriod::whereHas('proctors', function($query) use ($user) {
            $query->where('account_id', $user->id);
        })
        ->where(function($query) {
            $now = Carbon::now();
            $query->where('is_active', true)
                ->where('start_time', '<=', $now)
                ->where('end_time', '>=', $now);
        })
        ->get();

        $schedules = [];

        foreach ($examPeriods as $examPeriod) {
            $proctorId = ExamPeriodProctor::where('exam_period_id', $examPeriod->id)
                ->where('account_id', $user->id)
                ->value('id');

            $shifts = ExamShift::where('exam_period_id', $examPeriod->id)
                ->where('is_active', true)
                ->whereHas('rooms', function($query) use ($proctorId) {
                    $query->where('exam_period_proctor_id', $proctorId);
                })
                ->with(['rooms' => function($query) use ($proctorId) {
                    $query->where('exam_period_proctor_id', $proctorId)
                        ->with([
                            'examPeriodRoom.room.facility',
                            'examPeriodSubject.subject',
                            'examPeriodSubject.exam'
                        ]);
                }])
                ->get();

            if ($shifts->isNotEmpty()) {
                $schedules[] = [
                    'exam_period' => [
                        'id' => $examPeriod->id,
                        'name' => $examPeriod->name,
                        'start_time' => $examPeriod->start_time->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s'),
                        'end_time' => $examPeriod->end_time->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s')
                    ],
                    'shifts' => $shifts->map(function($shift) {
                        return [
                            'id' => $shift->id,
                            'name' => $shift->name,
                            'start_time' => $shift->start_time->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s'),
                            'end_time' => $shift->end_time->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s'),
                            'rooms' => $shift->rooms->map(function($shiftRoom) {
                                $room = $shiftRoom->examPeriodRoom->room;
                                $subject = $shiftRoom->examPeriodSubject->subject;
                                $exam = $shiftRoom->examPeriodSubject->exam;
                                
                                return [
                                    'id' => $room->id,
                                    'code' => $room->code,
                                    'name' => $room->name,
                                    'facility' => $room->facility->name,
                                    'capacity' => $room->capacity,
                                    'subject' => [
                                        'id' => $subject->id,
                                        'name' => $subject->name,
                                        'exam' => [
                                            'id' => $exam ? $exam->id : null,
                                            'name' => $exam ? $exam->name : null,
                                            'duration' => $exam ? $exam->duration : null,
                                            'total_questions' => $exam ? $exam->total_questions : null
                                        ]
                                    ]
                                ];
                            })
                        ];
                    })
                ];
            }
        }

        return $schedules;
    }
}