<?php

namespace App\Http\Controllers;

use App\Models\ExamPeriod;
use App\Models\ExamPeriodSubject;
use App\Models\ExamPeriodSubjectStudent;
use App\Exports\StudentsTemplateExport;
use App\Exports\StudentsExport;
use App\Imports\StudentsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamPeriodStudentController extends Controller
{
    public function index(
        Request $request, 
        ExamPeriod $examPeriod
    ) {
        $subjects = $examPeriod->examPeriodSubjects()
            ->with('subject')
            ->get();

        $students = ExamPeriodSubjectStudent::where('exam_period_id', $examPeriod->id)
            ->with('examPeriodSubject.subject')
            ->when($request->search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('exam_code', 'like', "%{$search}%")
                      ->orWhere('student_code', 'like', "%{$search}%")
                      ->orWhere('full_name', 'like', "%{$search}%");
                });
            })
            ->when($request->subject_id, function($query, $subjectId) {
                $query->where('exam_period_subject_id', $subjectId);
            })
            ->when($request->has('gender'), function($query) use ($request) {
                $query->where('gender', $request->gender);
            })
            ->when($request->sort_by, function($query) use ($request) {
                $query->orderBy($request->sort_by, $request->sort_dir ?? 'asc');
            }, function($query) {
                $query->orderBy('created_at', 'desc');
            })
            ->paginate(10)
            ->withQueryString();

        return view('exam_period_subject_students.index', compact('examPeriod', 'subjects', 'students'));
    }

    public function store(
        Request $request, 
        ExamPeriod $examPeriod
    ) {
        $validated = $request->validate([
            'student_code' => 'required|string',
            'full_name' => 'required|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'birthday' => 'nullable|date',
            'gender' => 'required|boolean',
            'subject_ids' => 'required|array',
            'subject_ids.*' => 'exists:exam_period_subjects,id'
        ]);

        try {
            DB::beginTransaction();
            
            foreach ($validated['subject_ids'] as $subjectId) {
                $subject = $examPeriod->examPeriodSubjects()->findOrFail($subjectId);
                
                // Tạo exam_code cho thí sinh
                $examCode = ExamPeriodSubjectStudent::generateExamCode($subjectId);
                
                // Tạo thí sinh mới
                ExamPeriodSubjectStudent::create([
                    'exam_period_id' => $examPeriod->id,
                    'exam_period_subject_id' => $subjectId,
                    'exam_code' => $examCode,
                    'student_code' => $validated['student_code'],
                    'full_name' => $validated['full_name'],
                    'phone' => $validated['phone'],
                    'address' => $validated['address'],
                    'birthday' => $validated['birthday'],
                    'gender' => $validated['gender']
                ]);
            }
            
            DB::commit();
            return redirect()->back()->with('success', 'Thêm thí sinh thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi thêm thí sinh');
        }
    }

    public function create(ExamPeriod $examPeriod)
    {
        $subjects = $examPeriod->examPeriodSubjects()
            ->with('subject')
            ->get();

        return view('exam_period_subject_students.create', compact('examPeriod', 'subjects'));
    }

    public function edit(ExamPeriod $examPeriod, ExamPeriodSubjectStudent $student)
    {
        $subjects = $examPeriod->examPeriodSubjects()
            ->with('subject')
            ->get();

        return view('exam_period_subject_students.edit', compact('examPeriod', 'subjects', 'student'));
    }

    public function update(Request $request, ExamPeriod $examPeriod, ExamPeriodSubjectStudent $student)
    {
        $validated = $request->validate([
            'student_code' => 'required|string',
            'full_name' => 'required|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'birthday' => 'nullable|date',
            'gender' => 'required|boolean',
            'subject_id' => 'required|exists:exam_period_subjects,id'
        ]);

        try {
            DB::beginTransaction();

            // Nếu đổi môn thi, cần tạo exam_code mới
            if ($student->exam_period_subject_id != $validated['subject_id']) {
                $validated['exam_code'] = ExamPeriodSubjectStudent::generateExamCode($validated['subject_id']);
                $validated['exam_period_subject_id'] = $validated['subject_id'];
            }

            $student->update($validated);
            
            DB::commit();
            return redirect()
                ->route('students.index', $examPeriod)
                ->with('success', 'Cập nhật thí sinh thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Có lỗi xảy ra khi cập nhật thí sinh');
        }
    }

    public function importExportTools(ExamPeriod $examPeriod)
    {
        $subjects = $examPeriod->examPeriodSubjects()
            ->with('subject')
            ->get();

        return view('exam_period_subject_students.tools', compact('examPeriod', 'subjects'));
    }

    public function downloadTemplate(ExamPeriod $examPeriod)
    {
        return Excel::download(
            new StudentsTemplateExport(),
            'mau_import_thi_sinh.xlsx'
        );
    }

    public function import(Request $request, ExamPeriod $examPeriod)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
            'subject_id' => 'required|exists:exam_period_subjects,id'
        ]);

        try {
            DB::beginTransaction();
            
            Excel::import(
                new StudentsImport($examPeriod->id, $request->subject_id),
                $request->file('file')
            );
            
            DB::commit();
            return redirect()
                ->route('students.index', $examPeriod)
                ->with('success', 'Import thí sinh thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function export(Request $request, ExamPeriod $examPeriod)
    {
        return Excel::download(
            new StudentsExport($request->subject_id),
            'danh_sach_thi_sinh.xlsx'
        );
    }

    // ... các method khác tương tự, thay đổi tham số và logic xử lý
} 