<?php

namespace App\Http\Controllers;

use App\Models\TestSession;
use App\Models\TestShift;
use App\Models\TestRoom;
use Illuminate\Http\Request;

class TestSessionRoomController extends Controller
{
    public function index(TestSession $testSession, TestShift $testShift)
    {
        $assignedRooms = $testShift->testRooms()->pluck('test_room_id')->toArray();
        
        // Lấy danh sách phòng thi đang hoạt động
        $availableRooms = TestRoom::whereHas('testLocation', function($q) {
            $q->where('is_active', true);
        })
        ->where('is_active', true)
        ->orderBy('test_location_id')
        ->orderBy('name')
        ->get()
        ->groupBy('test_location.name');

        return view('test_session_rooms.index', compact(
            'testSession', 
            'testShift', 
            'assignedRooms',
            'availableRooms'
        ));
    }

    public function update(Request $request, TestSession $testSession, TestShift $testShift)
    {
        $validated = $request->validate([
            'rooms' => 'required|array',
            'rooms.*' => 'exists:test_rooms,id'
        ]);

        // Cập nhật danh sách phòng thi
        $testShift->testRooms()->sync($validated['rooms']);

        return redirect()
            ->route('test_sessions.test_shifts.test_rooms.index', [$testSession, $testShift])
            ->with('success', 'Đã cập nhật danh sách phòng thi thành công');
    }
} 