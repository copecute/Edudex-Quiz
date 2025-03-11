<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamPeriod;
use App\Models\ExamPeriodProctor;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\ExamShift;
use App\Models\ExamPeriodRoom;
use App\Models\ExamPeriodRoomStudent;
use App\Http\Controllers\Api\ExamGeneratorController;

class ExamScheduleController extends Controller
{
    public function students(Request $request, ExamShift $shift, ExamPeriodRoom $room)
    {
        try {
            // Kiểm tra CBCT có được phân công cho phòng thi này không
            $proctor = $request->user();
            $proctorId = ExamPeriodProctor::where('exam_period_id', $shift->exam_period_id)
                ->where('account_id', $proctor->id)
                ->value('id');

            $isAssigned = DB::table('exam_shift_rooms')
                ->where('exam_shift_id', $shift->id)
                ->where('exam_period_room_id', $room->id)
                ->where('exam_period_proctor_id', $proctorId)
                ->exists();

            if (!$isAssigned) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bạn không được phân công coi thi phòng này'
                ], 403);
            }

            // Lấy danh sách thí sinh
            $students = DB::table('exam_period_room_students as eprs')
                ->join('exam_period_subject_students as epss', 'eprs.exam_period_subject_student_id', '=', 'epss.id')
                ->where('eprs.exam_shift_id', $shift->id)
                ->where('eprs.exam_period_room_id', $room->id)
                ->select([
                    'epss.exam_code',
                    'epss.student_code',
                    'epss.full_name',
                    'epss.birthday as date_of_birth',
                    'epss.gender',
                    'epss.phone',
                    'epss.address',
                    'eprs.seat_number'
                ])
                ->orderBy('eprs.seat_number')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $students->map(function($student) {
                    return [
                        'exam_code' => $student->exam_code,
                        'student_code' => $student->student_code,
                        'full_name' => $student->full_name,
                        'date_of_birth' => $student->date_of_birth ? Carbon::parse($student->date_of_birth)->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s') : null,
                        'gender' => $student->gender ? 'Nam' : 'Nữ',
                        'phone' => $student->phone,
                        'address' => $student->address,
                        'seat_number' => $student->seat_number
                    ];
                })
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting room students', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi lấy danh sách thí sinh: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getQuestionsByDifficulty($subjectCode, $difficulty, $limit, $tags = [])
    {
        // Lưu trữ thông tin thiếu câu hỏi
        $missingInfo = [
            'difficulty' => $difficulty,
            'required' => $limit,
            'tagged' => 0,
            'random' => 0,
            'missing' => 0
        ];

        // Base query để lấy câu hỏi ngẫu nhiên theo độ khó
        $baseQuery = DB::table('questions')
            ->where('subject_code', $subjectCode)
            ->where('difficulty', $difficulty);

        // Lấy tất cả ID câu hỏi đã chọn để tránh trùng lặp
        $selectedIds = collect();

        // Nếu có tags, ưu tiên lấy câu hỏi có tag trước
        if (!empty($tags)) {
            $taggedIds = DB::table('questions')
                ->where('subject_code', $subjectCode)
                ->where('difficulty', $difficulty)
                ->whereIn('id', function($query) use ($tags) {
                    $query->select('question_id')
                        ->from('question_tag')
                        ->join('tags', 'question_tag.tag_id', '=', 'tags.id')
                        ->whereIn('tags.name', $tags);
                })
                ->orderBy(DB::raw('RAND()'))
                ->limit($limit)
                ->pluck('id');

            $missingInfo['tagged'] = $taggedIds->count();
            $selectedIds = $selectedIds->concat($taggedIds);
        }

        // Nếu chưa đủ số lượng, lấy thêm câu hỏi random
        if ($selectedIds->count() < $limit) {
            $remainingLimit = $limit - $selectedIds->count();
            $randomIds = DB::table('questions')
                ->where('subject_code', $subjectCode)
                ->where('difficulty', $difficulty)
                ->whereNotIn('id', $selectedIds)
                ->orderBy(DB::raw('RAND()'))
                ->limit($remainingLimit)
                ->pluck('id');

            $missingInfo['random'] = $randomIds->count();
            $selectedIds = $selectedIds->concat($randomIds);
        }

        // Tính số câu hỏi còn thiếu
        $missingInfo['missing'] = $limit - $selectedIds->count();

        // Log thông tin chi tiết về việc lấy câu hỏi
        \Log::info("Questions distribution for {$difficulty}", $missingInfo);

        // Nếu thiếu câu hỏi, throw exception với thông tin chi tiết
        if ($selectedIds->count() < $limit) {
            $message = "Không đủ câu hỏi {$difficulty} (cần {$limit} câu):\n";
            
            // Tổng số câu hỏi có tag đã lấy được
            $totalTaggedSelected = 0;
            
            if (!empty($tags)) {
                foreach ($tags as $tag) {
                    // Đếm tổng số câu hỏi có tag và độ khó này
                    $tagTotalCount = DB::table('questions')
                        ->where('subject_code', $subjectCode)
                        ->where('difficulty', $difficulty)
                        ->whereIn('id', function($query) use ($tag) {
                            $query->select('question_id')
                                ->from('question_tag')
                                ->join('tags', 'question_tag.tag_id', '=', 'tags.id')
                                ->where('tags.name', $tag);
                        })
                        ->count();

                    // Đếm số câu hỏi đã lấy được cho tag này
                    $tagSelectedCount = $selectedIds->intersect(
                        DB::table('questions')
                            ->where('subject_code', $subjectCode)
                            ->where('difficulty', $difficulty)
                            ->whereIn('id', function($query) use ($tag) {
                                $query->select('question_id')
                                    ->from('question_tag')
                                    ->join('tags', 'question_tag.tag_id', '=', 'tags.id')
                                    ->where('tags.name', $tag);
                            })
                            ->pluck('id')
                    )->count();
                    
                    $totalTaggedSelected += $tagSelectedCount;
                    $message .= "- Tag {$tag} ({$difficulty}): {$tagSelectedCount}/{$tagTotalCount} câu\n";
                }
            }

            // Tính số câu hỏi random cần lấy
            $randomNeeded = $limit - $totalTaggedSelected;

            // Đếm số câu hỏi random đã lấy được
            $randomSelectedCount = $selectedIds->count() - $totalTaggedSelected;

            $message .= "- Câu hỏi random: {$randomSelectedCount}/{$randomNeeded} câu\n";

            // Đếm tổng số câu hỏi có độ khó này
            $totalCount = DB::table('questions')
                ->where('subject_code', $subjectCode)
                ->where('difficulty', $difficulty)
                ->count();

            $message .= "- Tổng số câu {$difficulty} hiện có: {$totalCount} câu\n";
            $message .= "- Đã lấy được: {$selectedIds->count()}/{$limit} câu\n";
            $message .= "- Còn thiếu: {$missingInfo['missing']} câu";
            
            throw new \Exception($message);
        }

        // Lấy chi tiết câu hỏi, đáp án và tags
        return DB::table('questions as q')
            ->join('answers as a', 'q.id', '=', 'a.question_id')
            ->leftJoin('question_tag as qt', 'q.id', '=', 'qt.question_id')
            ->leftJoin('tags as t', 'qt.tag_id', '=', 't.id')
            ->whereIn('q.id', $selectedIds)
            ->select([
                'q.id as question_id',
                'q.content',
                'q.link_media',
                'q.difficulty as type',
                'a.id as answer_id',
                'a.content as answer_content',
                'a.link_media as answer_media',
                'a.is_correct',
                DB::raw('GROUP_CONCAT(DISTINCT t.name) as tags')
            ])
            ->groupBy('q.id', 'a.id', 'q.content', 'q.link_media', 'q.difficulty', 'a.content', 'a.link_media', 'a.is_correct')
            ->orderBy('q.id')
            ->get();
    }

    public function exam(Request $request, ExamShift $shift, ExamPeriodRoom $room)
    {
        // Chuyển hướng sang ExamGeneratorController
        return app(ExamGeneratorController::class)->generateExam($shift, $room);
    }

    public function getStudentsByRoom(Request $request, ExamShift $shift, ExamPeriodRoom $room)
    {
        try {
            // Kiểm tra CBCT có được phân công cho phòng thi này không
            $proctor = $request->user();
            $proctorId = ExamPeriodProctor::where('exam_period_id', $shift->exam_period_id)
                ->where('account_id', $proctor->id)
                ->value('id');

            $isAssigned = DB::table('exam_shift_rooms')
                ->where('exam_shift_id', $shift->id)
                ->where('exam_period_room_id', $room->id)
                ->where('exam_period_proctor_id', $proctorId)
                ->exists();

            if (!$isAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không được phân công cho phòng thi này'
                ], 403);
            }

            // Lấy danh sách thí sinh
            $students = ExamPeriodRoomStudent::with(['student', 'examPeriodSubject.subject'])
                ->where('exam_shift_id', $shift->id)
                ->where('exam_period_room_id', $room->id)
                ->get()
                ->map(function ($student) {
                    return [
                        'seat_number' => $student->seat_number,
                        'exam_code' => $student->student->exam_code,
                        'student_code' => $student->student->student_code,
                        'full_name' => $student->student->full_name,
                        'date_of_birth' => $student->student->birthday ? 
                            Carbon::parse($student->student->birthday)
                                ->setTimezone('Asia/Ho_Chi_Minh')
                                ->format('Y-m-d H:i:s') : null,
                        'gender' => $student->student->gender ? 'Nam' : 'Nữ',
                        'phone' => $student->student->phone,
                        'address' => $student->student->address,
                        'subject_name' => $student->examPeriodSubject->subject->name
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $students
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách sinh viên: ' . $e->getMessage()
            ], 500);
        }
    }
} 