<?php

namespace App\Http\Controllers;

use App\Models\ExamPeriod;
use App\Models\ExamPeriodSubject;
use App\Models\ExamShift;
use App\Models\ExamPeriodRoom;
use App\Models\ExamPeriodProctor;
use App\Models\ExamPeriodSubjectStudent;
use App\Models\ExamPeriodRoomStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExamPeriodAssignmentController extends Controller
{
    // Hiển thị form phân công môn thi vào ca thi
    public function subjects(ExamPeriod $examPeriod)
    {
        $subjects = $examPeriod->examPeriodSubjects()
            ->with(['subject' => function($query) {
                $query->select('id', 'name', 'code');
            }, 'examShifts'])
            ->get();
            
        $shifts = $examPeriod->examShifts;

        return view('exam_periods.assignment.subjects', compact('examPeriod', 'subjects', 'shifts'));
    }

    // Xử lý phân công môn thi vào ca thi
    public function assignSubjects(Request $request, ExamPeriod $examPeriod)
    {
        $validated = $request->validate([
            'assignments' => 'required|array',
            'assignments.*.subject_id' => 'required|exists:exam_period_subjects,id',
            'assignments.*.shift_ids' => 'required|array',
            'assignments.*.shift_ids.*' => 'exists:exam_shifts,id'
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['assignments'] as $assignment) {
                $subject = ExamPeriodSubject::findOrFail($assignment['subject_id']);
                $subject->examShifts()->sync($assignment['shift_ids']);
            }
            DB::commit();
            return redirect()->back()->with('success', 'Phân công môn thi thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi phân công môn thi');
        }
    }

    // Hiển thị form phân công phòng thi cho ca thi
    public function rooms(ExamPeriod $examPeriod)
    {
        $shifts = $examPeriod->examShifts()
            ->with([
                'subjects.subject', 
                'subjects.students', 
                'rooms.room',
                'rooms' => function($query) {
                    $query->withPivot(['exam_period_subject_id', 'exam_period_proctor_id']);
                }
            ])
            ->get()
            ->map(function ($shift) {
                // Tính tổng số thí sinh trong ca thi
                $totalStudents = $shift->subjects->sum(function ($subject) {
                    return $subject->students->count();
                });
                
                // Tính tổng sức chứa của các phòng đã chọn
                $totalCapacity = $shift->rooms->sum(function ($room) {
                    return $room->room->capacity;
                });

                $shift->total_students = $totalStudents;
                $shift->total_capacity = $totalCapacity;
                return $shift;
            });
            
        $rooms = $examPeriod->rooms()
            ->with('room')
            ->get();

        $proctors = $examPeriod->proctors()
            ->join('accounts', 'exam_period_proctors.account_id', '=', 'accounts.id')
            ->join('account_infos', 'accounts.id', '=', 'account_infos.account_id')
            ->select(
                'exam_period_proctors.id as proctor_id',
                'accounts.username',
                'account_infos.fullName'
            )
            ->get();

        \Log::info('Proctors:', $proctors->toArray());

        return view('exam_periods.assignment.rooms', compact('examPeriod', 'shifts', 'rooms', 'proctors'));
    }

    // Xử lý phân công phòng thi cho ca thi
    public function assignRooms(Request $request, ExamPeriod $examPeriod)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'assignments' => 'required|array',
                'assignments.*.shift_id' => 'required|exists:exam_shifts,id',
                'assignments.*.rooms' => 'required|array',
                'assignments.*.rooms.*.room_id' => 'required|exists:exam_period_rooms,id',
                'assignments.*.rooms.*.subject_id' => 'required|exists:exam_period_subjects,id',
                'assignments.*.rooms.*.proctor_id' => 'nullable|exists:exam_period_proctors,id'
            ], [
                'assignments.*.rooms.*.subject_id.required' => 'Vui lòng chọn môn thi cho tất cả các phòng được chọn',
                'assignments.*.shift_id.required' => 'Thiếu thông tin ca thi',
                'assignments.*.rooms.required' => 'Vui lòng chọn ít nhất một phòng thi',
                'assignments.*.rooms.*.room_id.required' => 'Thiếu thông tin phòng thi',
            ]);

            // Lấy tất cả shift_id từ request
            $shiftIds = collect($validated['assignments'])->pluck('shift_id')->toArray();
            
            // Lấy tất cả room_id được gửi lên theo từng shift
            $assignedRooms = [];
            foreach ($validated['assignments'] as $assignment) {
                $assignedRooms[$assignment['shift_id']] = collect($assignment['rooms'])
                    ->pluck('room_id')
                    ->toArray();
            }

            // Xử lý từng ca thi
            foreach ($shiftIds as $shiftId) {
                $shift = ExamShift::findOrFail($shiftId);
                
                // Tìm các phòng không còn được chọn để xóa
                $currentRooms = $shift->rooms()->pluck('exam_period_rooms.id')->toArray();
                $roomsToDelete = array_diff($currentRooms, $assignedRooms[$shiftId] ?? []);
                
                if (!empty($roomsToDelete)) {
                    DB::table('exam_shift_rooms')
                        ->where('exam_shift_id', $shiftId)
                        ->whereIn('exam_period_room_id', $roomsToDelete)
                        ->delete();
                }
            }

            // Kiểm tra trùng CBCT trong cùng ca thi
            foreach ($validated['assignments'] as $assignment) {
                $shiftId = $assignment['shift_id'];
                $proctorIds = collect($assignment['rooms'])
                    ->pluck('proctor_id')
                    ->filter()
                    ->toArray();
                
                if (count($proctorIds) !== count(array_unique($proctorIds))) {
                    throw new \Exception('Một cán bộ coi thi không thể coi nhiều phòng trong cùng một ca thi');
                }
            }

            // Thêm hoặc cập nhật phân công mới
            foreach ($validated['assignments'] as $assignment) {
                foreach ($assignment['rooms'] as $room) {
                    DB::table('exam_shift_rooms')->updateOrInsert(
                        [
                            'exam_shift_id' => $assignment['shift_id'],
                            'exam_period_room_id' => $room['room_id']
                        ],
                        [
                            'exam_period_subject_id' => $room['subject_id'],
                            'exam_period_proctor_id' => $room['proctor_id'],
                            'updated_at' => now()
                        ]
                    );
                }
            }

            // Xử lý chuyển thí sinh khi chuyển môn thi của phòng
            foreach ($validated['assignments'] as $assignment) {
                foreach ($assignment['rooms'] as $room) {
                    // Kiểm tra xem phòng có thay đổi môn thi không
                    $currentSubject = DB::table('exam_shift_rooms')
                        ->where([
                            'exam_shift_id' => $assignment['shift_id'],
                            'exam_period_room_id' => $room['room_id']
                        ])
                        ->value('exam_period_subject_id');

                    if ($currentSubject && $currentSubject != $room['subject_id']) {
                        // Lấy danh sách thí sinh trong phòng
                        $students = ExamPeriodRoomStudent::where([
                            'exam_shift_id' => $assignment['shift_id'],
                            'exam_period_room_id' => $room['room_id']
                        ])->get();

                        // Xóa phân công cũ
                        ExamPeriodRoomStudent::where([
                            'exam_shift_id' => $assignment['shift_id'],
                            'exam_period_room_id' => $room['room_id']
                        ])->delete();

                        // Tạo phân công mới với môn thi mới
                        foreach ($students as $index => $student) {
                            ExamPeriodRoomStudent::create([
                                'exam_period_id' => $examPeriod->id,
                                'exam_shift_id' => $assignment['shift_id'],
                                'exam_period_room_id' => $room['room_id'],
                                'exam_period_subject_student_id' => $student->exam_period_subject_student_id,
                                'seat_number' => $index + 1
                            ]);
                        }
                    }
                }
            }

            // Xử lý phân công thí sinh
            if ($request->has('student_assignments')) {
                foreach ($request->student_assignments as $assignment) {
                    $shift = ExamShift::with(['subjects', 'rooms'])->findOrFail($assignment['shift_id']);
                    
                    // Lấy danh sách thí sinh của các môn trong ca thi
                    $students = ExamPeriodSubjectStudent::whereHas('examPeriodSubject', function($query) use ($shift) {
                        $query->whereHas('examShifts', function($q) use ($shift) {
                            $q->where('exam_shifts.id', $shift->id);
                        });
                    })
                    ->select('exam_period_subject_students.*', 'exam_period_subjects.id as exam_period_subject_id')
                    ->join('exam_period_subjects', 'exam_period_subject_students.exam_period_subject_id', '=', 'exam_period_subjects.id')
                    ->whereNotIn('exam_period_subject_students.id', function($query) use ($shift) {
                        $query->select('exam_period_room_students.exam_period_subject_student_id')
                            ->from('exam_period_room_students')
                            ->where('exam_shift_id', $shift->id);
                    })
                    ->get();

                    if ($assignment['assignment_type'] == 'random') {
                        $students = $students->shuffle();
                    } else {
                        $students = $students->sortBy('exam_code');
                    }

                    // Lấy danh sách phòng thi và sức chứa
                    $rooms = $shift->rooms->map(function($room) {
                        return [
                            'room_id' => $room->id,
                            'capacity' => $room->room->capacity,
                            'current_count' => 0
                        ];
                    })->toArray();

                    // Xóa phân công cũ
                    ExamPeriodRoomStudent::where([
                        'exam_period_id' => $examPeriod->id,
                        'exam_shift_id' => $shift->id
                    ])->delete();

                    // Phân công thí sinh vào phòng
                    $assignedStudents = []; // Theo dõi thí sinh đã được phân công
                    $this->assignStudentsToRooms($examPeriod, $shift, $students);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Phân công phòng thi và thí sinh thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    private function assignStudentsToRooms($examPeriod, $shift, $students)
    {
        // Lấy thông tin phân công phòng thi - môn thi
        $roomSubjects = DB::table('exam_shift_rooms')
            ->where('exam_shift_id', $shift->id)
            ->get()
            ->pluck('exam_period_subject_id', 'exam_period_room_id')
            ->toArray();

        // Nhóm thí sinh theo môn thi
        $studentsBySubject = $students->groupBy('exam_period_subject_id');
        $assignedStudents = []; // Theo dõi thí sinh đã được phân công

        // Phân công thí sinh vào phòng theo môn thi
        foreach ($roomSubjects as $roomId => $subjectId) {
            if (!isset($studentsBySubject[$subjectId])) {
                continue;
            }

            $room = ExamPeriodRoom::with('room')->find($roomId);
            $studentsForSubject = $studentsBySubject[$subjectId];
            $seatNumber = 1;

            foreach ($studentsForSubject as $student) {
                // Kiểm tra xem thí sinh đã được phân công trong ca thi này chưa
                if (isset($assignedStudents[$student->id])) {
                    continue;
                }

                // Kiểm tra sức chứa phòng thi
                if ($seatNumber > $room->room->capacity) {
                    break;
                }

                ExamPeriodRoomStudent::create([
                    'exam_period_id' => $examPeriod->id,
                    'exam_shift_id' => $shift->id,
                    'exam_period_room_id' => $roomId,
                    'exam_period_subject_id' => $student->exam_period_subject_id,
                    'exam_period_subject_student_id' => $student->id,
                    'seat_number' => $seatNumber++
                ]);

                // Đánh dấu thí sinh đã được phân công
                $assignedStudents[$student->id] = true;
            }
        }
    }

    public function assignStudents(Request $request, ExamPeriod $examPeriod)
    {
        $request->validate([
            'assignment_type' => 'required|in:sequential,random',
            'shift_id' => 'required|exists:exam_shifts,id'
        ]);

        try {
            DB::beginTransaction();

            $shift = ExamShift::with(['subjects', 'rooms'])->findOrFail($request->shift_id);
            
            // Lấy danh sách thí sinh của các môn trong ca thi
            $students = ExamPeriodSubjectStudent::whereHas('examPeriodSubject', function($query) use ($shift) {
                $query->whereHas('examShifts', function($q) use ($shift) {
                    $q->where('exam_shifts.id', $shift->id);
                });
            })
            ->select('exam_period_subject_students.*', 'exam_period_subjects.id as exam_period_subject_id')
            ->join('exam_period_subjects', 'exam_period_subject_students.exam_period_subject_id', '=', 'exam_period_subjects.id')
            ->whereNotIn('exam_period_subject_students.id', function($query) use ($shift) {
                $query->select('exam_period_room_students.exam_period_subject_student_id')
                    ->from('exam_period_room_students')
                    ->where('exam_shift_id', $shift->id);
            })
            ->get();

            if ($request->assignment_type == 'random') {
                $students = $students->shuffle();
            } else {
                // Sắp xếp theo số báo danh
                $students = $students->sortBy('exam_code');
            }

            // Lấy danh sách phòng thi và sức chứa
            $rooms = $shift->rooms->map(function($room) {
                return [
                    'room_id' => $room->id,
                    'capacity' => $room->room->capacity,
                    'current_count' => 0
                ];
            })->toArray();

            // Xóa phân công cũ
            ExamPeriodRoomStudent::where([
                'exam_period_id' => $examPeriod->id,
                'exam_shift_id' => $shift->id
            ])->delete();

            // Phân công thí sinh vào phòng
            $assignedStudents = []; // Theo dõi thí sinh đã được phân công
            foreach ($students as $student) {
                // Kiểm tra xem thí sinh đã được phân công trong ca thi này chưa
                if (isset($assignedStudents[$student->id])) {
                    continue;
                }

                // Tìm phòng còn chỗ
                foreach ($rooms as &$room) {
                    if ($room['current_count'] < $room['capacity']) {
                        ExamPeriodRoomStudent::create([
                            'exam_period_id' => $examPeriod->id,
                            'exam_shift_id' => $shift->id,
                            'exam_period_room_id' => $room['room_id'],
                            'exam_period_subject_id' => $student->exam_period_subject_id,
                            'exam_period_subject_student_id' => $student->id,
                            'seat_number' => $room['current_count'] + 1
                        ]);
                        $room['current_count']++;
                        $assignedStudents[$student->id] = true;
                        break;
                    }
                }
            }

            DB::commit();
            return back()->with('success', 'Phân công thí sinh thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function exportRoomAssignments(ExamPeriod $examPeriod, Request $request)
    {
        $spreadsheet = new Spreadsheet();
        
        // Lấy tất cả môn thi của kỳ thi
        $subjects = $examPeriod->examPeriodSubjects()
            ->with(['subject', 'examShifts.rooms' => function($query) {
                $query->join('rooms', 'exam_period_rooms.room_id', '=', 'rooms.id')
                    ->orderBy('rooms.name')
                    ->select('exam_period_rooms.*');
            }])
            ->get();

        foreach ($subjects as $examPeriodSubject) {
            // Tạo worksheet mới cho mỗi môn
            $sheet = $spreadsheet->createSheet();
            // Giới hạn tên sheet tối đa 31 ký tự theo quy định của Excel
            $sheetTitle = mb_substr($examPeriodSubject->subject->name, 0, 31);
            $sheet->setTitle($sheetTitle);
            
            // Thiết lập tiêu đề
            $sheet->mergeCells('A1:I1');
            $sheet->setCellValue('A1', 'DANH SÁCH PHÂN CÔNG PHÒNG THI');
            $sheet->mergeCells('A2:I2');
            $sheet->setCellValue('A2', 'Môn thi: ' . $examPeriodSubject->subject->name . ' (' . $examPeriodSubject->subject->code . ')');
            
            $row = 3;
            
            // Lặp qua từng ca thi của môn này
            foreach ($examPeriodSubject->examShifts as $shift) {
                $row++;
                // Tiêu đề ca thi
                $sheet->mergeCells("A{$row}:I{$row}");
                $sheet->setCellValue("A{$row}", $shift->name . ' - ' . $shift->start_time->format('H:i d/m/Y'));
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                
                // Lấy danh sách phòng thi được phân công cho môn này trong ca này
                $roomAssignments = $shift->rooms()
                    ->whereHas('examPeriodRoomStudents', function($query) use ($examPeriodSubject) {
                        $query->where('exam_period_subject_id', $examPeriodSubject->id);
                    })
                    ->with([
                        'room',
                        'examPeriodRoomStudents' => function($query) use ($examPeriodSubject) {
                            $query->where('exam_period_subject_id', $examPeriodSubject->id)
                                ->with('student');
                        }
                    ])
                    ->join('rooms', 'exam_period_rooms.room_id', '=', 'rooms.id')
                    ->leftJoin('exam_period_proctors', 'exam_shift_rooms.exam_period_proctor_id', '=', 'exam_period_proctors.id')
                    ->leftJoin('accounts', 'exam_period_proctors.account_id', '=', 'accounts.id')
                    ->leftJoin('account_infos', 'accounts.id', '=', 'account_infos.account_id')
                    ->orderBy('rooms.name')
                    ->select('exam_period_rooms.*', 'account_infos.fullName as proctor_name')
                    ->get();
                
                // Debug log
                \Log::info('Room Assignments:', [
                    'shift_id' => $shift->id,
                    'rooms' => $roomAssignments->map(function($room) {
                        return [
                            'room_id' => $room->id,
                            'room_name' => $room->room->name,
                            'pivot' => $room->pivot,
                            'proctor_id' => $room->pivot->exam_period_proctor_id ?? null,
                            'proctor' => $room->proctor ? [
                                'id' => $room->proctor->id,
                                'account' => $room->proctor->account ? [
                                    'id' => $room->proctor->account->id,
                                    'info' => $room->proctor->account->accountInfo ? [
                                        'id' => $room->proctor->account->accountInfo->id,
                                        'full_name' => $room->proctor->account->accountInfo->fullName
                                    ] : null
                                ] : null
                            ] : null
                        ];
                    })->toArray()
                ]);

                foreach ($roomAssignments as $roomAssignment) {
                    if ($roomAssignment->examPeriodRoomStudents->isEmpty()) continue;
                    
                    $row++;
                    
                    // Thông tin phòng
                    $sheet->mergeCells("A{$row}:I{$row}");
                    // Debug log
                    \Log::info('Processing room:', [
                        'room_id' => $roomAssignment->id,
                        'room_name' => $roomAssignment->room->name,
                        'pivot' => $roomAssignment->pivot,
                        'proctor_id' => $roomAssignment->pivot->exam_period_proctor_id ?? null,
                        'proctor' => $roomAssignment->proctor
                    ]);

                    $proctorName = $roomAssignment->proctor_name ?? 'Chưa phân công';
                    $sheet->setCellValue("A{$row}", 'Phòng ' . $roomAssignment->room->name . ' - CBCT: ' . $proctorName);
                    $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                    $row++;
                    
                    // Header của bảng
                    $headers = [
                        'STT', 
                        'SBD', 
                        'Mã SV', 
                        'Họ và tên', 
                        'Ngày sinh',
                        'Giới tính',
                        'Số điện thoại',
                        'Địa chỉ',
                        'Số chỗ ngồi'
                    ];
                    
                    $col = 'A';
                    foreach ($headers as $header) {
                        $sheet->setCellValue($col . $row, $header);
                        $sheet->getStyle($col . $row)->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'D3D3D3']
                            ]
                        ]);
                        $col++;
                    }
                    
                    // Dữ liệu thí sinh
                    foreach ($roomAssignment->examPeriodRoomStudents as $i => $roomStudent) {
                        $student = $roomStudent->student;
                        if (!$student) continue;
                        $row++;
                        
                        $sheet->setCellValue('A' . $row, $i + 1);
                        $sheet->setCellValue('B' . $row, $student->exam_code);
                        $sheet->setCellValue('C' . $row, $student->student_code);
                        $sheet->setCellValue('D' . $row, $student->full_name);
                        $sheet->setCellValue('E' . $row, $student->birthday ? date('d/m/Y', strtotime($student->birthday)) : '');
                        $sheet->setCellValue('F' . $row, $student->gender ? 'Nam' : 'Nữ');
                        $sheet->setCellValue('G' . $row, $student->phone);
                        $sheet->setCellValue('H' . $row, $student->address);
                        $sheet->setCellValue('I' . $row, $roomStudent->seat_number);
                    }
                    
                    // Thêm border cho bảng
                    $sheet->getStyle('A' . ($row - count($roomAssignment->examPeriodRoomStudents) + 1) . ':I' . $row)
                          ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    
                    $row += 1; // Khoảng cách giữa các phòng
                }
                
                $row += 1; // Khoảng cách giữa các ca thi
            }
            
            // Auto-fit columns
            foreach(range('A','I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Style cho tiêu đề
            $sheet->getStyle('A1:A2')->getFont()->setBold(true);
            $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        
        // Xóa sheet mặc định
        $spreadsheet->removeSheetByIndex(0);
        
        // Xuất file
        $filename = 'phan_cong_phong_thi_' . str_replace(' ', '_', $examPeriod->name) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // Hiển thị form tự động phân công
    public function autoAssignmentForm(ExamPeriod $examPeriod)
    {
        $data = [
            'examPeriod' => $examPeriod,
            'shifts' => $examPeriod->examShifts,
            'subjects' => $examPeriod->examPeriodSubjects()->with('subject', 'students')->get(),
            'rooms' => $examPeriod->examPeriodRooms()->with('room')->get(),
            'proctors' => ExamPeriodProctor::where('exam_period_id', $examPeriod->id)
                ->with('account.accountInfo')
                ->get()
        ];

        // Tính toán thống kê
        $stats = [
            'total_shifts' => $data['shifts']->count(),
            'total_subjects' => $data['subjects']->count(),
            'total_rooms' => $data['rooms']->count(),
            'total_room_capacity' => $data['rooms']->sum(function($room) {
                return $room->room->capacity;
            }),
            'total_proctors' => $data['proctors']->count(),
            'total_students' => $data['subjects']->sum(function($subject) {
                return $subject->students->count();
            })
        ];

        return view('exam_periods.assignment.auto', compact('data', 'stats'));
    }

    // Xử lý tự động phân công
    public function autoAssign(Request $request, ExamPeriod $examPeriod)
    {
        try {
            DB::beginTransaction();

            // 1. Thu thập dữ liệu
            $shifts = $examPeriod->examShifts;
            $subjects = $examPeriod->examPeriodSubjects()->with('subject', 'students')->get();
            $rooms = $examPeriod->examPeriodRooms()->with('room')->get();
            $proctors = ExamPeriodProctor::where('exam_period_id', $examPeriod->id)->get();

            // 2. Tính toán số ca thi cần thiết cho mỗi môn
            $subjectShiftNeeds = $this->calculateShiftNeeds($subjects, $rooms);

            // 3. Phân bổ môn thi vào ca thi
            $shiftAssignments = $this->distributeSubjectsToShifts($subjects, $shifts, $subjectShiftNeeds);

            // Xóa phân công môn thi cũ
            DB::table('exam_period_subject_shifts')->whereIn('exam_shift_id', $shifts->pluck('id'))->delete();
            
            // Lưu phân công môn thi mới
            foreach ($shiftAssignments as $shiftId => $shiftSubjects) {
                foreach ($shiftSubjects as $subject) {
                    DB::table('exam_period_subject_shifts')->insert([
                        'exam_shift_id' => $shiftId,
                        'exam_period_subject_id' => $subject->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            // 4. Phân bổ phòng thi và CBCT cho từng ca
            foreach ($shifts as $shift) {
                $shiftSubjects = $shiftAssignments[$shift->id] ?? [];
                if (empty($shiftSubjects)) continue;

                // Phân bổ phòng thi cho các môn trong ca
                $roomAssignments = $this->assignRoomsForShift($shift, $shiftSubjects, $rooms);

                // Phân bổ CBCT cho các phòng
                $this->assignProctorsForShift($shift, $roomAssignments, $proctors);

                // Phân bổ thí sinh vào phòng
                $this->assignStudentsToRoomsAuto($shift, $roomAssignments);
            }

            DB::commit();
            return redirect()->route('exam-periods.assignment.subjects', $examPeriod)
                ->with('success', 'Đã hoàn thành tự động phân công');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    private function calculateShiftNeeds($subjects, $rooms)
    {
        $totalCapacity = $rooms->sum(function($room) {
            return $room->room->capacity;
        });

        $needs = [];
        foreach ($subjects as $subject) {
            $studentCount = $subject->students->count();
            // Đảm bảo mỗi môn được phân vào ít nhất 1 ca thi
            $shiftsNeeded = max(1, ceil($studentCount / $totalCapacity));
            $needs[$subject->id] = $shiftsNeeded;
        }

        return $needs;
    }

    private function distributeSubjectsToShifts($subjects, $shifts, $shiftNeeds)
    {
        $assignments = [];
        $currentShiftIndex = 0;
        $shiftsCount = $shifts->count();

        if ($shiftsCount == 0) {
            throw new \Exception('Không có ca thi nào được tạo');
        }

        // Sắp xếp môn thi theo số lượng thí sinh giảm dần
        $subjects = $subjects->sortByDesc(function($subject) {
            return $subject->students->count();
        });

        foreach ($subjects as $subject) {
            $neededShifts = $shiftNeeds[$subject->id];
            
            for ($i = 0; $i < $neededShifts; $i++) {
                $shiftId = $shifts[$currentShiftIndex]->id;
               
                // Kiểm tra xem môn này đã được phân vào ca thi này chưa
                if (!isset($assignments[$shiftId])) {
                    $assignments[$shiftId] = [];
                }
                if (!in_array($subject, $assignments[$shiftId])) {
                    $assignments[$shiftId][] = $subject;
                }
                
                // Chuyển sang ca thi tiếp theo
                $currentShiftIndex = ($currentShiftIndex + 1) % $shiftsCount;
            }
        }

        return $assignments;
    }

    private function assignRoomsForShift($shift, $subjects, $availableRooms)
    {
        $assignments = [];
        $currentRoomIndex = 0;
        $roomsCount = $availableRooms->count();

        foreach ($subjects as $subject) {
            $studentCount = $subject->students->count();
            $assignedCount = 0;

            while ($assignedCount < $studentCount && $currentRoomIndex < $roomsCount) {
                $room = $availableRooms[$currentRoomIndex];
                $assignments[] = [
                    'room' => $room,
                    'subject' => $subject
                ];
                
                $assignedCount += $room->room->capacity;
                $currentRoomIndex++;
            }
        }

        // Lưu phân công phòng thi
        foreach ($assignments as $assignment) {
            DB::table('exam_shift_rooms')->updateOrInsert(
                [
                    'exam_shift_id' => $shift->id,
                    'exam_period_room_id' => $assignment['room']->id,
                ],
                [
                    'exam_period_subject_id' => $assignment['subject']->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        return $assignments;
    }

    private function assignProctorsForShift($shift, $roomAssignments, $proctors)
    {
        // Lọc CBCT chưa được phân công trong ca này
        $availableProctors = $proctors->filter(function($proctor) use ($shift) {
            return !DB::table('exam_shift_rooms')
                ->where('exam_shift_id', $shift->id)
                ->where('exam_period_proctor_id', $proctor->id)
                ->exists();
        });

        $proctorIndex = 0;
        $proctorCount = $availableProctors->count();

        foreach ($roomAssignments as $assignment) {
            if ($proctorIndex >= $proctorCount) break;

            $proctor = $availableProctors->values()[$proctorIndex];
            
            DB::table('exam_shift_rooms')
                ->where('exam_shift_id', $shift->id)
                ->where('exam_period_room_id', $assignment['room']->id)
                ->update(['exam_period_proctor_id' => $proctor->id]);

            $proctorIndex++;
        }
    }

    private function assignStudentsToRoomsAuto($shift, $roomAssignments)
    {
        foreach ($roomAssignments as $assignment) {
            $students = $assignment['subject']->students()
                ->whereNotIn('id', function($query) use ($shift) {
                    $query->select('exam_period_subject_student_id')
                        ->from('exam_period_room_students')
                        ->where('exam_shift_id', $shift->id);
                })
                ->orderBy('exam_code')
                ->take($assignment['room']->room->capacity)
                ->get();

            $seatNumber = 1;
            foreach ($students as $student) {
                ExamPeriodRoomStudent::create([
                    'exam_period_id' => $shift->examPeriod->id,
                    'exam_shift_id' => $shift->id,
                    'exam_period_room_id' => $assignment['room']->id,
                    'exam_period_subject_id' => $assignment['subject']->id,
                    'exam_period_subject_student_id' => $student->id,
                    'seat_number' => $seatNumber++
                ]);
            }
        }
    }
} 