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
        try {
            // Log input parameters
            \Log::info('Exam info request', [
                'shift_id' => $shift->id,
                'room_id' => $room->id
            ]);

            // Kiểm tra CBCT có được phân công cho phòng thi này không
            $proctor = $request->user();
            $proctorId = ExamPeriodProctor::where('exam_period_id', $shift->exam_period_id)
                ->where('account_id', $proctor->id)
                ->value('id');
            
            \Log::info('Proctor check', [
                'proctor_id' => $proctorId,
                'exam_period_id' => $shift->exam_period_id,
                'account_id' => $proctor->id
            ]);

            $shiftRoom = DB::table('exam_shift_rooms')
                ->where('exam_shift_id', $shift->id)
                ->where('exam_period_room_id', $room->id)
                ->where('exam_period_proctor_id', $proctorId)
                ->first();
            
            \Log::info('Shift room check', [
                'shift_room' => $shiftRoom
            ]);

            if (!$shiftRoom) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bạn không được phân công coi thi phòng này'
                ], 403);
            }

            // Debug step 1: Kiểm tra thông tin môn thi và đề thi
            $examBasicInfo = DB::table('exam_shift_rooms as esr')
                ->join('exam_period_subjects as eps', 'esr.exam_period_subject_id', '=', 'eps.id')
                ->join('subjects as s', 'eps.subject_id', '=', 's.id')
                ->join('exams as e', 'eps.exam_id', '=', 'e.id')
                ->where('esr.exam_shift_id', $shift->id)
                ->where('esr.exam_period_room_id', $room->id)
                ->select([
                    'eps.id as subject_id',
                    's.code as subject_code',
                    's.name as subject_name',
                    'e.id as exam_id',
                    'e.name as exam_name',
                    'e.duration',
                    'e.total_questions',
                    'e.description as exam_description'
                ])
                ->first();

            \Log::info('Basic exam info', [
                'exam_info' => $examBasicInfo
            ]);

            if (!$examBasicInfo) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy thông tin môn thi và đề thi'
                ], 404);
            }

            // Debug step 2: Kiểm tra câu hỏi của đề thi
            $examTags = DB::table('exam_tags')
                ->join('tags', 'exam_tags.tag_id', '=', 'tags.id')
                ->where('exam_tags.exam_id', $examBasicInfo->exam_id)
                ->pluck('tags.name')
                ->toArray();

            \Log::info('Exam tags', [
                'exam_id' => $examBasicInfo->exam_id,
                'tags_count' => count($examTags),
                'tags' => $examTags
            ]);

            // Lấy thông tin đề thi để biết tỷ lệ các loại câu hỏi
            $exam = DB::table('exams')
                ->where('id', $examBasicInfo->exam_id)
                ->select(['easy_rate', 'medium_rate', 'hard_rate', 'total_questions'])
                ->first();

            // Tính số lượng câu hỏi cho mỗi độ khó
            $easyCount = round($exam->total_questions * $exam->easy_rate / 100);
            $mediumCount = round($exam->total_questions * $exam->medium_rate / 100);
            $hardCount = $exam->total_questions - $easyCount - $mediumCount;

            // Tạo mảng lưu thông tin thiếu câu hỏi cho từng độ khó
            $missingMessages = [];

            try {
                $questions = collect();
                
                // Lấy câu hỏi dễ
                try {
                    $questions = $questions->concat(
                        $this->getQuestionsByDifficulty($examBasicInfo->subject_code, 'easy', $easyCount, $examTags)
                    );
                } catch (\Exception $e) {
                    $missingMessages[] = $e->getMessage();
                }

                // Lấy câu hỏi trung bình
                try {
                    $questions = $questions->concat(
                        $this->getQuestionsByDifficulty($examBasicInfo->subject_code, 'medium', $mediumCount, $examTags)
                    );
                } catch (\Exception $e) {
                    $missingMessages[] = $e->getMessage();
                }

                // Lấy câu hỏi khó
                try {
                    $questions = $questions->concat(
                        $this->getQuestionsByDifficulty($examBasicInfo->subject_code, 'hard', $hardCount, $examTags)
                    );
                } catch (\Exception $e) {
                    $missingMessages[] = $e->getMessage();
                }

                // Nếu có bất kỳ độ khó nào thiếu câu hỏi
                if (!empty($missingMessages)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Không đủ câu hỏi cho đề thi:\n\n" . implode("\n\n", $missingMessages)
                    ], 404);
                }

            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Có lỗi xảy ra khi lấy câu hỏi: ' . $e->getMessage()
                ], 500);
            }

            // Log thông tin debug
            \Log::info('Questions fetched', [
                'exam_id' => $examBasicInfo->exam_id,
                'total_questions' => $questions->count(),
                'tags' => $examTags,
                'distribution' => [
                    'easy' => $easyCount,
                    'medium' => $mediumCount,
                    'hard' => $hardCount
                ]
            ]);

            if ($questions->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy đủ câu hỏi cho đề thi. Vui lòng kiểm tra cấu hình đề thi.'
                ], 404);
            }

            // Format câu hỏi thành dạng cần trả về
            $formattedQuestions = $questions->groupBy('question_id')
                ->map(function($answers) {
                    $first = $answers->first();
                    return [
                        'id' => $first->question_id,
                        'content' => $first->content,
                        'type' => $first->type,
                        'media' => $first->link_media,
                        'tags' => $first->tags ? explode(',', $first->tags) : [],
                        'answers' => $answers->map(function($answer) {
                            return [
                                'id' => $answer->answer_id,
                                'content' => $answer->answer_content,
                                'media' => $answer->answer_media,
                                'is_correct' => (bool)$answer->is_correct
                            ];
                        })->values()
                    ];
                })
                ->values();

            // Xáo trộn ngẫu nhiên thứ tự các câu hỏi
            $formattedQuestions = $formattedQuestions->shuffle();

            // Xáo trộn ngẫu nhiên thứ tự các đáp án trong mỗi câu hỏi
            $formattedQuestions = $formattedQuestions->map(function($question) {
                $question['answers'] = collect($question['answers'])->shuffle()->values();
                return $question;
            });

            // Lấy thông tin chi tiết về đề thi và tags
            $examInfo = DB::table('exams as e')
                ->where('e.id', $examBasicInfo->exam_id)
                ->select([
                    'e.id',
                    'e.name',
                    'e.duration',
                    'e.total_questions',
                    'e.description',
                    'e.easy_rate',
                    'e.medium_rate', 
                    'e.hard_rate'
                ])
                ->first();

            // Lấy thông tin về tags và tỷ lệ độ khó của từng tag
            $examTags = DB::table('exam_tags as et')
                ->join('tags as t', 'et.tag_id', '=', 't.id')
                ->where('et.exam_id', $examBasicInfo->exam_id)
                ->select([
                    't.id',
                    't.name',
                    'et.num_questions',
                    'et.easy_rate',
                    'et.medium_rate',
                    'et.hard_rate'
                ])
                ->get();

            // Tính số câu hỏi cho mỗi độ khó của đề thi
            $difficultyRates = [
                'easy' => [
                    'percentage' => number_format($examInfo->easy_rate, 2),
                    'questions' => $easyCount,
                    'random_questions' => $easyCount - $examTags->sum(function($tag) {
                        return round($tag->num_questions * $tag->easy_rate / 100);
                    })
                ],
                'medium' => [
                    'percentage' => number_format($examInfo->medium_rate, 2),
                    'questions' => $mediumCount,
                    'random_questions' => $mediumCount - $examTags->sum(function($tag) {
                        return round($tag->num_questions * $tag->medium_rate / 100);
                    })
                ],
                'hard' => [
                    'percentage' => number_format($examInfo->hard_rate, 2),
                    'questions' => $hardCount,
                    'random_questions' => $hardCount - $examTags->sum(function($tag) {
                        return round($tag->num_questions * $tag->hard_rate / 100);
                    })
                ]
            ];

            // Format thông tin tags
            $formattedTags = $examTags->map(function($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'difficulty_rates' => [
                        'easy' => [
                            'percentage' => number_format($tag->easy_rate, 2),
                            'questions' => round($tag->num_questions * $tag->easy_rate / 100)
                        ],
                        'medium' => [
                            'percentage' => number_format($tag->medium_rate, 2),
                            'questions' => round($tag->num_questions * $tag->medium_rate / 100)
                        ],
                        'hard' => [
                            'percentage' => number_format($tag->hard_rate, 2),
                            'questions' => round($tag->num_questions * $tag->hard_rate / 100)
                        ]
                    ],
                    'num_questions' => $tag->num_questions
                ];
            });

            // Format dữ liệu trả về
            $examData = [
                'subject' => [
                    'id' => $examBasicInfo->subject_id,
                    'code' => $examBasicInfo->subject_code,
                    'name' => $examBasicInfo->subject_name
                ],
                'exam' => [
                    'id' => $examInfo->id,
                    'name' => $examInfo->name,
                    'duration' => $examInfo->duration,
                    'total_questions' => $examInfo->total_questions,
                    'description' => $examInfo->description,
                    'difficulty_rates' => $difficultyRates,
                    'random_questions_total' => $difficultyRates['easy']['random_questions'] + 
                        $difficultyRates['medium']['random_questions'] + 
                        $difficultyRates['hard']['random_questions']
                ],
                'tags' => $formattedTags,
                'questions' => $formattedQuestions
            ];

            return response()->json([
                'status' => 'success',
                'data' => $examData
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting exam info', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi lấy thông tin đề thi: ' . $e->getMessage()
            ], 500);
        }
    }
} 