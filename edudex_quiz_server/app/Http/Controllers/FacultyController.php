<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FacultiesExport;
use App\Imports\FacultiesImport;
use App\Exports\FacultiesTemplateExport;

class FacultyController extends Controller
{
    public function index(Request $request)
    {
        $query = Faculty::query();

        // Tìm kiếm
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $faculties = $query->paginate(10);
        return view('faculties.index', compact('faculties'));
    }

    public function create()
    {
        return view('faculties.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:faculties',
            'name' => 'required',
            'description' => 'nullable'
        ]);

        try {
            Faculty::create($request->all());
            return redirect()->route('faculties.index')
                ->with('success', 'Thêm khoa thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi thêm khoa!');
        }
    }

    public function edit(Faculty $faculty)
    {
        return view('faculties.edit', compact('faculty'));
    }

    public function update(Request $request, Faculty $faculty)
    {
        $request->validate([
            'code' => 'required|unique:faculties,code,'.$faculty->id,
            'name' => 'required',
            'description' => 'nullable'
        ]);

        try {
            $faculty->update($request->all());
            return redirect()->route('faculties.index')
                ->with('success', 'Cập nhật khoa thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật khoa!');
        }
    }

    public function destroy(Faculty $faculty)
    {
        try {
            $faculty->delete();
            return redirect()->route('faculties.index')
                ->with('success', 'Xóa khoa thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi xóa khoa!');
        }
    }

    public function export()
    {
        return Excel::download(new FacultiesExport, 'khoa.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new FacultiesImport, $request->file('file'));
            return back()->with('success', 'Import dữ liệu thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi import: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new FacultiesTemplateExport, 'template_khoa.xlsx');
    }

    public function importExportTools()
    {
        return view('faculties.tools');
    }
} 