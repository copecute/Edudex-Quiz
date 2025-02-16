<?php

namespace App\Http\Controllers;

use App\Models\TestSession;
use App\Models\TestShift;
use App\Models\TestShiftSubjectRoom;
use App\Models\TestRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestShiftSubjectRoomController extends Controller
{
    public function index(TestSession $testSession, TestShift $testShift)
    {
        // Load môn thi của ca thi
        $testShift->load('testSessionSubjects.subject');
        
        // Lấy danh sách phòng thi khả dụng
        $availableRooms = TestRoom::whereHas('testLocation', function($q) {
            $q->where('is_active', true);
        })
        ->where('is_active', true)
        ->whereDoesntHave('testShiftSubjectRooms', function($q) use ($testShift) {
            $q->where('test_shift_id', $testShift->id);
        })
        ->orderBy('test_location_id')
        ->orderBy('name')
        ->get()
        ->groupBy('test_location.name');

        return view('test_sessions.test_shifts.subject_rooms.index', compact(
            'testSession',
            'testShift',
            'availableRooms'
        ));
    }

    public function store(Request $request, TestSession $testSession, TestShift $testShift)
    {
        $validated = $request->validate([
            'test_session_subject_id' => 'required|exists:test_session_subjects,id',
            'test_room_id' => 'required|exists:test_rooms,id'
        ]);

        try {
            // Kiểm tra xem môn thi đã được phân phòng chưa
            if ($testShift->testShiftSubjectRooms()
                ->where('test_session_subject_id', $validated['test_session_subject_id'])
                ->exists()) {
                return back()->with('error', 'Môn thi này đã được phân phòng');
            }

            // Kiểm tra xem phòng thi đã được sử dụng chưa
            if ($testShift->testShiftSubjectRooms()
                ->where('test_room_id', $validated['test_room_id'])
                ->exists()) {
                return back()->with('error', 'Phòng thi này đã được sử dụng');
            }

            $testShift->testShiftSubjectRooms()->create($validated);

            return back()->with('success', 'Đã phân phòng thi thành công');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function destroy(TestSession $testSession, TestShift $testShift, TestShiftSubjectRoom $subjectRoom)
    {
        try {
            $subjectRoom->delete();
            return back()->with('success', 'Đã xóa phân công phòng thi thành công');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
} 