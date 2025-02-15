<?php

namespace App\Http\Controllers;

use App\Models\TestSession;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestSessionController extends Controller
{
    public function index(Request $request)
    {
        $query = TestSession::query();

        // Tìm kiếm theo tên
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Lọc theo trạng thái
        if ($status = $request->input('status')) {
            $now = now();
            switch($status) {
                case 'upcoming':
                    $query->where('start_time', '>', $now);
                    break;
                case 'ongoing':
                    $query->where('start_time', '<=', $now)
                          ->where('end_time', '>=', $now);
                    break;
                case 'ended':
                    $query->where('end_time', '<', $now);
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
            }
        }

        $testSessions = $query->latest()->paginate(10);
        
        return view('test_sessions.index', compact('testSessions'));
    }

    public function create()
    {
        return view('test_sessions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'is_active' => 'boolean'
        ]);

        TestSession::create($validated);

        return redirect()
            ->route('test_sessions.index')
            ->with('success', 'Đã tạo kỳ thi mới thành công');
    }

    public function edit(TestSession $testSession)
    {
        return view('test_sessions.edit', compact('testSession'));
    }

    public function update(Request $request, TestSession $testSession)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'is_active' => 'boolean'
        ]);

        $testSession->update($validated);

        return redirect()
            ->route('test_sessions.index')
            ->with('success', 'Đã cập nhật kỳ thi thành công');
    }

    public function destroy(TestSession $testSession)
    {
        $testSession->delete();
        return redirect()
            ->route('test_sessions.index')
            ->with('success', 'Đã xóa kỳ thi thành công');
    }

    public function toggleStatus(TestSession $testSession)
    {
        $testSession->update(['is_active' => !$testSession->is_active]);
        return redirect()
            ->route('test_sessions.index')
            ->with('success', 'Đã thay đổi trạng thái kỳ thi thành công');
    }
} 