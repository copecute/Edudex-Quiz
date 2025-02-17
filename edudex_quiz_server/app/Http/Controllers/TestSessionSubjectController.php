<?php

namespace App\Http\Controllers;

use App\Models\TestSession;
use App\Models\Subject;
use App\Models\TestRoom;
use App\Models\TestSessionSubject;
use App\Models\Student;
use App\Models\TestPaper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestSessionSubjectController extends Controller
{
    public function index(TestSession $testSession)
    {
        $subjects = $testSession->subjects()
            ->with(['testSessionSubjects' => function($query) use ($testSession) {
                $query->where('test_session_id', $testSession->id)
                      ->withCount(['testShifts' => function($q) use ($testSession) {
                          $q->where('test_shifts.test_session_id', $testSession->id);
                      }])
                      ->with('testPaper');
            }])
            ->with(['testShiftSubjectRooms' => function($query) use ($testSession) {
                $query->whereHas('testShift', function($q) use ($testSession) {
                    $q->where('test_shifts.test_session_id', $testSession->id);
                });
            }, 'testShiftSubjectRooms.testShift', 'testShiftSubjectRooms.testRoom']);

        // Tìm kiếm theo mã hoặc tên môn
        if ($search = request('search')) {
            $subjects->where(function($query) use ($search) {
                $query->where('code', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Lọc theo trạng thái đề thi
        if ($status = request('status')) {
            $subjects->whereHas('testSessionSubjects', function($query) use ($status, $testSession) {
                $query->where('test_session_id', $testSession->id)
                      ->when($status === 'has_test_paper', function($q) {
                          $q->whereNotNull('test_paper_id');
                      })
                      ->when($status === 'no_test_paper', function($q) {
                          $q->whereNull('test_paper_id');
                      });
            });
        }

        // Lọc theo trạng thái phòng thi
        if (request()->has('has_room')) {
            $hasRoom = request('has_room');
            if ($hasRoom == '1') {
                $subjects->has('testShiftSubjectRooms');
            } elseif ($hasRoom == '0') {
                $subjects->doesntHave('testShiftSubjectRooms');
            }
        }

        // Lọc theo ca thi
        if ($shiftId = request('shift')) {
            $subjects->whereHas('testSessionSubjects', function($query) use ($testSession, $shiftId) {
                $query->where('test_session_id', $testSession->id)
                      ->whereHas('testShifts', function($q) use ($shiftId) {
                          $q->where('test_shifts.id', $shiftId);
                      });
            });
        }

        $subjects = $subjects->get()
            ->map(function($subject) {
                // Lấy số ca thi từ test_session_subject
                $subject->test_shifts_count = $subject->testSessionSubjects->first()->test_shifts_count ?? 0;
                
                // Lấy danh sách đề thi cho môn này
                $subject->available_test_papers = TestPaper::where('subject_id', $subject->id)->get();
                
                return $subject;
            });

        // Lấy danh sách môn học chưa được thêm vào kỳ thi
        $availableSubjects = Subject::whereDoesntHave('testSessions', function($q) use ($testSession) {
            $q->where('test_sessions.id', $testSession->id);
        })->get();

        // Lấy danh sách phòng thi khả dụng
        $availableRooms = TestRoom::whereHas('testLocation', function($q) {
            $q->where('is_active', true);
        })
        ->where('is_active', true)
        ->orderBy('test_location_id')
        ->orderBy('name')
        ->get()
        ->groupBy('test_location.name');

        $testPapers = TestPaper::all(); // Lấy tất cả các đề thi

        // Lấy danh sách ca thi của kỳ thi này
        $testSession->load('testShifts');

        return view('test_sessions.subjects.index', compact(
            'testSession', 
            'subjects',
            'availableSubjects',
            'availableRooms',
            'testPapers'
        ));
    }

    public function store(Request $request, TestSession $testSession)
    {
        $validated = $request->validate([
            'subjects' => 'required|array',
            'subjects.*' => 'exists:subjects,id'
        ]);

        $testSession->subjects()->attach($validated['subjects']);

        return redirect()
            ->route('test_sessions.subjects.index', $testSession)
            ->with('success', 'Đã thêm môn thi thành công');
    }

    public function destroy(TestSession $testSession, Subject $subject)
    {
        $testSession->subjects()->detach($subject->id);

        return redirect()
            ->route('test_sessions.subjects.index', $testSession)
            ->with('success', 'Đã xóa môn thi thành công');
    }

    public function assignShifts(Request $request, TestSession $testSession, Subject $subject)
    {
        $validated = $request->validate([
            'test_shift_ids' => 'required|array',
            'test_shift_ids.*' => 'exists:test_shifts,id'
        ]);

        try {
            // Lấy test_session_subject trực tiếp từ model thay vì qua pivot
            $testSessionSubject = TestSessionSubject::where('test_session_id', $testSession->id)
                ->where('subject_id', $subject->id)
                ->firstOrFail();

            // Sync ca thi cho môn học
            $testSessionSubject->testShifts()->sync($validated['test_shift_ids']);

            return back()->with('success', 'Đã phân ca thi thành công');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function students(TestSession $testSession, Subject $subject)
    {
        // Lấy test_session_subject
        $testSessionSubject = TestSessionSubject::where('test_session_id', $testSession->id)
            ->where('subject_id', $subject->id)
            ->firstOrFail();

        // Lấy ID ngành của môn học
        $majorId = $subject->major_id;

        // Lấy danh sách sinh viên đã đăng ký
        $assignedStudents = $testSessionSubject->students()
            ->whereHas('majors', function($q) use ($majorId) {
                $q->where('majors.id', $majorId);
            })
            ->with([
                'majors',
                'testSessionSubjects' => function($q) use ($testSessionSubject) {
                    $q->where('test_session_subjects.id', $testSessionSubject->id);
                },
                'testSessionSubjects.testShiftSubjectRooms.testRoom',
                'testSessionSubjects.testShiftSubjectRooms.testShift'
            ])
            ->orderBy('name')
            ->paginate(10);

        // Lấy danh sách sinh viên chưa đăng ký và có ngành trùng với môn học
        $availableStudents = Student::whereDoesntHave('testSessionSubjects', function($q) use ($testSessionSubject) {
                $q->where('test_session_subject_id', $testSessionSubject->id);
            })
            ->whereHas('majors', function($q) use ($majorId) {
                $q->where('majors.id', $majorId);
            })
            ->where('status', true) // Chỉ lấy sinh viên đang học
            ->orderBy('name')
            ->get();

        return view('test_sessions.subjects.students', compact(
            'testSession',
            'subject',
            'testSessionSubject',
            'assignedStudents',
            'availableStudents'
        ));
    }

    public function assignStudents(Request $request, TestSession $testSession, Subject $subject)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => [
                'exists:students,id',
                function ($attribute, $value, $fail) use ($subject) {
                    $student = Student::find($value);
                    if (!$student->majors->contains('id', $subject->major_id)) {
                        $fail('Thí sinh phải thuộc ngành ' . $subject->major->name);
                    }
                }
            ]
        ]);

        try {
            DB::beginTransaction();

            $testSessionSubject = TestSessionSubject::where('test_session_id', $testSession->id)
                ->where('subject_id', $subject->id)
                ->firstOrFail();

            // Lấy số báo danh lớn nhất hiện tại của kỳ thi
            $maxExamCode = DB::table('test_session_subject_students')
                ->join('test_session_subjects', 'test_session_subjects.id', '=', 'test_session_subject_students.test_session_subject_id')
                ->where('test_session_subjects.test_session_id', $testSession->id)
                ->max('exam_code');

            $nextNumber = $maxExamCode ? (int)substr($maxExamCode, -4) + 1 : 1;

            // Lấy danh sách phòng thi và sức chứa còn lại
            $availableRooms = DB::table('test_shift_subject_rooms')
                ->select('test_shift_subject_rooms.id', 'test_rooms.capacity')
                ->join('test_rooms', 'test_rooms.id', '=', 'test_shift_subject_rooms.test_room_id')
                ->join('test_shifts', 'test_shifts.id', '=', 'test_shift_subject_rooms.test_shift_id')
                ->where('test_shift_subject_rooms.test_session_subject_id', $testSessionSubject->id)
                ->whereRaw('(SELECT COUNT(*) FROM test_session_subject_students 
                            WHERE test_shift_subject_room_id = test_shift_subject_rooms.id) < test_rooms.capacity')
                ->orderBy('test_shift_subject_rooms.id')
                ->get();

            if ($availableRooms->isEmpty()) {
                throw new \Exception('Không còn phòng thi nào có chỗ trống');
            }

            $currentRoomIndex = 0;
            $currentRoom = $availableRooms[$currentRoomIndex];
            $studentsInRoom = 0;

            foreach ($validated['student_ids'] as $studentId) {
                // Kiểm tra và chuyển phòng nếu phòng hiện tại đã đầy
                if ($studentsInRoom >= $currentRoom->capacity) {
                    $currentRoomIndex++;
                    if ($currentRoomIndex >= $availableRooms->count()) {
                        throw new \Exception('Không đủ chỗ trong các phòng thi');
                    }
                    $currentRoom = $availableRooms[$currentRoomIndex];
                    $studentsInRoom = 0;
                }

                // Tạo số báo danh: KT + số 4 chữ số
                $examCode = 'KT' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

                // Thêm thí sinh vào môn thi với số báo danh và phòng thi
                $testSessionSubject->students()->attach($studentId, [
                    'exam_code' => $examCode,
                    'test_shift_subject_room_id' => $currentRoom->id
                ]);

                $nextNumber++;
                $studentsInRoom++;
            }

            DB::commit();
            return back()->with('success', 'Đã thêm thí sinh vào môn thi và phân phòng thành công');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function removeStudent(TestSession $testSession, Subject $subject, Student $student)
    {
        $testSessionSubject = TestSessionSubject::where('test_session_id', $testSession->id)
            ->where('subject_id', $subject->id)
            ->firstOrFail();

        $testSessionSubject->students()->detach($student->id);

        return back()->with('success', 'Đã xóa thí sinh khỏi môn thi thành công');
    }

    public function assignTestPaper(Request $request, TestSession $testSession, TestSessionSubject $testSessionSubject)
    {
        \Log::info('Request data:', $request->all()); // Log dữ liệu gửi lên

        $validated = $request->validate([
            'test_paper_id' => 'required|exists:test_papers,id',
        ]);

        // Cập nhật test_paper_id trong bảng test_session_subjects
        TestSessionSubject::where('test_session_id', $testSession->id)
            ->where('subject_id', $testSessionSubject->subject_id)
            ->update(['test_paper_id' => $validated['test_paper_id']]);

        return back()->with('success', 'Đã phân đề thi thành công');
    }
} 