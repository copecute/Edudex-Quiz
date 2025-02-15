<?php

namespace App\Http\Controllers;

use App\Models\TestRoom;
use App\Models\TestLocation;
use Illuminate\Http\Request;

class TestRoomController extends Controller
{
    public function index(Request $request)
    {
        $query = TestRoom::query()->with('testLocation');
        
        // Lọc theo địa điểm
        if ($locationId = $request->input('location')) {
            $query->where('test_location_id', $locationId);
        }

        // Tìm kiếm theo mã hoặc tên phòng
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Lọc theo trạng thái
        if ($status = $request->input('status')) {
            $query->where('is_active', $status === 'active');
        }

        $testRooms = $query->latest()->paginate(10);
        $testLocations = TestLocation::where('is_active', true)->get();
        
        return view('test_rooms.index', compact('testRooms', 'testLocations'));
    }

    public function create()
    {
        $testLocations = TestLocation::where('is_active', true)->get();
        return view('test_rooms.create', compact('testLocations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'test_location_id' => 'required|exists:test_locations,id',
            'is_active' => 'boolean'
        ]);

        TestRoom::create($validated);

        return redirect()
            ->route('test_rooms.index', ['location' => $request->test_location_id])
            ->with('success', 'Đã tạo phòng thi mới thành công');
    }

    public function edit(TestRoom $testRoom)
    {
        $testLocations = TestLocation::where('is_active', true)->get();
        return view('test_rooms.edit', compact('testRoom', 'testLocations'));
    }

    public function update(Request $request, TestRoom $testRoom)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'test_location_id' => 'required|exists:test_locations,id',
            'is_active' => 'boolean'
        ]);

        $testRoom->update($validated);

        return redirect()
            ->route('test_rooms.index', ['location' => $request->test_location_id])
            ->with('success', 'Đã cập nhật phòng thi thành công');
    }

    public function destroy(TestRoom $testRoom)
    {
        $locationId = $testRoom->test_location_id;
        $testRoom->delete();
        
        return redirect()
            ->route('test_rooms.index', ['location' => $locationId])
            ->with('success', 'Đã xóa phòng thi thành công');
    }

    public function toggleStatus(TestRoom $testRoom)
    {
        $testRoom->update(['is_active' => !$testRoom->is_active]);
        
        return redirect()
            ->route('test_rooms.index', ['location' => $testRoom->test_location_id])
            ->with('success', 'Đã thay đổi trạng thái phòng thi thành công');
    }
} 