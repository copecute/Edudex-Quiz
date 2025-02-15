<?php

namespace App\Http\Controllers;

use App\Models\TestLocation;
use Illuminate\Http\Request;

class TestLocationController extends Controller
{
    public function index(Request $request)
    {
        $query = TestLocation::query();

        // Tìm kiếm theo tên hoặc địa chỉ
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
        }

        // Lọc theo trạng thái
        if ($status = $request->input('status')) {
            $query->where('is_active', $status === 'active');
        }

        $testLocations = $query->latest()->paginate(10);
        
        return view('test_locations.index', compact('testLocations'));
    }

    public function create()
    {
        return view('test_locations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        TestLocation::create($validated);

        return redirect()
            ->route('test_locations.index')
            ->with('success', 'Đã tạo địa điểm thi mới thành công');
    }

    public function edit(TestLocation $testLocation)
    {
        return view('test_locations.edit', compact('testLocation'));
    }

    public function update(Request $request, TestLocation $testLocation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $testLocation->update($validated);

        return redirect()
            ->route('test_locations.index')
            ->with('success', 'Đã cập nhật địa điểm thi thành công');
    }

    public function destroy(TestLocation $testLocation)
    {
        $testLocation->delete();
        return redirect()
            ->route('test_locations.index')
            ->with('success', 'Đã xóa địa điểm thi thành công');
    }

    public function toggleStatus(TestLocation $testLocation)
    {
        $testLocation->update(['is_active' => !$testLocation->is_active]);
        return redirect()
            ->route('test_locations.index')
            ->with('success', 'Đã thay đổi trạng thái địa điểm thi thành công');
    }
} 