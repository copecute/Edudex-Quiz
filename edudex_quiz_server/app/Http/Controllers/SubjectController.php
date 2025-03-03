<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Major;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SubjectsExport;
use App\Imports\SubjectsImport;
use App\Exports\SubjectsTemplateExport;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Subject::with('major.faculty');

        // Tìm kiếm
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Lọc theo ngành
        if ($request->major_id) {
            $query->where('major_id', $request->major_id);
        }

        $subjects = $query->paginate(10);
        $majors = Major::with('faculty')->get();
        return view('subjects.index', compact('subjects', 'majors'));
    }

    public function create()
    {
        $majors = Major::with('faculty')->get();
        return view('subjects.create', compact('majors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:subjects',
            'name' => 'required',
            'credits' => 'required|integer|min:1',
            'major_id' => 'required|exists:majors,id',
            'description' => 'nullable'
        ]);

        try {
            Subject::create($request->all());
            return redirect()->route('subjects.index')
                ->with('success', 'Thêm môn học thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi thêm môn học!');
        }
    }

    public function edit(Subject $subject)
    {
        $majors = Major::with('faculty')->get();
        return view('subjects.edit', compact('subject', 'majors'));
    }

    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'code' => 'required|unique:subjects,code,'.$subject->id,
            'name' => 'required',
            'credits' => 'required|integer|min:1',
            'major_id' => 'required|exists:majors,id',
            'description' => 'nullable'
        ]);

        try {
            $subject->update($request->all());
            return redirect()->route('subjects.index')
                ->with('success', 'Cập nhật môn học thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật môn học!');
        }
    }

    public function destroy(Subject $subject)
    {
        try {
            $subject->delete();
            return redirect()->route('subjects.index')
                ->with('success', 'Xóa môn học thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi xóa môn học!');
        }
    }

    public function export()
    {
        return Excel::download(new SubjectsExport, 'mon_hoc.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new SubjectsImport, $request->file('file'));
            return back()->with('success', 'Import dữ liệu thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi import: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new SubjectsTemplateExport, 'template_mon_hoc.xlsx');
    }

    public function importExportTools()
    {
        return view('subjects.tools');
    }
} 