<?php

namespace App\Http\Controllers;

use App\Models\TestSession;
use App\Models\Subject;
use App\Models\TestRoom;
use App\Models\TestSessionSubject;
use Illuminate\Http\Request;

class TestSessionSubjectController extends Controller
{
    public function index(TestSession $testSession)
    {
        $subjects = $testSession->subjects()
            ->with(['testSessionSubjects' => function($query) use ($testSession) {
                $query->where('test_session_id', $testSession->id)
                      ->withCount(['testShifts' => function($q) use ($testSession) {
                          $q->where('test_shifts.test_session_id', $testSession->id);
                      }]);
            }])
            ->with(['testShiftSubjectRooms' => function($query) use ($testSession) {
                $query->whereHas('testShift', function($q) use ($testSession) {
                    $q->where('test_shifts.test_session_id', $testSession->id);
                });
            }, 'testShiftSubjectRooms.testShift', 'testShiftSubjectRooms.testRoom'])
            ->get()
            ->map(function($subject) {
                // Lấy số ca thi từ test_session_subject
                $subject->test_shifts_count = $subject->testSessionSubjects->first()->test_shifts_count ?? 0;
                return $subject;
            });

        // Lấy danh sách môn học chưa được thêm vào kỳ thi
        $availableSubjects = Subject::whereDoesntHave('testSessions', function($q) use ($testSession) {
            $q->where('test_sessions.id', $testSession->id);
        })->get();

        // Lấy danh sách phòng thi khả dụng
        $availableRooms = TestRoom::whereHas('testLocation', function($q) {
            $q->where('is_active', true);
        })
        ->where('is_active', true)
        ->orderBy('test_location_id')
        ->orderBy('name')
        ->get()
        ->groupBy('test_location.name');

        return view('test_sessions.subjects.index', compact(
            'testSession', 
            'subjects',
            'availableSubjects',
            'availableRooms'
        ));
    }

    public function store(Request $request, TestSession $testSession)
    {
        $validated = $request->validate([
            'subjects' => 'required|array',
            'subjects.*' => 'exists:subjects,id'
        ]);

        $testSession->subjects()->attach($validated['subjects']);

        return redirect()
            ->route('test_sessions.subjects.index', $testSession)
            ->with('success', 'Đã thêm môn thi thành công');
    }

    public function destroy(TestSession $testSession, Subject $subject)
    {
        $testSession->subjects()->detach($subject->id);

        return redirect()
            ->route('test_sessions.subjects.index', $testSession)
            ->with('success', 'Đã xóa môn thi thành công');
    }

    public function assignShifts(Request $request, TestSession $testSession, Subject $subject)
    {
        $validated = $request->validate([
            'test_shift_ids' => 'required|array',
            'test_shift_ids.*' => 'exists:test_shifts,id'
        ]);

        try {
            // Lấy test_session_subject trực tiếp từ model thay vì qua pivot
            $testSessionSubject = TestSessionSubject::where('test_session_id', $testSession->id)
                ->where('subject_id', $subject->id)
                ->firstOrFail();

            // Sync ca thi cho môn học
            $testSessionSubject->testShifts()->sync($validated['test_shift_ids']);

            return back()->with('success', 'Đã phân ca thi thành công');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
} 