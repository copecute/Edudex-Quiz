<?php

namespace App\Http\Controllers;

use App\Models\ExamPeriod;
use App\Models\ExamResult;
use Illuminate\Http\Request;

class ExamResultController extends Controller
{
    public function index(Request $request, ExamPeriod $examPeriod)
    {
        // Load relationships cho filter
        $examPeriod->load([
            'examPeriodSubjects.subject',
            'examShifts'
        ]);

        $results = ExamResult::where('exam_period_id', $examPeriod->id)
            ->with([
                'examShift',
                'examPeriodSubject.subject',
                'exam',
                'examPeriodRoom.room',
                'proctor.account.accountInfo',
                'student'
            ])
            ->when($request->search, function($query, $search) {
                $query->whereHas('student', function($q) use ($search) {
                    $q->where('exam_code', 'like', "%{$search}%")
                      ->orWhere('student_code', 'like', "%{$search}%")
                      ->orWhere('full_name', 'like', "%{$search}%");
                });
            })
            ->when($request->subject_id, function($query, $subjectId) {
                $query->where('exam_period_subject_id', $subjectId);
            })
            ->when($request->shift_id, function($query, $shiftId) {
                $query->where('exam_shift_id', $shiftId);
            })
            ->paginate(10);

        // Tính toán thống kê
        $stats = [
            'total' => $results->total(),
            'avg_score' => $results->avg('score'),
            'max_score' => $results->max('score'),
            'min_score' => $results->min('score')
        ];

        return view('exam_results.index', compact('examPeriod', 'results', 'stats'));
    }
} 