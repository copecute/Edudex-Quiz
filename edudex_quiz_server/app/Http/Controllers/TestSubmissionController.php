<?php

namespace App\Http\Controllers;

use App\Models\TestSubmission;
use Illuminate\Http\Request;

class TestSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $query = TestSubmission::with([
            'student', 
            'testPaper',
            'subject',
            'testSession'
        ]);

        // Tìm kiếm theo mã sinh viên
        if ($request->filled('student_code')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('code', 'like', '%' . $request->student_code . '%');
            });
        }

        // Tìm kiếm theo tên sinh viên
        if ($request->filled('student_name')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->student_name . '%');
            });
        }

        // Lọc theo kỳ thi
        if ($request->filled('test_session_id')) {
            $query->where('test_session_id', $request->test_session_id);
        }

        // Lọc theo môn thi
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Lọc theo thời gian nộp
        if ($request->filled('date_from')) {
            $query->whereDate('submitted_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('submitted_at', '<=', $request->date_to);
        }

        $submissions = $query->latest()->paginate(20)->withQueryString();

        // Lấy danh sách kỳ thi và môn học để hiển thị trong form lọc
        $testSessions = \App\Models\TestSession::orderBy('name')->get();
        $subjects = \App\Models\Subject::orderBy('name')->get();

        return view('test_submissions.index', compact('submissions', 'testSessions', 'subjects'));
    }

    public function show(TestSubmission $submission)
    {
        $submission->load([
            'student',
            'testPaper',
            'subject',
            'testSession',
            'testSessionSubject'
        ]);

        if (!$submission->student) {
            return redirect()->route('test-submissions.index')
                ->with('error', 'Không tìm thấy thông tin sinh viên của bài làm này');
        }

        return view('test_submissions.show', compact('submission'));
    }
} 