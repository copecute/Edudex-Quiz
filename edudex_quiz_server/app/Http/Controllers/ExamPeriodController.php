<?php

namespace App\Http\Controllers;

use App\Models\ExamPeriod;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExamPeriodsExport;
use App\Imports\ExamPeriodsImport;
use App\Exports\ExamPeriodsTemplateExport;
use Carbon\Carbon;

class ExamPeriodController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $status = $request->status;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $examPeriods = ExamPeriod::search($search)
            ->filterByStatus($status)
            ->filterByDateRange($startDate, $endDate)
            ->orderBy('start_time', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('exam_periods.index', compact('examPeriods'));
    }

    public function create()
    {
        return view('exam_periods.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        // Set giờ mặc định
        $validated['start_time'] = Carbon::parse($validated['start_time'])->startOfDay();
        $validated['end_time'] = Carbon::parse($validated['end_time'])->endOfDay();

        ExamPeriod::create($validated);
        return redirect()->route('exam-periods.index')->with('success', 'Thêm kỳ thi thành công!');
    }

    public function edit(ExamPeriod $examPeriod)
    {
        return view('exam_periods.edit', compact('examPeriod'));
    }

    public function update(Request $request, ExamPeriod $examPeriod)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        // Set giờ mặc định
        $validated['start_time'] = Carbon::parse($validated['start_time'])->startOfDay();
        $validated['end_time'] = Carbon::parse($validated['end_time'])->endOfDay();

        $examPeriod->update($validated);
        return redirect()->route('exam-periods.index')->with('success', 'Cập nhật kỳ thi thành công!');
    }

    public function destroy(ExamPeriod $examPeriod)
    {
        try {
            $examPeriod->delete();
            return back()->with('success', 'Xóa kỳ thi thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi xóa kỳ thi!');
        }
    }

    public function toggleStatus(ExamPeriod $examPeriod)
    {
        $examPeriod->update([
            'is_active' => !$examPeriod->is_active
        ]);

        $message = $examPeriod->is_active ? 'Mở khóa kỳ thi thành công!' : 'Khóa kỳ thi thành công!';
        return back()->with('success', $message);
    }

    public function export()
    {
        return Excel::download(new ExamPeriodsExport, 'ky_thi.xlsx');
    }

    public function import(Request $request)
    {
        try {
            Excel::import(new ExamPeriodsImport, $request->file('file'));
            return back()->with('success', 'Import dữ liệu thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi import: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new ExamPeriodsTemplateExport, 'template_ky_thi.xlsx');
    }

    public function importExportTools()
    {
        return view('exam_periods.tools');
    }

    public function dashboard(ExamPeriod $examPeriod)
    {
        // Load các thông tin cần thiết
        $examPeriod->load([
            'examShifts' => function($query) {
                $query->withCount(['subjects', 'rooms']);
            },
            'examPeriodSubjects.subject',
            'rooms.room',
            'proctors.account.accountInfo'
        ]);

        // Tính toán các thống kê
        $stats = [
            'total_shifts' => $examPeriod->examShifts->count(),
            'total_subjects' => $examPeriod->examPeriodSubjects->count(),
            'total_rooms' => $examPeriod->rooms->count(),
            'total_proctors' => $examPeriod->proctors->count(),
        ];

        return view('exam_periods.dashboard', compact('examPeriod', 'stats'));
    }

    public function show(ExamPeriod $examPeriod)
    {
        return redirect()->route('exam-periods.dashboard', $examPeriod);
    }
} 