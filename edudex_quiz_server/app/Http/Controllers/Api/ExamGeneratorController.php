<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamPeriodSubject;
use App\Models\Question;
use App\Models\ExamShift;
use App\Models\ExamPeriodRoom;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExamGeneratorController extends Controller
{
    /**
     * sinh đề thi cho thí sinh
     */
    public function generateExam(ExamShift $shift, ExamPeriodRoom $room)
    {
        try {
            // lấy thông tin môn thi của phòng thi
            $examShiftRoom = DB::table('exam_shift_rooms')
                ->where('exam_shift_id', $shift->id)
                ->where('exam_period_room_id', $room->id)
                ->first();

            if (!$examShiftRoom) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy thông tin phòng thi'
                ], 404);
            }

            // lấy thông tin môn thi và đề thi mẫu
            $examPeriodSubject = ExamPeriodSubject::with(['subject', 'exam.tags'])
                ->find($examShiftRoom->exam_period_subject_id);

            if (!$examPeriodSubject || !$examPeriodSubject->exam) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy thông tin đề thi'
                ], 404);
            }

            $exam = $examPeriodSubject->exam;
            $subject = $examPeriodSubject->subject;

            // Kiểm tra số lượng câu hỏi có sẵn trước khi sinh đề
            $availableQuestions = [];
            
            if ($exam->tags->isNotEmpty()) {
                // Kiểm tra theo tags
                foreach ($exam->tags as $tag) {
                    $pivot = $tag->pivot;
                    $tagStats = [
                        'tag' => $tag->name,
                        'required' => [],
                        'available' => []
                    ];

                    foreach (['easy', 'medium', 'hard'] as $difficulty) {
                        $requiredCount = round($pivot->num_questions * $pivot->{"${difficulty}_rate"} / 100);
                        
                        // Đếm số câu hỏi có sẵn theo tag và độ khó
                        $availableCount = Question::where('subject_code', $exam->subject_code)
                            ->where('difficulty', $difficulty)
                            ->whereHas('tags', function($q) use ($tag) {
                                $q->where('tags.id', $tag->id);
                            })
                            ->count();

                        $tagStats['required'][$difficulty] = $requiredCount;
                        $tagStats['available'][$difficulty] = $availableCount;
                    }

                    $availableQuestions['tags'][] = $tagStats;
                }

                // Kiểm tra câu hỏi random
                foreach (['easy', 'medium', 'hard'] as $difficulty) {
                    $randomCount = $this->getRandomQuestionCount($difficulty);
                    if ($randomCount > 0) {
                        $availableCount = Question::where('subject_code', $exam->subject_code)
                            ->where('difficulty', $difficulty)
                            ->count();

                        $availableQuestions['random'][$difficulty] = [
                            'required' => $randomCount,
                            'available' => $availableCount
                        ];
                    }
                }
            } else {
                // Kiểm tra theo độ khó
                foreach (['easy', 'medium', 'hard'] as $difficulty) {
                    $requiredCount = round($exam->total_questions * $exam->{"${difficulty}_rate"} / 100);
                    
                    $availableCount = Question::where('subject_code', $exam->subject_code)
                        ->where('difficulty', $difficulty)
                        ->count();

                    $availableQuestions['difficulty'][$difficulty] = [
                        'required' => $requiredCount,
                        'available' => $availableCount
                    ];
                }
            }

            // Kiểm tra và tạo thông báo lỗi nếu thiếu câu hỏi
            $missingQuestions = [];
            $totalRequired = 0;
            $totalAvailable = 0;

            if ($exam->tags->isNotEmpty()) {
                // Tính tổng số câu hỏi theo tags
                $tagTotalAvailable = [];
                foreach ($availableQuestions['tags'] as $tagStats) {
                    foreach (['easy', 'medium', 'hard'] as $difficulty) {
                        $required = $tagStats['required'][$difficulty];
                        $available = $tagStats['available'][$difficulty];
                        $totalRequired += $required;
                        
                        // Lưu số câu hỏi có sẵn theo độ khó
                        if (!isset($tagTotalAvailable[$difficulty])) {
                            $tagTotalAvailable[$difficulty] = 0;
                        }
                        $tagTotalAvailable[$difficulty] = max($tagTotalAvailable[$difficulty], $available);

                        if ($available < $required) {
                            $missingQuestions[] = [
                                'type' => 'tag',
                                'name' => $tagStats['tag'],
                                'difficulty' => $difficulty,
                                'required' => $required,
                                'available' => $available
                            ];
                        }
                    }
                }

                // Tính tổng số câu hỏi random
                foreach ($availableQuestions['random'] as $difficulty => $stats) {
                    $required = $stats['required'];
                    $available = $stats['available'];
                    $totalRequired += $required;

                    // Cộng thêm số câu random có sẵn
                    if (isset($tagTotalAvailable[$difficulty])) {
                        $available = max(0, $available - $tagTotalAvailable[$difficulty]);
                    }
                    $totalAvailable += min($available, $required);

                    if ($available < $required) {
                        $missingQuestions[] = [
                            'type' => 'random',
                            'difficulty' => $difficulty,
                            'required' => $required,
                            'available' => $available
                        ];
                    }
                }

                // Tính tổng số câu hỏi có sẵn từ tags
                foreach ($tagTotalAvailable as $available) {
                    $totalAvailable += $available;
                }
            } else {
                foreach ($availableQuestions['difficulty'] as $difficulty => $stats) {
                    $required = $stats['required'];
                    $available = $stats['available'];
                    $totalRequired += $required;
                    $totalAvailable += min($available, $required);

                    if ($available < $required) {
                        $missingQuestions[] = [
                            'type' => 'difficulty',
                            'difficulty' => $difficulty,
                            'required' => $required,
                            'available' => $available
                        ];
                    }
                }
            }

            if (!empty($missingQuestions)) {
                $message = "Không đủ câu hỏi theo yêu cầu:\n";
                $hasMissingQuestions = false;

                foreach ($missingQuestions as $missing) {
                    // Chỉ hiển thị thông báo khi thực sự thiếu câu hỏi
                    if ($missing['available'] < $missing['required']) {
                        $hasMissingQuestions = true;
                        
                        if ($missing['type'] === 'tag') {
                            $message .= sprintf(
                                "- Tag '%s' (%s): cần %d câu, chỉ có %d câu\n",
                                $missing['name'],
                                $missing['difficulty'],
                                $missing['required'],
                                $missing['available']
                            );
                        } elseif ($missing['type'] === 'random') {
                            $message .= sprintf(
                                "- Random (%s): cần %d câu, chỉ có %d câu\n",
                                $missing['difficulty'],
                                $missing['required'],
                                $missing['available']
                            );
                        } else {
                            $message .= sprintf(
                                "- Độ khó %s: cần %d câu, chỉ có %d câu\n",
                                ucfirst($missing['difficulty']),
                                $missing['required'],
                                $missing['available']
                            );
                        }
                    }
                }

                // Chỉ hiển thị thông báo tổng số câu hỏi khi thực sự thiếu
                if ($totalAvailable < $totalRequired) {
                    $message .= sprintf(
                        "Tổng số câu hỏi: cần %d câu, chỉ có %d câu\n",
                        $totalRequired,
                        $totalAvailable
                    );
                    $hasMissingQuestions = true;
                }

                // Chỉ trả về lỗi khi thực sự thiếu câu hỏi
                if ($hasMissingQuestions) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $message
                    ], 400);
                }
            }

            // Nếu đủ câu hỏi thì mới tiến hành sinh đề
            if ($exam->tags->isNotEmpty()) {
                list($questions, $missingInfo) = $this->generateQuestionsWithTags($exam);
            } else {
                list($questions, $missingInfo) = $this->generateQuestionsWithoutTags($exam);
            }

            // trộn thứ tự câu hỏi và đáp án
            shuffle($questions);
            foreach ($questions as &$question) {
                shuffle($question['answers']);
            }

            // Tính toán số câu hỏi random cho mỗi độ khó
            $randomQuestions = [
                'easy' => 2,
                'medium' => 2,
                'hard' => 0
            ];

            $totalRandomQuestions = array_sum($randomQuestions);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'subject' => [
                        'id' => $subject->id,
                        'code' => $subject->code,
                        'name' => $subject->name
                    ],
                    'exam' => [
                        'id' => $exam->id,
                        'name' => $exam->name,
                        'duration' => $exam->duration,
                        'total_questions' => $exam->total_questions,
                        'description' => $exam->description,
                        'difficulty_rates' => [
                            'easy' => [
                                'percentage' => number_format($exam->easy_rate, 2),
                                'questions' => round($exam->total_questions * $exam->easy_rate / 100),
                                'random_questions' => $randomQuestions['easy']
                            ],
                            'medium' => [
                                'percentage' => number_format($exam->medium_rate, 2),
                                'questions' => round($exam->total_questions * $exam->medium_rate / 100),
                                'random_questions' => $randomQuestions['medium']
                            ],
                            'hard' => [
                                'percentage' => number_format($exam->hard_rate, 2),
                                'questions' => round($exam->total_questions * $exam->hard_rate / 100),
                                'random_questions' => $randomQuestions['hard']
                            ]
                        ],
                        'random_questions_total' => $totalRandomQuestions
                    ],
                    'tags' => $exam->tags->map(function($tag) {
                        $pivot = $tag->pivot;
                        return [
                            'id' => $tag->id,
                            'name' => $tag->name,
                            'difficulty_rates' => [
                                'easy' => [
                                    'percentage' => number_format($pivot->easy_rate, 2),
                                    'questions' => round($pivot->num_questions * $pivot->easy_rate / 100)
                                ],
                                'medium' => [
                                    'percentage' => number_format($pivot->medium_rate, 2),
                                    'questions' => round($pivot->num_questions * $pivot->medium_rate / 100)
                                ],
                                'hard' => [
                                    'percentage' => number_format($pivot->hard_rate, 2),
                                    'questions' => round($pivot->num_questions * $pivot->hard_rate / 100)
                                ]
                            ],
                            'num_questions' => $pivot->num_questions
                        ];
                    }),
                    'questions' => $questions
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi tạo đề thi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * sinh câu hỏi cho đề thi có tags
     */
    private function generateQuestionsWithTags($exam)
    {
        $allQuestions = [];
        $selectedQuestionIds = [];
        $totalQuestionsNeeded = $exam->total_questions;
        $totalQuestionsSelected = 0;
        
        // Mảng lưu trữ câu hỏi theo tag và độ khó
        $tagQuestionsByDifficulty = [];
        
        // Mảng lưu trữ câu hỏi random theo độ khó
        $randomQuestionsByDifficulty = [];
        
        // 1. Lấy tất cả câu hỏi theo tag và độ khó
        foreach ($exam->tags as $tag) {
            $pivot = $tag->pivot;
            $tagQuestionsByDifficulty[$tag->id] = [];
            
            foreach (['easy', 'medium', 'hard'] as $difficulty) {
                $requiredCount = round($pivot->num_questions * $pivot->{"${difficulty}_rate"} / 100);
                
                if ($requiredCount == 0) continue;
                
                // Lấy tất cả câu hỏi có sẵn theo tag và độ khó
                $questions = Question::where('subject_code', $exam->subject_code)
                    ->where('difficulty', $difficulty)
                    ->whereHas('tags', function($q) use ($tag) {
                        $q->where('tags.id', $tag->id);
                    })
                    ->whereNotIn('id', $selectedQuestionIds)
                    ->with('answers', 'tags')
                    ->inRandomOrder()
                    ->get();
                
                // Lưu vào mảng tạm
                $tagQuestionsByDifficulty[$tag->id][$difficulty] = [
                    'required' => $requiredCount,
                    'questions' => $questions
                ];
            }
        }
        
        // 2. Lấy tất cả câu hỏi random theo độ khó
        foreach (['easy', 'medium', 'hard'] as $difficulty) {
            $randomCount = $this->getRandomQuestionCount($difficulty);
            
            if ($randomCount == 0) continue;
            
            // Lấy tất cả câu hỏi có sẵn theo độ khó
            $randomQuestionsByDifficulty[$difficulty] = [
                'required' => $randomCount,
                'questions' => []
            ];
        }
        
        // 3. Lấy câu hỏi từ mỗi tag theo độ khó
        foreach ($tagQuestionsByDifficulty as $tagId => $difficultyQuestions) {
            foreach ($difficultyQuestions as $difficulty => $data) {
                $requiredCount = $data['required'];
                $questions = $data['questions'];
                
                // Lấy đủ số câu hỏi cần thiết
                foreach ($questions->take($requiredCount) as $question) {
                    if ($totalQuestionsSelected >= $totalQuestionsNeeded) {
                        break 3; // Thoát tất cả các vòng lặp nếu đã đủ câu hỏi
                    }
                    
                    $selectedQuestionIds[] = $question->id;
                    $allQuestions[] = $this->formatQuestion($question);
                    $totalQuestionsSelected++;
                }
            }
        }
        
        // 4. Lấy câu hỏi random nếu chưa đủ
        foreach (['easy', 'medium', 'hard'] as $difficulty) {
            if (!isset($randomQuestionsByDifficulty[$difficulty])) continue;
            
            $randomCount = $randomQuestionsByDifficulty[$difficulty]['required'];
            
            if ($randomCount == 0) continue;
            
            // Lấy câu hỏi random theo độ khó
            $randomQuestions = Question::where('subject_code', $exam->subject_code)
                ->where('difficulty', $difficulty)
                ->whereNotIn('id', $selectedQuestionIds)
                ->with('answers', 'tags')
                ->inRandomOrder()
                ->take($randomCount)
                ->get();
            
            foreach ($randomQuestions as $question) {
                if ($totalQuestionsSelected >= $totalQuestionsNeeded) {
                    break 2; // Thoát tất cả các vòng lặp nếu đã đủ câu hỏi
                }
                
                $selectedQuestionIds[] = $question->id;
                $allQuestions[] = $this->formatQuestion($question);
                $totalQuestionsSelected++;
            }
        }
        
        // 5. Kiểm tra nếu không đủ câu hỏi
        if ($totalQuestionsSelected < $totalQuestionsNeeded) {
            // Lấy thêm câu hỏi bất kỳ nếu chưa đủ
            $remainingCount = $totalQuestionsNeeded - $totalQuestionsSelected;
            
            $additionalQuestions = Question::where('subject_code', $exam->subject_code)
                ->whereNotIn('id', $selectedQuestionIds)
                ->with('answers', 'tags')
                ->inRandomOrder()
                ->take($remainingCount)
                ->get();
            
            foreach ($additionalQuestions as $question) {
                $selectedQuestionIds[] = $question->id;
                $allQuestions[] = $this->formatQuestion($question);
                $totalQuestionsSelected++;
            }
        }
        
        return [
            $allQuestions,
            []
        ];
    }

    private function getRandomQuestionCount($difficulty)
    {
        $randomQuestions = [
            'easy' => 2,
            'medium' => 2,
            'hard' => 0
        ];
        return $randomQuestions[$difficulty] ?? 0;
    }

    private function formatQuestion($question)
    {
        return [
            'id' => $question->id,
            'content' => $question->content,
            'type' => $question->difficulty,
            'media' => $question->link_media ?: null,
            'tags' => $question->tags->pluck('name')->toArray(),
            'answers' => $question->answers->map(function($answer) {
                return [
                    'id' => $answer->id,
                    'content' => $answer->content,
                    'media' => $answer->link_media ?: null,
                    'is_correct' => (bool)$answer->is_correct
                ];
            })->toArray()
        ];
    }

    /**
     * sinh câu hỏi cho đề thi không có tags
     */
    private function generateQuestionsWithoutTags($exam)
    {
        $allQuestions = [];
        $selectedQuestionIds = [];
        $totalQuestionsNeeded = $exam->total_questions;
        $totalQuestionsSelected = 0;
        
        // Mảng lưu trữ câu hỏi theo độ khó
        $questionsByDifficulty = [];
        
        // 1. Lấy tất cả câu hỏi theo độ khó
        foreach (['easy', 'medium', 'hard'] as $difficulty) {
            $requiredCount = round($exam->total_questions * $exam->{"${difficulty}_rate"} / 100);
            
            if ($requiredCount == 0) continue;
            
            // Lấy tất cả câu hỏi có sẵn theo độ khó
            $questions = Question::where('subject_code', $exam->subject_code)
                ->where('difficulty', $difficulty)
                ->whereNotIn('id', $selectedQuestionIds)
                ->with('answers', 'tags')
                ->inRandomOrder()
                ->get();
            
            // Lưu vào mảng tạm
            $questionsByDifficulty[$difficulty] = [
                'required' => $requiredCount,
                'questions' => $questions
            ];
        }
        
        // 2. Lấy câu hỏi từ mỗi độ khó
        foreach ($questionsByDifficulty as $difficulty => $data) {
            $requiredCount = $data['required'];
            $questions = $data['questions'];
            
            // Lấy đủ số câu hỏi cần thiết
            foreach ($questions->take($requiredCount) as $question) {
                if ($totalQuestionsSelected >= $totalQuestionsNeeded) {
                    break 2; // Thoát tất cả các vòng lặp nếu đã đủ câu hỏi
                }
                
                $selectedQuestionIds[] = $question->id;
                $allQuestions[] = $this->formatQuestion($question);
                $totalQuestionsSelected++;
            }
        }
        
        // 3. Kiểm tra nếu không đủ câu hỏi
        if ($totalQuestionsSelected < $totalQuestionsNeeded) {
            // Lấy thêm câu hỏi bất kỳ nếu chưa đủ
            $remainingCount = $totalQuestionsNeeded - $totalQuestionsSelected;
            
            $additionalQuestions = Question::where('subject_code', $exam->subject_code)
                ->whereNotIn('id', $selectedQuestionIds)
                ->with('answers', 'tags')
                ->inRandomOrder()
                ->take($remainingCount)
                ->get();
            
            foreach ($additionalQuestions as $question) {
                $selectedQuestionIds[] = $question->id;
                $allQuestions[] = $this->formatQuestion($question);
                $totalQuestionsSelected++;
            }
        }
        
        return [
            $allQuestions,
            []
        ];
    }

    /**
     * lấy câu hỏi theo độ khó
     */
    private function getQuestionsByDifficulty($subjectCode, $tagId = null, $difficulty, $count)
    {
        $query = Question::where('subject_code', $subjectCode)
            ->where('difficulty', $difficulty)
            ->with('answers');

        if ($tagId) {
            $query->whereHas('tags', function($q) use ($tagId) {
                $q->where('tags.id', $tagId);
            });
        }

        $questions = $query->inRandomOrder()
            ->take($count)
            ->get()
            ->map(function($question) {
                return [
                    'id' => $question->id,
                    'content' => $question->content,
                    'link_media' => $question->link_media,
                    'answers' => $question->answers->map(function($answer) {
                        return [
                            'id' => $answer->id,
                            'content' => $answer->content,
                            'link_media' => $answer->link_media,
                            'is_correct' => $answer->is_correct
                        ];
                    })->toArray()
                ];
            })
            ->toArray();

        return count($questions) == $count ? $questions : null;
    }
} 