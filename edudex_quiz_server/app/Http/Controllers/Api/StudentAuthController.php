<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TestSessionSubject;
use Illuminate\Support\Facades\Storage;
use App\Models\TestSubmission;
use Illuminate\Support\Facades\Log;

class StudentAuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'student_code' => 'required',
            'exam_code' => 'required'
        ]);

        // Kiểm tra xem sinh viên có tồn tại không
        $student = Student::where('code', $validated['student_code'])
            ->where('status', true) // Chỉ cho phép sinh viên đang học đăng nhập
            ->first();

        if (!$student) {
            return response()->json([
                'message' => 'Số báo danh không đúng hoặc không phải trong thời gian thi'
            ], 401); // Trả về mã 401
        }

        // Kiểm tra số báo danh trong kỳ thi đang hoạt động
        $hasValidExamCode = $student->testSessionSubjects()
            ->whereHas('testSession', function($q) {
                $q->where('is_active', true);
            })
            ->wherePivot('exam_code', $validated['exam_code'])
            ->exists();

        if (!$hasValidExamCode) {
            return response()->json([
                'message' => 'Số báo danh không đúng hoặc không phải trong thời gian thi'
            ], 401); // Trả về mã 401
        }

        // Tạo token cho sinh viên
        $token = $student->createToken('student-token')->plainTextToken;

        // Chuẩn bị dữ liệu trả về
        $examInfo = $student->testSessionSubjects()
            ->with(['testPaper', 'testSession', 'subject', 'testShiftSubjectRooms.testRoom.testLocation', 'testShiftSubjectRooms.testShift'])
            ->get()
            ->map(function($testSessionSubject) use ($student) {
                // Lấy thông tin từ bảng trung gian
                $pivotData = DB::table('test_session_subject_students')
                    ->where('test_session_subject_id', $testSessionSubject->id)
                    ->where('student_id', $student->id)
                    ->first();

                // Lấy thông tin phòng thi
                $room = $pivotData->test_shift_subject_room_id 
                    ? $testSessionSubject->testShiftSubjectRooms
                        ->where('id', $pivotData->test_shift_subject_room_id)
                        ->first()
                    : null;
                
                return [
                    'test_session' => [
                        'id' => $testSessionSubject->testSession->id,
                        'name' => $testSessionSubject->testSession->name,
                        'start_date' => $testSessionSubject->testSession->start_time,
                        'end_date' => $testSessionSubject->testSession->end_time,
                    ],
                    'subject' => [
                        'id' => $testSessionSubject->subject->id,
                        'code' => $testSessionSubject->subject->code,
                        'name' => $testSessionSubject->subject->name,
                    ],
                    'exam_code' => $pivotData->exam_code,
                    'room' => $room ? [
                        'name' => $room->testRoom->name,
                        'location' => $room->testRoom->testLocation->name,
                        'shift' => [
                            'name' => $room->testShift->name,
                            'start_time' => $room->testShift->start_time,
                            'end_time' => $room->testShift->end_time,
                        ]
                    ] : null,
                    // Thêm thông tin đề thi
                    'test_paper' => $testSessionSubject->test_paper_id ? [
                        'id' => $testSessionSubject->testPaper->id,
                        'name' => $testSessionSubject->testPaper->name,
                        'duration' => $testSessionSubject->testPaper->duration,
                        'total_questions' => $testSessionSubject->testPaper->total_questions,
                    ] : null,
                    // Thêm ID của test_session_subject để client có thể gọi API lấy chi tiết đề thi
                    'test_session_subject_id' => $testSessionSubject->id
                ];
            });

        // Trả về thông tin sinh viên và token
        return response()->json([
            'token' => $token,
            'student' => [
                'id' => $student->id,
                'code' => $student->code,
                'name' => $student->name,
                'email' => $student->email,
                'phone' => $student->phone,
                'address' => $student->address,
                'birthday' => $student->birthday?->format('Y-m-d'),
                'gender' => $student->gender,
                'avatar_url' => $student->avatar_url,
                'majors' => $student->majors->map(function($major) {
                    return [
                        'id' => $major->id,
                        'name' => $major->name,
                        'is_main' => $major->pivot->is_main
                    ];
                })
            ],
            'exams' => $examInfo
        ]);
    }

    public function profile(Request $request)
    {
        $student = $request->user();
        
        return response()->json([
            'student' => [
                'id' => $student->id,
                'code' => $student->code,
                'name' => $student->name,
                'email' => $student->email,
                'phone' => $student->phone,
                'address' => $student->address,
                'birthday' => $student->birthday?->format('Y-m-d'),
                'gender' => $student->gender,
                'avatar_url' => $student->avatar_url,
                'majors' => $student->majors->map(function($major) {
                    return [
                        'id' => $major->id,
                        'name' => $major->name,
                        'is_main' => $major->pivot->is_main
                    ];
                })
            ]
        ]);
    }

    public function logout(Request $request)
    {
        try {
            // Kiểm tra xem có token hiện tại không
            if ($request->user() && $request->user()->currentAccessToken()) {
                // Xóa token hiện tại
                $request->user()->currentAccessToken()->delete();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Đăng xuất thành công'
                ]);
            }

            // Nếu không có token, vẫn trả về thành công
            return response()->json([
                'success' => true,
                'message' => 'Đã đăng xuất'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi đăng xuất: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTestPaper(Request $request, $testSessionSubjectId)
    {
        try {
            // Lấy thông tin môn thi của sinh viên
            $testSessionSubject = TestSessionSubject::with(['testPaper', 'subject'])
                ->whereHas('students', function($query) use ($request) {
                    $query->where('students.id', $request->user()->id);
                })
                ->findOrFail($testSessionSubjectId);

            // Kiểm tra xem đã đến giờ thi chưa
            $currentShiftRoom = $testSessionSubject->testShiftSubjectRooms()
                ->whereHas('testShift', function($query) {
                    $query->where('start_time', '<=', now())
                          ->where('end_time', '>=', now());
                })
                ->first();

            if (!$currentShiftRoom) {
                return response()->json([
                    'message' => 'Chưa đến giờ thi hoặc đã hết giờ thi'
                ], 403);
            }

            // Kiểm tra xem đã được phân đề thi chưa
            if (!$testSessionSubject->test_paper_id) {
                return response()->json([
                    'message' => 'Môn thi chưa được phân đề thi'
                ], 404);
            }

            // Lấy tỉ lệ độ khó tổng thể của đề thi
            $difficultyRates = $testSessionSubject->testPaper->getDifficultyRatesAttribute();

            // Lấy thông tin chi tiết về tags và số câu hỏi
            $tags = $testSessionSubject->testPaper->tags->map(function($tag) {
                $numQuestions = $tag->pivot->num_questions;
                $easyRate = $tag->pivot->easy_rate;
                $mediumRate = $tag->pivot->medium_rate;
                $hardRate = $tag->pivot->hard_rate;

                // Tính số câu hỏi theo độ khó
                $easyQuestions = round($numQuestions * $easyRate / 100);
                $mediumQuestions = round($numQuestions * $mediumRate / 100);
                $hardQuestions = $numQuestions - $easyQuestions - $mediumQuestions;

                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'total_questions' => $numQuestions,
                    'questions_by_level' => [
                        'easy' => $easyQuestions,
                        'medium' => $mediumQuestions,
                        'hard' => $hardQuestions
                    ],
                    'rates' => [
                        'easy' => $easyRate,
                        'medium' => $mediumRate,
                        'hard' => $hardRate
                    ]
                ];
            });

            // Trả về thông tin đề thi
            return response()->json([
                'test_paper' => [
                    'id' => $testSessionSubject->testPaper->id,
                    'name' => $testSessionSubject->testPaper->name,
                    'subject' => [
                        'id' => $testSessionSubject->subject->id,
                        'code' => $testSessionSubject->subject->code,
                        'name' => $testSessionSubject->subject->name,
                    ],
                    'duration' => $testSessionSubject->testPaper->duration,
                    'total_questions' => $testSessionSubject->testPaper->total_questions,
                    // Thêm thông tin về độ khó và tags
                    'difficulty_rates' => [
                        'easy' => round($difficultyRates['easy'], 1),
                        'medium' => round($difficultyRates['medium'], 1),
                        'hard' => round($difficultyRates['hard'], 1)
                    ],
                    'tags' => $tags,
                    'shift' => [
                        'id' => $currentShiftRoom->testShift->id,
                        'name' => $currentShiftRoom->testShift->name,
                        'start_time' => $currentShiftRoom->testShift->start_time,
                        'end_time' => $currentShiftRoom->testShift->end_time,
                    ],
                    'room' => [
                        'id' => $currentShiftRoom->testRoom->id,
                        'name' => $currentShiftRoom->testRoom->name,
                        'location' => [
                            'id' => $currentShiftRoom->testRoom->testLocation->id,
                            'name' => $currentShiftRoom->testRoom->testLocation->name,
                        ]
                    ]
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy thông tin đề thi'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getQuestions(Request $request, $testSessionSubjectId)
    {
        try {
            // Lấy thông tin môn thi của sinh viên
            $testSessionSubject = TestSessionSubject::with([
                'testPaper.questions.answers',
                'testPaper.tags',
                'subject'
            ])->whereHas('students', function($query) use ($request) {
                $query->where('students.id', $request->user()->id);
            })->findOrFail($testSessionSubjectId);

            // Kiểm tra xem đã đến giờ thi chưa
            $currentShiftRoom = $testSessionSubject->testShiftSubjectRooms()
                ->whereHas('testShift', function($query) {
                    $query->where('start_time', '<=', now())
                          ->where('end_time', '>=', now());
                })
                ->first();

            if (!$currentShiftRoom) {
                // Lấy ca thi gần nhất của sinh viên
                $nextShiftRoom = $testSessionSubject->testShiftSubjectRooms()
                    ->whereHas('testShift', function($query) {
                        $query->where('start_time', '>', now())
                              ->orderBy('start_time', 'asc');
                    })
                    ->first();

                return response()->json([
                    'message' => 'Chưa đến giờ thi hoặc đã hết giờ thi',
                    'current_time' => now(),
                    'shift_info' => $nextShiftRoom ? [
                        'name' => $nextShiftRoom->testShift->name,
                        'start_time' => $nextShiftRoom->testShift->start_time,
                        'end_time' => $nextShiftRoom->testShift->end_time,
                        'room' => [
                            'name' => $nextShiftRoom->testRoom->name,
                            'location' => $nextShiftRoom->testRoom->testLocation->name
                        ]
                    ] : null
                ], 403);
            }

            // Kiểm tra xem đã được phân đề thi chưa
            if (!$testSessionSubject->test_paper_id) {
                return response()->json([
                    'message' => 'Môn thi chưa được phân đề thi'
                ], 404);
            }

            // Lấy tỉ lệ độ khó tổng thể của đề thi
            $difficultyRates = $testSessionSubject->testPaper->getDifficultyRatesAttribute();

            // Lấy thông tin chi tiết về tags và số câu hỏi
            $tags = $testSessionSubject->testPaper->tags->map(function($tag) {
                $numQuestions = $tag->pivot->num_questions;
                $easyRate = $tag->pivot->easy_rate;
                $mediumRate = $tag->pivot->medium_rate;
                $hardRate = $tag->pivot->hard_rate;

                // Tính số câu hỏi theo độ khó
                $easyQuestions = round($numQuestions * $easyRate / 100);
                $mediumQuestions = round($numQuestions * $mediumRate / 100);
                $hardQuestions = $numQuestions - $easyQuestions - $mediumQuestions;

                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'total_questions' => $numQuestions,
                    'questions_by_level' => [
                        'easy' => $easyQuestions,
                        'medium' => $mediumQuestions,
                        'hard' => $hardQuestions
                    ],
                    'rates' => [
                        'easy' => $easyRate,
                        'medium' => $mediumRate,
                        'hard' => $hardRate
                    ]
                ];
            });

            // Lấy danh sách câu hỏi và đáp án
            $allQuestions = collect();

            // Lấy câu hỏi theo từng tag
            foreach ($tags as $tag) {
                // Lấy câu hỏi theo độ khó cho mỗi tag
                $tagQuestions = $testSessionSubject->testPaper->questions()
                    ->whereHas('tags', function($query) use ($tag) {
                        $query->where('tags.id', $tag['id']);
                    })
                    ->get();

                // Lấy câu hỏi dễ
                $easyQuestionsAvailable = $tagQuestions->where('level', 1);
                $easyQuestionsNeeded = $tag['questions_by_level']['easy'];
                if ($easyQuestionsAvailable->count() < $easyQuestionsNeeded) {
                    return response()->json([
                        'message' => "Không đủ câu hỏi dễ cho tag {$tag['name']}. Cần {$easyQuestionsNeeded} câu, chỉ có {$easyQuestionsAvailable->count()} câu."
                    ], 500);
                }
                $easyQuestions = $easyQuestionsAvailable->random($easyQuestionsNeeded);

                // Lấy câu hỏi trung bình
                $mediumQuestionsAvailable = $tagQuestions->where('level', 2);
                $mediumQuestionsNeeded = $tag['questions_by_level']['medium'];
                if ($mediumQuestionsAvailable->count() < $mediumQuestionsNeeded) {
                    return response()->json([
                        'message' => "Không đủ câu hỏi trung bình cho tag {$tag['name']}. Cần {$mediumQuestionsNeeded} câu, chỉ có {$mediumQuestionsAvailable->count()} câu."
                    ], 500);
                }
                $mediumQuestions = $mediumQuestionsAvailable->random($mediumQuestionsNeeded);

                // Lấy câu hỏi khó
                $hardQuestionsAvailable = $tagQuestions->where('level', 3);
                $hardQuestionsNeeded = $tag['questions_by_level']['hard'];
                if ($hardQuestionsAvailable->count() < $hardQuestionsNeeded) {
                    return response()->json([
                        'message' => "Không đủ câu hỏi khó cho tag {$tag['name']}. Cần {$hardQuestionsNeeded} câu, chỉ có {$hardQuestionsAvailable->count()} câu."
                    ], 500);
                }
                $hardQuestions = $hardQuestionsAvailable->random($hardQuestionsNeeded);

                // Gộp các câu hỏi lại
                $allQuestions = $allQuestions->concat($easyQuestions)
                    ->concat($mediumQuestions)
                    ->concat($hardQuestions);
            }

            // Format câu hỏi và xáo trộn
            $questions = $allQuestions->map(function($question) {
                return [
                    'id' => $question->id,
                    'content' => $question->content,
                    'type' => $question->type,
                    'level' => $question->level,
                    'score' => $question->score,
                    'tags' => $question->tags->map(function($tag) {
                        return [
                            'id' => $tag->id,
                            'name' => $tag->name
                        ];
                    }),
                    'answers' => $question->answers->map(function($answer) {
                        return [
                            'id' => $answer->id,
                            'content' => $answer->content,
                            'explanation' => $answer->explanation
                        ];
                    })->shuffle(),
                    'explanation' => $question->explanation,
                    'image_url' => $question->image_url,
                ];
            })->shuffle();

            // Kiểm tra số lượng câu hỏi
            if ($questions->count() !== $testSessionSubject->testPaper->total_questions) {
                return response()->json([
                    'message' => 'Không đủ số lượng câu hỏi theo yêu cầu'
                ], 500);
            }

            // Trả về thông tin đề thi và danh sách câu hỏi
            return response()->json([
                'test_paper' => [
                    'id' => $testSessionSubject->testPaper->id,
                    'name' => $testSessionSubject->testPaper->name,
                    'subject' => [
                        'id' => $testSessionSubject->subject->id,
                        'code' => $testSessionSubject->subject->code,
                        'name' => $testSessionSubject->subject->name,
                    ],
                    'duration' => $testSessionSubject->testPaper->duration,
                    'total_questions' => $testSessionSubject->testPaper->total_questions,
                    // Thêm thông tin về độ khó và tags
                    'difficulty_rates' => [
                        'easy' => round($difficultyRates['easy'], 1),
                        'medium' => round($difficultyRates['medium'], 1),
                        'hard' => round($difficultyRates['hard'], 1)
                    ],
                    'tags' => $tags,
                    'questions' => $questions
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Không tìm thấy thông tin đề thi'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function submitTest(Request $request)
    {
        try {
            $validated = $request->validate([
                'answers' => 'required|array',
                'answers.*.question_id' => 'required|exists:questions,id',
                'answers.*.answer_id' => 'required|exists:answers,id',
                'submission_file' => 'required|string',
                'started_at' => 'required|date',
                'test_session_subject_id' => 'required|exists:test_session_subjects,id'
            ]);

            $student = $request->user();
            $testSessionSubject = TestSessionSubject::with([
                'testPaper.questions.answers',
                'testSession',
                'subject'
            ])->findOrFail($validated['test_session_subject_id']);

            // Kiểm tra quyền nộp bài trước
            if (!$testSessionSubject->students()->where('students.id', $student->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền nộp bài cho môn thi này'
                ], 403);
            }

            // Kiểm tra thời gian thi
            $currentShiftRoom = $testSessionSubject->testShiftSubjectRooms()
                ->whereHas('testShift', function($query) {
                    $query->where('start_time', '<=', now())
                          ->where('end_time', '>=', now());
                })
                ->first();

            if (!$currentShiftRoom) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đã hết thời gian làm bài'
                ], 403);
            }

            // Kiểm tra bài nộp tồn tại
            $existingSubmission = TestSubmission::where([
                'test_session_id' => $testSessionSubject->test_session_id,
                'student_id' => $student->id,
                'subject_id' => $testSessionSubject->subject_id,
                'test_session_subject_id' => $validated['test_session_subject_id']
            ])->whereNotNull('submitted_at')->first();

            if ($existingSubmission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã nộp bài cho môn này trong kỳ thi. Không thể nộp lại.'
                ], 400);
            }

            // Tính điểm
            $totalQuestions = $testSessionSubject->testPaper->total_questions; // Lấy số câu hỏi của đề thi
            $correctAnswers = 0;
            $answers = collect($validated['answers']);

            // Chỉ lấy các câu hỏi trong danh sách answers của sinh viên
            $questionIds = $answers->pluck('question_id');
            $questions = $testSessionSubject->testPaper->questions()
                ->whereIn('questions.id', $questionIds)
                ->get();

            foreach ($questions as $question) {
                $studentAnswer = $answers->firstWhere('question_id', $question->id);
                if ($studentAnswer) {
                    $isCorrect = $question->answers()
                        ->where('id', $studentAnswer['answer_id'])
                        ->where('is_correct', true)
                        ->exists();
                    if ($isCorrect) {
                        $correctAnswers++;
                    }
                }
            }

            // Tính điểm theo thang 10
            $score = ($correctAnswers / $totalQuestions) * 10;

            // Lưu file bài làm
            $fileName = null;
            if ($validated['submission_file']) {
                $fileData = base64_decode($validated['submission_file']);
                $fileName = 'submissions/' . $student->id . '_' . time() . '.edudex';
                Storage::disk('public')->put($fileName, $fileData);
            }

            // Lưu bài làm vào database
            $submission = \App\Models\TestSubmission::create([
                'student_id' => $student->id,
                'test_paper_id' => $testSessionSubject->test_paper_id,
                'test_session_subject_id' => $testSessionSubject->id,
                'test_session_id' => $testSessionSubject->test_session_id,
                'subject_id' => $testSessionSubject->subject_id,
                'score' => round($score, 2),
                'submission_file' => $fileName,
                'started_at' => $validated['started_at'],
                'submitted_at' => now(),
                'notes' => "Trả lời đúng: $correctAnswers/$totalQuestions câu"
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nộp bài thành công',
                'data' => [
                    'submission_id' => $submission->id,
                    'score' => $score,
                    'correct_answers' => $correctAnswers,
                    'total_questions' => $totalQuestions
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Submit test error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
} 