<?php

namespace App\Http\Controllers;

use App\Models\ExamPeriod;
use App\Models\ExamPeriodSubject;
use App\Models\ExamShift;
use App\Models\ExamPeriodRoom;
use App\Models\ExamPeriodProctor;
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
        \Log::info('Request data:', $request->all());

        try {
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

            DB::beginTransaction();
            try {
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

                DB::commit();
                return redirect()->back()->with('success', 'Phân công phòng thi thành công');
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Error in transaction:', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error:', [
                'errors' => $e->errors()
            ]);
            return back()->with('error', 'Có lỗi xảy ra: ' . collect($e->errors())->first()[0]);
        }
    }
} 