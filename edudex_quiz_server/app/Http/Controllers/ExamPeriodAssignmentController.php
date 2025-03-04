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
            ->with(['subjects', 'rooms'])
            ->get();
            
        $rooms = $examPeriod->rooms;

        return view('exam_periods.assignment.rooms', compact('examPeriod', 'shifts', 'rooms'));
    }

    // Xử lý phân công phòng thi cho ca thi
    public function assignRooms(Request $request, ExamPeriod $examPeriod)
    {
        $validated = $request->validate([
            'assignments' => 'required|array',
            'assignments.*.shift_id' => 'required|exists:exam_shifts,id',
            'assignments.*.room_ids' => 'required|array',
            'assignments.*.room_ids.*' => 'exists:exam_period_rooms,id'
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['assignments'] as $assignment) {
                $shift = ExamShift::findOrFail($assignment['shift_id']);
                $shift->rooms()->sync($assignment['room_ids']);
            }
            DB::commit();
            return redirect()->back()->with('success', 'Phân công phòng thi thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi phân công phòng thi');
        }
    }

    // Hiển thị form phân công CBCT cho phòng thi
    public function proctors(ExamPeriod $examPeriod)
    {
        $shifts = $examPeriod->examShifts()
            ->with(['subjects.subject', 'rooms' => function($query) {
                $query->withPivot('id');
            }, 'rooms.room', 'rooms.proctors'])
            ->get();
            
        $proctors = $examPeriod->proctors()
            ->with('account.accountInfo')
            ->get();

        return view('exam_periods.assignment.proctors', compact('examPeriod', 'shifts', 'proctors'));
    }

    // Xử lý phân công CBCT cho phòng thi
    public function assignProctors(Request $request, ExamPeriod $examPeriod)
    {
        \Log::info('Request data:', $request->all());

        // Kiểm tra xem có shift_room_id nào không
        $hasShiftRoomIds = collect($request->input('assignments', []))
            ->filter(function ($assignment) {
                return !empty($assignment['shift_room_id']);
            })
            ->isNotEmpty();

        if (!$hasShiftRoomIds) {
            \Log::error('Không tìm thấy shift_room_id trong request');
            return redirect()->back()->with('error', 'Không tìm thấy thông tin phòng thi');
        }

        $validated = $request->validate([
            'assignments' => 'required|array',
            'assignments.*.shift_room_id' => 'required|exists:exam_shift_rooms,id',
            'assignments.*.proctor_id' => 'nullable|exists:exam_period_proctors,id'
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['assignments'] as $assignment) {
                if (empty($assignment['proctor_id'])) {
                    DB::table('exam_shift_room_proctors')
                        ->where('exam_shift_room_id', $assignment['shift_room_id'])
                        ->delete();
                } else {
                    DB::table('exam_shift_room_proctors')->updateOrInsert(
                        [
                            'exam_shift_room_id' => $assignment['shift_room_id'],
                            'exam_period_proctor_id' => $assignment['proctor_id']
                        ],
                        [
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                }
            }
            DB::commit();
            return redirect()->back()->with('success', 'Phân công CBCT thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Lỗi phân công CBCT: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi phân công CBCT');
        }
    }
} 