<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Facility;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RoomsExport;
use App\Imports\RoomsImport;
use App\Exports\RoomsTemplateExport;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $query = Room::with('facility');

        // Tìm kiếm
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhereHas('facility', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Lọc theo cơ sở
        if ($request->facility_id) {
            $query->where('facility_id', $request->facility_id);
        }

        // Lọc theo trạng thái
        if ($request->has('status')) {
            $query->where('is_active', $request->status);
        }

        $rooms = $query->paginate(10);
        $facilities = Facility::where('is_active', true)->get();
        
        return view('rooms.index', compact('rooms', 'facilities'));
    }

    public function create()
    {
        $facilities = Facility::where('is_active', true)->get();
        
        if ($facilities->isEmpty()) {
            return redirect()->route('rooms.index')
                ->with('warning', 'Bạn cần thêm ít nhất một cơ sở thi trước khi thêm phòng thi.');
        }
        
        return view('rooms.create', compact('facilities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:rooms',
            'name' => 'required',
            'facility_id' => 'required|exists:facilities,id',
            'description' => 'nullable',
            'capacity' => 'required|integer|min:1'
        ], [
            'capacity.required' => 'Vui lòng nhập sức chứa phòng thi',
            'capacity.integer' => 'Sức chứa phải là số nguyên',
            'capacity.min' => 'Sức chứa phải lớn hơn 0'
        ]);

        Room::create($request->all());
        return redirect()->route('rooms.index')
            ->with('success', 'Thêm phòng thi thành công!');
    }

    public function edit(Room $room)
    {
        $facilities = Facility::where('is_active', true)->get();
        return view('rooms.edit', compact('room', 'facilities'));
    }

    public function update(Request $request, Room $room)
    {
        $request->validate([
            'code' => 'required|unique:rooms,code,'.$room->id,
            'name' => 'required',
            'facility_id' => 'required|exists:facilities,id',
            'description' => 'nullable',
            'capacity' => 'required|integer|min:1'
        ], [
            'capacity.required' => 'Vui lòng nhập sức chứa phòng thi',
            'capacity.integer' => 'Sức chứa phải là số nguyên',
            'capacity.min' => 'Sức chứa phải lớn hơn 0'
        ]);

        $room->update($request->all());
        return redirect()->route('rooms.index')
            ->with('success', 'Cập nhật phòng thi thành công!');
    }

    public function destroy(Room $room)
    {
        $room->delete();
        return redirect()->route('rooms.index')
            ->with('success', 'Xóa phòng thi thành công!');
    }

    public function toggleStatus(Room $room)
    {
        try {
            $room->update(['is_active' => !$room->is_active]);
            return response()->json([
                'success' => true,
                'message' => $room->is_active ? 'Mở khóa phòng thi thành công!' : 'Khóa phòng thi thành công!'
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
        return Excel::download(new RoomsExport, 'rooms.xlsx');
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
            Excel::import(new RoomsImport, $request->file('file'));
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
        return view('rooms.tools');
    }

    public function downloadTemplate()
    {
        return Excel::download(new RoomsTemplateExport, 'template_phong_thi.xlsx');
    }
} 