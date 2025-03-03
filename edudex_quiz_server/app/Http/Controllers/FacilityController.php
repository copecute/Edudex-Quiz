<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FacilitiesExport;
use App\Imports\FacilitiesImport;
use App\Exports\FacilitiesTemplateExport;

class FacilityController extends Controller
{
    public function index(Request $request)
    {
        $query = Facility::query();

        // Tìm kiếm
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Lọc theo trạng thái
        if ($request->has('status')) {
            $query->where('is_active', $request->status);
        }

        $facilities = $query->paginate(10);
        return view('facilities.index', compact('facilities'));
    }

    public function create()
    {
        return view('facilities.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:facilities',
            'name' => 'required',
            'address' => 'required',
            'description' => 'nullable'
        ]);

        Facility::create($request->all());
        return redirect()->route('facilities.index')
            ->with('success', 'Thêm cơ sở thành công!');
    }

    public function edit(Facility $facility)
    {
        return view('facilities.edit', compact('facility'));
    }

    public function update(Request $request, Facility $facility)
    {
        $request->validate([
            'code' => 'required|unique:facilities,code,'.$facility->id,
            'name' => 'required',
            'address' => 'required',
            'description' => 'nullable'
        ]);

        $facility->update($request->all());
        return redirect()->route('facilities.index')
            ->with('success', 'Cập nhật cơ sở thành công!');
    }

    public function destroy(Facility $facility)
    {
        $facility->delete();
        return redirect()->route('facilities.index')
            ->with('success', 'Xóa cơ sở thành công!');
    }

    public function toggleStatus(Facility $facility)
    {
        try {
            $facility->update(['is_active' => !$facility->is_active]);
            return response()->json([
                'success' => true,
                'message' => $facility->is_active ? 'Mở khóa cơ sở thành công!' : 'Khóa cơ sở thành công!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi thay đổi trạng thái'
            ], 500);
        }
    }

    public function export()
    {
        return Excel::download(new FacilitiesExport, 'facilities.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ], [
            'file.required' => 'Vui lòng chọn file để import',
            'file.mimes' => 'File phải có định dạng xlsx hoặc xls'
        ]);

        try {
            Excel::import(new FacilitiesImport, $request->file('file'));
            return back()->with('success', 'Import dữ liệu thành công!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            
            foreach ($failures as $failure) {
                $errors[] = "Dòng {$failure->row()}: {$failure->errors()[0]}";
            }
            
            return back()
                ->with('error', 'Import thất bại!')
                ->with('import_errors', $errors);
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function importExportTools()
    {
        return view('facilities.tools');
    }

    public function downloadTemplate()
    {
        return Excel::download(new FacilitiesTemplateExport, 'template_co_so.xlsx');
    }
} 