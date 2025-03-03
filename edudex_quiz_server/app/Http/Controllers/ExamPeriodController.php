<?php

namespace App\Http\Controllers;

use App\Models\ExamPeriod;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExamPeriodsExport;
use App\Imports\ExamPeriodsImport;
use App\Exports\ExamPeriodsTemplateExport;

class ExamPeriodController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $examPeriods = ExamPeriod::search($search)
            ->active($status)
            ->orderBy('start_time', 'desc')
            ->paginate(10);

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
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        ExamPeriod::create($validated);

        return redirect()->route('exam-periods.index')
            ->with('success', 'Thêm kỳ thi thành công!');
    }

    public function edit(ExamPeriod $examPeriod)
    {
        return view('exam_periods.edit', compact('examPeriod'));
    }

    public function update(Request $request, ExamPeriod $examPeriod)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $examPeriod->update($validated);

        return redirect()->route('exam-periods.index')
            ->with('success', 'Cập nhật kỳ thi thành công!');
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
} 