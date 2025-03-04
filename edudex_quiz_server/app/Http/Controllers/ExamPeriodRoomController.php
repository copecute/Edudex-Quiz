<?php

namespace App\Http\Controllers;

use App\Models\ExamPeriodRoom;
use App\Models\ExamPeriod;
use App\Models\Room;
use App\Models\Facility;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExamPeriodRoomsExport;
use App\Imports\ExamPeriodRoomsImport;
use App\Exports\ExamPeriodRoomsTemplateExport;
use Illuminate\Support\Facades\DB;

class ExamPeriodRoomController extends Controller
{
    public function index(ExamPeriod $examPeriod)
    {
        $facilities = Facility::orderBy('name')->get();
        $search = request('search');
        $facilityId = request('facility_id');

        // Kiểm tra xem có phòng thi nào trong hệ thống không
        $hasRooms = Room::where('is_active', true)->exists();

        $rooms = ExamPeriodRoom::with(['room.facility'])
            ->where('exam_period_id', $examPeriod->id)
            ->search($search)
            ->byFacility($facilityId)
            ->paginate(10)
            ->withQueryString();

        return view('exam_period_rooms.index', compact('examPeriod', 'rooms', 'facilities', 'hasRooms'));
    }

    public function assign(ExamPeriod $examPeriod)
    {
        $facilities = Facility::orderBy('name')->get();
        $search = request('search');
        $facilityId = request('facility_id');

        // Lấy tất cả phòng thi đang hoạt động
        $rooms = Room::with('facility')
            ->where('is_active', true)
            ->when($search, function($q) use ($search) {
                $q->where(function($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhereHas('facility', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->when($facilityId, function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        // Lấy danh sách ID phòng đã được phân công
        $assignedRoomIds = ExamPeriodRoom::where('exam_period_id', $examPeriod->id)
            ->pluck('room_id')
            ->toArray();

        return view('exam_period_rooms.assign', compact('examPeriod', 'rooms', 'facilities', 'assignedRoomIds'));
    }

    public function store(Request $request, ExamPeriod $examPeriod)
    {
        try {
            // Lấy danh sách phòng đã được phân công hiện tại
            $currentRoomIds = ExamPeriodRoom::where('exam_period_id', $examPeriod->id)
                ->pluck('room_id')
                ->toArray();

            // Lấy danh sách phòng mới được chọn
            $newRoomIds = $request->input('room_ids', []);

            // Xóa các phòng đã bỏ chọn
            $roomsToDelete = array_diff($currentRoomIds, $newRoomIds);
            if (!empty($roomsToDelete)) {
                ExamPeriodRoom::where('exam_period_id', $examPeriod->id)
                    ->whereIn('room_id', $roomsToDelete)
                    ->delete();
            }

            // Thêm các phòng mới được chọn
            $roomsToAdd = array_diff($newRoomIds, $currentRoomIds);
            if (!empty($roomsToAdd)) {
                $data = array_map(function($roomId) use ($examPeriod) {
                    return [
                        'exam_period_id' => $examPeriod->id,
                        'room_id' => $roomId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }, $roomsToAdd);

                ExamPeriodRoom::insert($data);
            }

            return redirect()->route('exam-period-rooms.index', $examPeriod)
                ->with('success', 'Cập nhật phân công phòng thi thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi phân công phòng thi!');
        }
    }

    public function destroy(ExamPeriod $examPeriod, Request $request)
    {
        try {
            // Xóa bản ghi trong bảng exam_period_rooms
            ExamPeriodRoom::where('exam_period_id', $examPeriod->id)
                ->where('room_id', $request->room_id)
                ->delete();
                
            return back()->with('success', 'Xóa phòng thi thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi xóa phòng thi!');
        }
    }

    public function destroyMultiple(Request $request, ExamPeriod $examPeriod)
    {
        $roomIds = $request->input('room_ids', []);
        
        try {
            // Xóa nhiều bản ghi trong bảng exam_period_rooms
            ExamPeriodRoom::where('exam_period_id', $examPeriod->id)
                ->whereIn('room_id', $roomIds)
                ->delete();
                
            return back()->with('success', 'Xóa các phòng thi đã chọn thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi xóa phòng thi!');
        }
    }

    public function import(Request $request, ExamPeriod $examPeriod)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new ExamPeriodRoomsImport($examPeriod->id), $request->file('file'));
            return back()->with('success', 'Import danh sách phòng thi thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi import: ' . $e->getMessage());
        }
    }

    public function export(ExamPeriod $examPeriod)
    {
        return Excel::download(
            new ExamPeriodRoomsExport($examPeriod->id),
            'danh-sach-phong-thi-' . $examPeriod->name . '.xlsx'
        );
    }

    public function downloadTemplate(ExamPeriod $examPeriod)
    {
        return Excel::download(
            new ExamPeriodRoomsTemplateExport, 
            'mau-nhap-phong-thi-' . $examPeriod->name . '.xlsx'
        );
    }

    public function importExportTools(ExamPeriod $examPeriod)
    {
        return view('exam_period_rooms.tools', compact('examPeriod'));
    }
} 