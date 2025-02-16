<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentAuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'student_code' => 'required|exists:students,code',
            'exam_code' => 'required'
        ]);

        // Tìm sinh viên theo mã sinh viên và status = 1
        $student = Student::where('code', $validated['student_code'])
            ->where('status', true) // Chỉ cho phép sinh viên đang học đăng nhập
            ->with([
                'majors',
                'testSessionSubjects' => function($q) use ($validated) {
                    $q->whereHas('testSession', function($q) {
                        $q->where('is_active', true); // Chỉ lấy kỳ thi đang hoạt động
                    })
                    ->whereHas('students', function($q) use ($validated) {
                        $q->where('test_session_subject_students.exam_code', $validated['exam_code']);
                    });
                },
                'testSessionSubjects.testSession',
                'testSessionSubjects.subject',
                'testSessionSubjects.testShiftSubjectRooms.testRoom.testLocation',
                'testSessionSubjects.testShiftSubjectRooms.testShift'
            ])
            ->first();

        if (!$student) {
            return response()->json([
                'message' => 'Không tìm thấy sinh viên hoặc sinh viên đã nghỉ học'
            ], 404);
        }

        // Kiểm tra số báo danh trong kỳ thi đang hoạt động
        $hasValidExamCode = $student->testSessionSubjects()
            ->whereHas('testSession', function($q) {
                $q->where('is_active', true);
            })
            ->wherePivot('exam_code', $validated['exam_code'])
            ->exists();

        if (!$hasValidExamCode) {
            return response()->json([
                'message' => 'Số báo danh không đúng hoặc kỳ thi không hoạt động'
            ], 401);
        }

        // Tạo token cho sinh viên
        $token = $student->createToken('student-token')->plainTextToken;

        // Chuẩn bị dữ liệu trả về
        $examInfo = $student->testSessionSubjects->map(function($testSessionSubject) use ($student) {
            // Lấy thông tin từ bảng trung gian
            $pivotData = DB::table('test_session_subject_students')
                ->where('test_session_subject_id', $testSessionSubject->id)
                ->where('student_id', $student->id)
                ->first();

            // Lấy thông tin phòng thi
            $room = $pivotData->test_shift_subject_room_id 
                ? $testSessionSubject->testShiftSubjectRooms
                    ->where('id', $pivotData->test_shift_subject_room_id)
                    ->first()
                : null;
            
            return [
                'test_session' => [
                    'id' => $testSessionSubject->testSession->id,
                    'name' => $testSessionSubject->testSession->name,
                    'start_date' => $testSessionSubject->testSession->start_date,
                    'end_date' => $testSessionSubject->testSession->end_date,
                ],
                'subject' => [
                    'id' => $testSessionSubject->subject->id,
                    'code' => $testSessionSubject->subject->code,
                    'name' => $testSessionSubject->subject->name,
                ],
                'exam_code' => $pivotData->exam_code,
                'room' => $room ? [
                    'name' => $room->testRoom->name,
                    'location' => $room->testRoom->testLocation->name,
                    'shift' => [
                        'name' => $room->testShift->name,
                        'start_time' => $room->testShift->start_time,
                        'end_time' => $room->testShift->end_time,
                    ]
                ] : null
            ];
        });

        return response()->json([
            'token' => [
                'access_token' => $token,
                'token_type' => 'copecute'
            ],
            'student' => [
                'id' => $student->id,
                'code' => $student->code,
                'name' => $student->name,
                'email' => $student->email,
                'phone' => $student->phone,
                'address' => $student->address,
                'birthday' => $student->birthday?->format('Y-m-d'), // Format lại ngày tháng
                'gender' => $student->gender, // true: Nam, false: Nữ
                'avatar_url' => $student->avatar_url, // Sử dụng accessor đã định nghĩa
                'majors' => $student->majors->map(function($major) {
                    return [
                        'id' => $major->id,
                        'name' => $major->name,
                        'is_main' => $major->pivot->is_main
                    ];
                })
            ],
            'exams' => $examInfo
        ]);
    }

    public function profile(Request $request)
    {
        $student = $request->user();
        
        return response()->json([
            'student' => [
                'id' => $student->id,
                'code' => $student->code,
                'name' => $student->name,
                'email' => $student->email,
                'phone' => $student->phone,
                'address' => $student->address,
                'birthday' => $student->birthday?->format('Y-m-d'),
                'gender' => $student->gender,
                'avatar_url' => $student->avatar_url,
                'majors' => $student->majors->map(function($major) {
                    return [
                        'id' => $major->id,
                        'name' => $major->name,
                        'is_main' => $major->pivot->is_main
                    ];
                })
            ]
        ]);
    }
} 