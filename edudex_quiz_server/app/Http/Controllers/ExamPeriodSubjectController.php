<?php

namespace App\Http\Controllers;

use App\Models\ExamPeriodSubject;
use App\Models\ExamPeriod;
use App\Models\Subject;
use App\Models\Exam;
use Illuminate\Http\Request;

class ExamPeriodSubjectController extends Controller
{
    public function index(ExamPeriod $examPeriod)
    {
        $subjects = $examPeriod->examPeriodSubjects()
            ->with('subject')
            ->search(request('search'))
            ->paginate(10);

        return view('exam_period_subjects.index', compact('examPeriod', 'subjects'));
    }

    public function create(ExamPeriod $examPeriod)
    {
        $subjects = Subject::all();
        return view('exam_period_subjects.create', compact('examPeriod', 'subjects'));
    }

    public function store(Request $request, ExamPeriod $examPeriod)
    {
        $validated = $request->validate([
            'subject_id' => [
                'required',
                'exists:subjects,id',
                function ($attribute, $value, $fail) use ($examPeriod) {
                    // Kiểm tra môn học đã tồn tại trong kỳ thi chưa
                    $exists = ExamPeriodSubject::where('exam_period_id', $examPeriod->id)
                        ->where('subject_id', $value)
                        ->exists();
                    
                    if ($exists) {
                        $fail('Môn học này đã được thêm vào kỳ thi.');
                    }
                },
            ],
            'exam_id' => [
                'required',
                'exists:exams,id',
                function ($attribute, $value, $fail) use ($request) {
                    // Kiểm tra đề thi có thuộc môn học không
                    $exam = Exam::find($value);
                    $subject = Subject::find($request->subject_id);
                    if ($exam->subject_code != $subject->code) {
                        $fail('Đề thi phải thuộc môn học đã chọn.');
                    }
                },
            ],
        ], [
            'subject_id.required' => 'Vui lòng chọn môn học',
            'subject_id.exists' => 'Môn học không tồn tại',
            'exam_id.required' => 'Vui lòng chọn đề thi',
            'exam_id.exists' => 'Đề thi không tồn tại',
        ]);

        $examPeriod->examPeriodSubjects()->create($validated);
        return redirect()->route('exam-period-subjects.index', $examPeriod)
            ->with('success', 'Thêm môn thi thành công!');
    }

    public function edit(ExamPeriod $examPeriod, ExamPeriodSubject $examPeriodSubject)
    {
        $subjects = Subject::all();
        $subject = Subject::find($examPeriodSubject->subject_id);
        $exams = Exam::where('subject_code', $subject->code)->get();
        
        return view('exam_period_subjects.edit', compact('examPeriod', 'examPeriodSubject', 'subjects', 'exams'));
    }

    public function update(Request $request, ExamPeriod $examPeriod, ExamPeriodSubject $examPeriodSubject)
    {
        $validated = $request->validate([
            'subject_id' => [
                'required',
                'exists:subjects,id',
                function ($attribute, $value, $fail) use ($examPeriod, $examPeriodSubject) {
                    // Kiểm tra môn học đã tồn tại trong kỳ thi chưa (trừ chính nó)
                    $exists = ExamPeriodSubject::where('exam_period_id', $examPeriod->id)
                        ->where('subject_id', $value)
                        ->where('id', '!=', $examPeriodSubject->id)
                        ->exists();
                    
                    if ($exists) {
                        $fail('Môn học này đã được thêm vào kỳ thi.');
                    }
                },
            ],
            'exam_id' => [
                'required',
                'exists:exams,id',
                function ($attribute, $value, $fail) use ($request) {
                    $exam = Exam::find($value);
                    $subject = Subject::find($request->subject_id);
                    if ($exam->subject_code != $subject->code) {
                        $fail('Đề thi phải thuộc môn học đã chọn.');
                    }
                },
            ],
        ]);

        $examPeriodSubject->update($validated);
        return redirect()->route('exam-period-subjects.index', $examPeriod)
            ->with('success', 'Cập nhật môn thi thành công!');
    }

    public function destroy(ExamPeriod $examPeriod, ExamPeriodSubject $examPeriodSubject)
    {
        $examPeriodSubject->delete();
        return redirect()->route('exam-period-subjects.index', $examPeriod)
            ->with('success', 'Xóa môn thi thành công!');
    }

    // API để lấy danh sách đề thi theo môn học
    public function getExamsBySubject(Request $request)
    {
        $subjectId = $request->subject_id;
        
        // Lấy subject_code từ Subject
        $subject = Subject::find($subjectId);
        if (!$subject) {
            return response()->json([]);
        }

        $exams = Exam::where('subject_code', $subject->code)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return response()->json($exams);
    }
} 