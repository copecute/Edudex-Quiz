<?php

namespace App\Http\Controllers;

use App\Models\ExamPeriod;
use App\Models\ExamPeriodSubject;
use App\Models\ExamShift;
use App\Models\ExamPeriodRoom;
use App\Models\ExamPeriodProctor;
use App\Models\ExamPeriodSubjectStudent;
use App\Models\ExamPeriodRoomStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamPeriodAssignmentController extends Controller
{
    // Hiển thị form phân công môn thi vào ca thi
    public function subjects(ExamPeriod $examPeriod)
    {
        $subjects = $examPeriod->examPeriodSubjects()
            ->with(['subject' => function($query) {
                $query->select('id', 'name', 'code');
            }, 'examShifts'])
            ->get();
            
        $shifts = $examPeriod->examShifts;

        return view('exam_periods.assignment.subjects', compact('examPeriod', 'subjects', 'shifts'));
    }

    // Xử lý phân công môn thi vào ca thi
    public function assignSubjects(Request $request, ExamPeriod $examPeriod)
    {
        $validated = $request->validate([
            'assignments' => 'required|array',
            'assignments.*.subject_id' => 'required|exists:exam_period_subjects,id',
            'assignments.*.shift_ids' => 'required|array',
            'assignments.*.shift_ids.*' => 'exists:exam_shifts,id'
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['assignments'] as $assignment) {
                $subject = ExamPeriodSubject::findOrFail($assignment['subject_id']);
                $subject->examShifts()->sync($assignment['shift_ids']);
            }
            DB::commit();
            return redirect()->back()->with('success', 'Phân công môn thi thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi phân công môn thi');
        }
    }

    // Hiển thị form phân công phòng thi cho ca thi
    public function rooms(ExamPeriod $examPeriod)
    {
        $shifts = $examPeriod->examShifts()
            ->with([
                'subjects.subject', 
                'subjects.students', 
                'rooms.room',
                'rooms' => function($query) {
                    $query->withPivot(['exam_period_subject_id', 'exam_period_proctor_id']);
                }
            ])
            ->get()
            ->map(function ($shift) {
                // Tính tổng số thí sinh trong ca thi
                $totalStudents = $shift->subjects->sum(function ($subject) {
                    return $subject->students->count();
                });
                
                // Tính tổng sức chứa của các phòng đã chọn
                $totalCapacity = $shift->rooms->sum(function ($room) {
                    return $room->room->capacity;
                });

                $shift->total_students = $totalStudents;
                $shift->total_capacity = $totalCapacity;
                return $shift;
            });
            
        $rooms = $examPeriod->rooms()
            ->with('room')
            ->get();

        $proctors = $examPeriod->proctors()
            ->join('accounts', 'exam_period_proctors.account_id', '=', 'accounts.id')
            ->join('account_infos', 'accounts.id', '=', 'account_infos.account_id')
            ->select(
                'exam_period_proctors.id as proctor_id',
                'accounts.username',
                'account_infos.fullName'
            )
            ->get();

        \Log::info('Proctors:', $proctors->toArray());

        return view('exam_periods.assignment.rooms', compact('examPeriod', 'shifts', 'rooms', 'proctors'));
    }

    // Xử lý phân công phòng thi cho ca thi
    public function assignRooms(Request $request, ExamPeriod $examPeriod)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'assignments' => 'required|array',
                'assignments.*.shift_id' => 'required|exists:exam_shifts,id',
                'assignments.*.rooms' => 'required|array',
                'assignments.*.rooms.*.room_id' => 'required|exists:exam_period_rooms,id',
                'assignments.*.rooms.*.subject_id' => 'required|exists:exam_period_subjects,id',
                'assignments.*.rooms.*.proctor_id' => 'nullable|exists:exam_period_proctors,id'
            ], [
                'assignments.*.rooms.*.subject_id.required' => 'Vui lòng chọn môn thi cho tất cả các phòng được chọn',
                'assignments.*.shift_id.required' => 'Thiếu thông tin ca thi',
                'assignments.*.rooms.required' => 'Vui lòng chọn ít nhất một phòng thi',
                'assignments.*.rooms.*.room_id.required' => 'Thiếu thông tin phòng thi',
            ]);

            // Lấy tất cả shift_id từ request
            $shiftIds = collect($validated['assignments'])->pluck('shift_id')->toArray();
            
            // Lấy tất cả room_id được gửi lên theo từng shift
            $assignedRooms = [];
            foreach ($validated['assignments'] as $assignment) {
                $assignedRooms[$assignment['shift_id']] = collect($assignment['rooms'])
                    ->pluck('room_id')
                    ->toArray();
            }

            // Xử lý từng ca thi
            foreach ($shiftIds as $shiftId) {
                $shift = ExamShift::findOrFail($shiftId);
                
                // Tìm các phòng không còn được chọn để xóa
                $currentRooms = $shift->rooms()->pluck('exam_period_rooms.id')->toArray();
                $roomsToDelete = array_diff($currentRooms, $assignedRooms[$shiftId] ?? []);
                
                if (!empty($roomsToDelete)) {
                    DB::table('exam_shift_rooms')
                        ->where('exam_shift_id', $shiftId)
                        ->whereIn('exam_period_room_id', $roomsToDelete)
                        ->delete();
                }
            }

            // Kiểm tra trùng CBCT trong cùng ca thi
            foreach ($validated['assignments'] as $assignment) {
                $shiftId = $assignment['shift_id'];
                $proctorIds = collect($assignment['rooms'])
                    ->pluck('proctor_id')
                    ->filter()
                    ->toArray();
                
                if (count($proctorIds) !== count(array_unique($proctorIds))) {
                    throw new \Exception('Một cán bộ coi thi không thể coi nhiều phòng trong cùng một ca thi');
                }
            }

            // Thêm hoặc cập nhật phân công mới
            foreach ($validated['assignments'] as $assignment) {
                foreach ($assignment['rooms'] as $room) {
                    DB::table('exam_shift_rooms')->updateOrInsert(
                        [
                            'exam_shift_id' => $assignment['shift_id'],
                            'exam_period_room_id' => $room['room_id']
                        ],
                        [
                            'exam_period_subject_id' => $room['subject_id'],
                            'exam_period_proctor_id' => $room['proctor_id'],
                            'updated_at' => now()
                        ]
                    );
                }
            }

            // Xử lý chuyển thí sinh khi chuyển môn thi của phòng
            foreach ($validated['assignments'] as $assignment) {
                foreach ($assignment['rooms'] as $room) {
                    // Kiểm tra xem phòng có thay đổi môn thi không
                    $currentSubject = DB::table('exam_shift_rooms')
                        ->where([
                            'exam_shift_id' => $assignment['shift_id'],
                            'exam_period_room_id' => $room['room_id']
                        ])
                        ->value('exam_period_subject_id');

                    if ($currentSubject && $currentSubject != $room['subject_id']) {
                        // Lấy danh sách thí sinh trong phòng
                        $students = ExamPeriodRoomStudent::where([
                            'exam_shift_id' => $assignment['shift_id'],
                            'exam_period_room_id' => $room['room_id']
                        ])->get();

                        // Xóa phân công cũ
                        ExamPeriodRoomStudent::where([
                            'exam_shift_id' => $assignment['shift_id'],
                            'exam_period_room_id' => $room['room_id']
                        ])->delete();

                        // Tạo phân công mới với môn thi mới
                        foreach ($students as $index => $student) {
                            ExamPeriodRoomStudent::create([
                                'exam_period_id' => $examPeriod->id,
                                'exam_shift_id' => $assignment['shift_id'],
                                'exam_period_room_id' => $room['room_id'],
                                'exam_period_subject_student_id' => $student->exam_period_subject_student_id,
                                'seat_number' => $index + 1
                            ]);
                        }
                    }
                }
            }

            // Xử lý phân công thí sinh
            if ($request->has('student_assignments')) {
                foreach ($request->student_assignments as $assignment) {
                    $shift = ExamShift::with(['subjects', 'rooms'])->findOrFail($assignment['shift_id']);
                    
                    // Lấy danh sách thí sinh của các môn trong ca thi
                    $students = ExamPeriodSubjectStudent::whereIn(
                        'exam_period_subject_id', 
                        $shift->subjects->pluck('id')
                    )->get();

                    if ($assignment['assignment_type'] == 'random') {
                        $students = $students->shuffle();
                    } else {
                        $students = $students->sortBy('exam_code');
                    }

                    // Xóa phân công cũ
                    ExamPeriodRoomStudent::where([
                        'exam_period_id' => $examPeriod->id,
                        'exam_shift_id' => $shift->id
                    ])->delete();

                    // Phân công thí sinh vào phòng
                    $this->assignStudentsToRooms($examPeriod, $shift, $students);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Phân công phòng thi và thí sinh thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    private function assignStudentsToRooms($examPeriod, $shift, $students)
    {
        // Lấy thông tin phân công phòng thi - môn thi
        $roomSubjects = DB::table('exam_shift_rooms')
            ->where('exam_shift_id', $shift->id)
            ->get()
            ->pluck('exam_period_subject_id', 'exam_period_room_id')
            ->toArray();

        // Nhóm thí sinh theo môn thi
        $studentsBySubject = $students->groupBy('exam_period_subject_id');

        // Phân công thí sinh vào phòng theo môn thi
        foreach ($roomSubjects as $roomId => $subjectId) {
            if (!isset($studentsBySubject[$subjectId])) {
                continue;
            }

            $room = ExamPeriodRoom::with('room')->find($roomId);
            $studentsForSubject = $studentsBySubject[$subjectId];
            $seatNumber = 1;

            foreach ($studentsForSubject as $student) {
                // Kiểm tra sức chứa phòng thi
                if ($seatNumber > $room->room->capacity) {
                    break;
                }

                ExamPeriodRoomStudent::create([
                    'exam_period_id' => $examPeriod->id,
                    'exam_shift_id' => $shift->id,
                    'exam_period_room_id' => $roomId,
                    'exam_period_subject_student_id' => $student->id,
                    'seat_number' => $seatNumber++
                ]);
            }
        }
    }

    public function assignStudents(Request $request, ExamPeriod $examPeriod)
    {
        $request->validate([
            'assignment_type' => 'required|in:sequential,random',
            'shift_id' => 'required|exists:exam_shifts,id'
        ]);

        try {
            DB::beginTransaction();

            $shift = ExamShift::with(['subjects', 'rooms'])->findOrFail($request->shift_id);
            
            // Lấy danh sách thí sinh của các môn trong ca thi
            $students = ExamPeriodSubjectStudent::whereIn(
                'exam_period_subject_id', 
                $shift->subjects->pluck('id')
            )->get();

            if ($request->assignment_type == 'random') {
                $students = $students->shuffle();
            } else {
                // Sắp xếp theo số báo danh
                $students = $students->sortBy('exam_code');
            }

            // Lấy danh sách phòng thi và sức chứa
            $rooms = $shift->rooms->map(function($room) {
                return [
                    'room_id' => $room->id,
                    'capacity' => $room->room->capacity,
                    'current_count' => 0
                ];
            })->toArray();

            // Xóa phân công cũ
            ExamPeriodRoomStudent::where([
                'exam_period_id' => $examPeriod->id,
                'exam_shift_id' => $shift->id
            ])->delete();

            // Phân công thí sinh vào phòng
            foreach ($students as $student) {
                // Tìm phòng còn chỗ
                foreach ($rooms as &$room) {
                    if ($room['current_count'] < $room['capacity']) {
                        ExamPeriodRoomStudent::create([
                            'exam_period_id' => $examPeriod->id,
                            'exam_shift_id' => $shift->id,
                            'exam_period_room_id' => $room['room_id'],
                            'exam_period_subject_student_id' => $student->id,
                            'seat_number' => $room['current_count'] + 1
                        ]);
                        $room['current_count']++;
                        break;
                    }
                }
            }

            DB::commit();
            return back()->with('success', 'Phân công thí sinh thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
} 