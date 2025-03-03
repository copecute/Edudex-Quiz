<?php

namespace App\Http\Controllers;

use App\Models\ExamShift;
use App\Models\ExamPeriod;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExamShiftController extends Controller
{
    public function index(ExamPeriod $examPeriod)
    {
        $shifts = $examPeriod->examShifts()
            ->search(request('search'))
            ->orderBy('start_time')
            ->paginate(10);

        return view('exam_shifts.index', compact('examPeriod', 'shifts'));
    }

    public function create(ExamPeriod $examPeriod)
    {
        return view('exam_shifts.create', compact('examPeriod'));
    }

    public function store(Request $request, ExamPeriod $examPeriod)
    {
        $validated = $request->validate([
            'exam_period_id' => 'required|exists:exam_periods,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($examPeriod) {
                    if (strtotime($value) < strtotime($examPeriod->start_time)) {
                        $fail('Thời gian bắt đầu ca thi không được trước thời gian bắt đầu kỳ thi');
                    }
                    if (strtotime($value) > strtotime($examPeriod->end_time)) {
                        $fail('Thời gian bắt đầu ca thi không được sau thời gian kết thúc kỳ thi');
                    }
                },
            ],
            'end_time' => [
                'required',
                'date',
                'after:start_time',
                function ($attribute, $value, $fail) use ($examPeriod) {
                    if (strtotime($value) > strtotime($examPeriod->end_time)) {
                        $fail('Thời gian kết thúc ca thi không được sau thời gian kết thúc kỳ thi');
                    }
                },
            ],
        ]);

        $examPeriod->examShifts()->create($validated);

        return redirect()->route('exam-shifts.index', $examPeriod)
            ->with('success', 'Thêm ca thi thành công!');
    }

    public function edit(ExamPeriod $examPeriod, ExamShift $examShift)
    {
        return view('exam_shifts.edit', compact('examPeriod', 'examShift'));
    }

    public function update(Request $request, ExamPeriod $examPeriod, ExamShift $examShift)
    {
        $validated = $request->validate([
            'exam_period_id' => 'required|exists:exam_periods,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($examPeriod) {
                    if (strtotime($value) < strtotime($examPeriod->start_time)) {
                        $fail('Thời gian bắt đầu ca thi không được trước thời gian bắt đầu kỳ thi');
                    }
                    if (strtotime($value) > strtotime($examPeriod->end_time)) {
                        $fail('Thời gian bắt đầu ca thi không được sau thời gian kết thúc kỳ thi');
                    }
                },
            ],
            'end_time' => [
                'required',
                'date',
                'after:start_time',
                function ($attribute, $value, $fail) use ($examPeriod) {
                    if (strtotime($value) > strtotime($examPeriod->end_time)) {
                        $fail('Thời gian kết thúc ca thi không được sau thời gian kết thúc kỳ thi');
                    }
                },
            ],
        ]);

        $examShift->update($validated);

        return redirect()->route('exam-shifts.index', $examPeriod)
            ->with('success', 'Cập nhật ca thi thành công!');
    }

    public function destroy(ExamPeriod $examPeriod, ExamShift $examShift)
    {
        try {
            $examShift->delete();
            return back()->with('success', 'Xóa ca thi thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi xóa ca thi!');
        }
    }

    public function toggleStatus(ExamPeriod $examPeriod, ExamShift $examShift)
    {
        $examShift->update([
            'is_active' => !$examShift->is_active
        ]);

        $message = $examShift->is_active ? 'Mở khóa ca thi thành công!' : 'Khóa ca thi thành công!';
        return back()->with('success', $message);
    }
} 