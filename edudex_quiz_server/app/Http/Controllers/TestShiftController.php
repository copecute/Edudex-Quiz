<?php

namespace App\Http\Controllers;

use App\Models\TestShift;
use App\Models\TestSession;
use App\Models\TestRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestShiftController extends Controller
{
    public function index(Request $request, TestSession $testSession)
    {
        $query = $testSession->testShifts()
            ->with(['testSessionSubjects.subject'])
            ->latest();

        // Tìm kiếm theo tên
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Lọc theo môn học
        if ($subjectId = $request->input('subject')) {
            $query->whereHas('testSessionSubjects.subject', function($q) use ($subjectId) {
                $q->where('subjects.id', $subjectId);
            });
        }

        // Lọc theo trạng thái
        if ($status = $request->input('status')) {
            $query->where('is_active', $status === 'active');
        }

        $testShifts = $query->paginate(10);
        $subjects = $testSession->subjects;

        return view('test_sessions.test_shifts.index', compact('testSession', 'testShifts', 'subjects'));
    }

    public function create(TestSession $testSession)
    {
        // Lấy danh sách môn thi của kỳ thi
        $testSessionSubjects = $testSession->subjects()
            ->withPivot('id')
            ->get();

        // Lấy danh sách phòng thi đang hoạt động
        $availableRooms = TestRoom::whereHas('testLocation', function($q) {
            $q->where('is_active', true);
        })
        ->where('is_active', true)
        ->orderBy('test_location_id')
        ->orderBy('name')
        ->get()
        ->groupBy('test_location.name');

        return view('test_sessions.test_shifts.create', compact(
            'testSession',
            'testSessionSubjects',
            'availableRooms'
        ));
    }

    public function store(Request $request, TestSession $testSession)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'test_session_subject_ids' => 'required|array',
            'test_session_subject_ids.*' => 'exists:test_session_subjects,id',
            'is_active' => 'boolean',
            'rooms' => 'required|array',
            'rooms.*' => 'exists:test_rooms,id'
        ]);

        // Kiểm tra thời gian ca thi nằm trong khoảng thời gian kỳ thi
        if ($request->start_time < $testSession->start_time || 
            $request->end_time > $testSession->end_time) {
            return back()
                ->withInput()
                ->withErrors(['time' => 'Thời gian ca thi phải nằm trong khoảng thời gian của kỳ thi']);
        }

        DB::beginTransaction();
        try {
            // Tạo ca thi
            $testShift = $testSession->testShifts()->create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'is_active' => $validated['is_active'] ?? false
            ]);

            // Gán môn thi cho ca thi
            $testShift->testSessionSubjects()->attach($validated['test_session_subject_ids']);

            // Gán phòng thi cho ca thi
            $testShift->testRooms()->attach($validated['rooms']);

            DB::commit();

            return redirect()
                ->route('test_sessions.test_shifts.index', $testSession)
                ->with('success', 'Đã tạo ca thi mới thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi tạo ca thi: ' . $e->getMessage());
        }
    }

    public function edit(TestSession $testSession, TestShift $testShift)
    {
        // Lấy danh sách phòng thi đang hoạt động
        $availableRooms = TestRoom::whereHas('testLocation', function($q) {
            $q->where('is_active', true);
        })
        ->where('is_active', true)
        ->orderBy('test_location_id')
        ->orderBy('name')
        ->get()
        ->groupBy('test_location.name');

        return view('test_sessions.test_shifts.edit', compact(
            'testSession', 
            'testShift',
            'availableRooms'
        ));
    }

    public function update(Request $request, TestSession $testSession, TestShift $testShift)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'is_active' => 'boolean',
            'rooms' => 'required|array',
            'rooms.*' => 'exists:test_rooms,id'
        ]);

        // Kiểm tra thời gian ca thi nằm trong khoảng thời gian kỳ thi
        if ($request->start_time < $testSession->start_time || 
            $request->end_time > $testSession->end_time) {
            return back()
                ->withInput()
                ->withErrors(['time' => 'Thời gian ca thi phải nằm trong khoảng thời gian của kỳ thi']);
        }

        $testShift->update($validated);
        
        // Cập nhật danh sách phòng thi
        $testShift->testRooms()->sync($validated['rooms']);

        return redirect()
            ->route('test_sessions.test_shifts.index', $testSession)
            ->with('success', 'Đã cập nhật ca thi thành công');
    }

    public function destroy(TestSession $testSession, TestShift $testShift)
    {
        $testShift->delete();
        return redirect()
            ->route('test_sessions.test_shifts.index', $testSession)
            ->with('success', 'Đã xóa ca thi thành công');
    }

    public function toggleStatus(TestSession $testSession, TestShift $testShift)
    {
        $testShift->update(['is_active' => !$testShift->is_active]);
        return redirect()
            ->route('test_sessions.test_shifts.index', $testSession)
            ->with('success', 'Đã thay đổi trạng thái ca thi thành công');
    }

    public function show(TestSession $testSession, TestShift $testShift)
    {
        $testShift->load('testSessionSubjects.subject');
        return view('test_sessions.test_shifts.show', compact('testSession', 'testShift'));
    }
} 