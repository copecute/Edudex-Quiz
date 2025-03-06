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
        
        // Lấy thông tin đầy đủ của các phòng đã được phân công
        $assignedRooms = ExamPeriodRoom::with(['room.facility'])
            ->where('exam_period_id', $examPeriod->id)
            ->get()
            ->map(function($examPeriodRoom) {
                return [
                    'id' => $examPeriodRoom->room_id,
                    'code' => $examPeriodRoom->room->code,
                    'name' => $examPeriodRoom->room->name,
                    'facility' => $examPeriodRoom->room->facility->name,
                    'capacity' => $examPeriodRoom->room->capacity
                ];
            });

        if (request()->ajax()) {
            $search = request('search');
            $facilityId = request('facility_id');
            $page = request('page', 1);

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
                    $q->whereHas('facility', function($q) use ($facilityId) {
                        $q->where('id', $facilityId);
                    });
                })
                ->orderBy('code')
                ->paginate(10);

            $assignedRoomIds = ExamPeriodRoom::where('exam_period_id', $examPeriod->id)
                ->pluck('room_id')
                ->toArray();

            return response()->json([
                'html' => view('exam_period_rooms.partials.room_list', compact(
                    'rooms',
                    'assignedRoomIds'
                ))->render(),
                'pagination' => $rooms->links()->toHtml()
            ]);
        }

        // Initial load
        $rooms = Room::with('facility')
            ->where('is_active', true)
            ->orderBy('code')
            ->paginate(10);

        // Lấy danh sách ID phòng đã được phân công
        $assignedRoomIds = ExamPeriodRoom::where('exam_period_id', $examPeriod->id)
            ->pluck('room_id')
            ->toArray();

        return view('exam_period_rooms.assign', compact(
            'examPeriod', 
            'rooms', 
            'facilities', 
            'assignedRoomIds',
            'assignedRooms'
        ));
    }

    public function store(Request $request, ExamPeriod $examPeriod)
    {
        try {
            // Loại bỏ ID trùng lặp từ request
            $newRoomIds = array_values(array_unique($request->input('room_ids', [])));
            
            \Log::info('Start assigning rooms', [
                'exam_period_id' => $examPeriod->id,
                'room_ids' => $newRoomIds
            ]);

            // Lấy danh sách phòng đã được phân công hiện tại
            $currentRoomIds = ExamPeriodRoom::where('exam_period_id', $examPeriod->id)
                ->pluck('room_id')
                ->toArray();

            \Log::info('Current assigned rooms', [
                'current_room_ids' => $currentRoomIds
            ]);

            // Xóa các phòng đã bỏ chọn
            $roomsToDelete = array_values(array_diff($currentRoomIds, $newRoomIds));
            \Log::info('Rooms to delete', [
                'rooms_to_delete' => $roomsToDelete
            ]);

            if (!empty($roomsToDelete)) {
                try {
                    ExamPeriodRoom::where('exam_period_id', $examPeriod->id)
                        ->whereIn('room_id', $roomsToDelete)
                        ->delete();
                } catch (\Exception $e) {
                    \Log::error('Error deleting rooms', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            }

            // Thêm các phòng mới được chọn
            $roomsToAdd = array_values(array_diff($newRoomIds, $currentRoomIds));
            \Log::info('Rooms to add', [
                'rooms_to_add' => $roomsToAdd
            ]);

            if (!empty($roomsToAdd)) {
                try {
                    $data = array_map(function($roomId) use ($examPeriod) {
                        return [
                            'exam_period_id' => $examPeriod->id,
                            'room_id' => $roomId,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }, $roomsToAdd);

                    ExamPeriodRoom::insert($data);
                } catch (\Exception $e) {
                    \Log::error('Error inserting new rooms', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'data' => $data
                    ]);
                    throw $e;
                }
            }

            return redirect()->route('exam-period-rooms.index', $examPeriod)
                ->with('success', 'Cập nhật phân công phòng thi thành công!');
        } catch (\Exception $e) {
            \Log::error('General error in store method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return back()->with('error', 'Có lỗi xảy ra khi phân công phòng thi: ' . $e->getMessage());
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