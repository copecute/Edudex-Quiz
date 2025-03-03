<?php

namespace App\Http\Controllers;

use App\Models\Major;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MajorsExport;
use App\Imports\MajorsImport;
use App\Exports\MajorsTemplateExport;

class MajorController extends Controller
{
    public function index(Request $request)
    {
        $query = Major::with('faculty');

        // Tìm kiếm
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Lọc theo khoa
        if ($request->faculty_id) {
            $query->where('faculty_id', $request->faculty_id);
        }

        $majors = $query->paginate(10);
        $faculties = Faculty::all();
        return view('majors.index', compact('majors', 'faculties'));
    }

    public function create()
    {
        $faculties = Faculty::all();
        return view('majors.create', compact('faculties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:majors',
            'name' => 'required',
            'faculty_id' => 'required|exists:faculties,id',
            'description' => 'nullable'
        ]);

        try {
            Major::create($request->all());
            return redirect()->route('majors.index')
                ->with('success', 'Thêm ngành thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi thêm ngành!');
        }
    }

    public function edit(Major $major)
    {
        $faculties = Faculty::all();
        return view('majors.edit', compact('major', 'faculties'));
    }

    public function update(Request $request, Major $major)
    {
        $request->validate([
            'code' => 'required|unique:majors,code,'.$major->id,
            'name' => 'required',
            'faculty_id' => 'required|exists:faculties,id',
            'description' => 'nullable'
        ]);

        try {
            $major->update($request->all());
            return redirect()->route('majors.index')
                ->with('success', 'Cập nhật ngành thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật ngành!');
        }
    }

    public function destroy(Major $major)
    {
        try {
            $major->delete();
            return redirect()->route('majors.index')
                ->with('success', 'Xóa ngành thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi xóa ngành!');
        }
    }

    public function export()
    {
        return Excel::download(new MajorsExport, 'nganh.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new MajorsImport, $request->file('file'));
            return back()->with('success', 'Import dữ liệu thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi import: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new MajorsTemplateExport, 'template_nganh.xlsx');
    }

    public function importExportTools()
    {
        return view('majors.tools');
    }
} 