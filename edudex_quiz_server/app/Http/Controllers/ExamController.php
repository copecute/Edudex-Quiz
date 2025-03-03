<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Subject;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\ExamsExport;
use App\Exports\ExamsTemplateExport;
use App\Imports\ExamsImport;
use Maatwebsite\Excel\Facades\Excel;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $query = Exam::with(['subject', 'tags']);

        // Tìm kiếm
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('subject', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Lọc theo môn học
        if ($request->subject_code) {
            $query->where('subject_code', $request->subject_code);
        }

        $exams = $query->latest()->paginate(10);
        $subjects = Subject::all();

        return view('exams.index', compact('exams', 'subjects'));
    }

    public function create()
    {
        $subjects = Subject::all();
        return view('exams.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'total_questions' => 'required|integer|min:1',
            'subject_code' => 'required|exists:subjects,code',
            'easy_rate' => 'required|numeric|min:0|max:100',
            'medium_rate' => 'required|numeric|min:0|max:100',
            'hard_rate' => 'required|numeric|min:0|max:100',
            'tags' => 'nullable|array',
            'tags.*.id' => 'required|exists:tags,id',
            'tags.*.num_questions' => 'required|integer|min:1',
            'tags.*.easy_rate' => 'required|numeric|min:0|max:100',
            'tags.*.medium_rate' => 'required|numeric|min:0|max:100',
            'tags.*.hard_rate' => 'required|numeric|min:0|max:100',
        ]);

        // Kiểm tra tổng tỷ lệ = 100%
        if ($request->easy_rate + $request->medium_rate + $request->hard_rate != 100) {
            return back()->withErrors(['rates' => 'Tổng tỷ lệ các độ khó phải bằng 100%']);
        }

        // Kiểm tra tổng số câu hỏi của các tag không vượt quá total_questions
        if ($request->has('tags')) {
            $totalTagQuestions = collect($request->tags)->sum('num_questions');
            if ($totalTagQuestions > $request->total_questions) {
                return back()->withErrors(['tags' => 'Tổng số câu hỏi của các tag không được vượt quá tổng số câu hỏi của đề thi']);
            }
        }

        DB::beginTransaction();
        try {
            // Tạo đề thi
            $exam = Exam::create($request->only([
                'name', 'description', 'duration', 'total_questions',
                'subject_code', 'easy_rate', 'medium_rate', 'hard_rate'
            ]));

            // Thêm tags nếu có
            if ($request->has('tags')) {
                foreach ($request->tags as $tagData) {
                    $exam->examTags()->create([
                        'tag_id' => $tagData['id'],
                        'num_questions' => $tagData['num_questions'],
                        'easy_rate' => $tagData['easy_rate'],
                        'medium_rate' => $tagData['medium_rate'],
                        'hard_rate' => $tagData['hard_rate'],
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('exams.index')->with('success', 'Tạo đề thi thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function edit(Exam $exam)
    {
        $exam->load(['subject', 'tags']);
        $subjects = Subject::all();
        return view('exams.edit', compact('exam', 'subjects'));
    }

    public function update(Request $request, Exam $exam)
    {
        // Validation tương tự như store
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'total_questions' => 'required|integer|min:1',
            'subject_code' => 'required|exists:subjects,code',
            'easy_rate' => 'required|numeric|min:0|max:100',
            'medium_rate' => 'required|numeric|min:0|max:100',
            'hard_rate' => 'required|numeric|min:0|max:100',
            'tags' => 'nullable|array',
            'tags.*.id' => 'required|exists:tags,id',
            'tags.*.num_questions' => 'required|integer|min:1',
            'tags.*.easy_rate' => 'required|numeric|min:0|max:100',
            'tags.*.medium_rate' => 'required|numeric|min:0|max:100',
            'tags.*.hard_rate' => 'required|numeric|min:0|max:100',
        ]);

        // Kiểm tra tương tự như store
        if ($request->easy_rate + $request->medium_rate + $request->hard_rate != 100) {
            return back()->withErrors(['rates' => 'Tổng tỷ lệ các độ khó phải bằng 100%']);
        }

        if ($request->has('tags')) {
            $totalTagQuestions = collect($request->tags)->sum('num_questions');
            if ($totalTagQuestions > $request->total_questions) {
                return back()->withErrors(['tags' => 'Tổng số câu hỏi của các tag không được vượt quá tổng số câu hỏi của đề thi']);
            }
        }

        DB::beginTransaction();
        try {
            // Cập nhật đề thi
            $exam->update($request->only([
                'name', 'description', 'duration', 'total_questions',
                'subject_code', 'easy_rate', 'medium_rate', 'hard_rate'
            ]));

            // Xóa tất cả exam tags cũ
            $exam->examTags()->delete();

            // Thêm lại tags mới nếu có
            if ($request->has('tags')) {
                foreach ($request->tags as $tagData) {
                    $exam->examTags()->create([
                        'tag_id' => $tagData['id'],
                        'num_questions' => $tagData['num_questions'],
                        'easy_rate' => $tagData['easy_rate'],
                        'medium_rate' => $tagData['medium_rate'],
                        'hard_rate' => $tagData['hard_rate'],
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('exams.index')->with('success', 'Cập nhật đề thi thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function destroy(Exam $exam)
    {
        try {
            $exam->delete();
            return redirect()->route('exams.index')->with('success', 'Xóa đề thi thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi xóa đề thi');
        }
    }

    public function export()
    {
        return Excel::download(new ExamsExport, 'danh_sach_de_thi.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new ExamsImport, $request->file('file'));
            return back()->with('success', 'Import dữ liệu thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi import: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new ExamsTemplateExport, 'template_de_thi.xlsx');
    }

    public function importExportTools()
    {
        return view('exams.tools');
    }

    public function getBySubject(Subject $subject)
    {
        $exams = Exam::where('subject_code', $subject->code)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
        
        return response()->json($exams);
    }
} 