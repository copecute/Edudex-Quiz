<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Subject;
use App\Models\Tag;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\QuestionsExport;
use App\Imports\QuestionsImport;
use App\Exports\QuestionsTemplateExport;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $query = Question::with(['subject', 'tags']);

        // Tìm kiếm theo nội dung
        if ($request->search) {
            $query->search($request->search);
        }

        // Lọc theo môn học
        if ($request->subject_code) {
            $query->where('subject_code', $request->subject_code);
        }

        // Lọc theo độ khó
        if ($request->difficulty) {
            $query->byDifficulty($request->difficulty);
        }

        // Lọc theo tag
        if ($request->tag_id) {
            $query->whereHas('tags', function($q) use ($request) {
                $q->where('tags.id', $request->tag_id);
            });
        }

        $questions = $query->latest()->paginate(10);
        $subjects = Subject::all();
        
        // Lấy tags của môn học được chọn
        $tags = [];
        if ($request->subject_code) {
            $tags = Tag::where('subject_code', $request->subject_code)->get();
        }

        return view('questions.index', compact('questions', 'subjects', 'tags'));
    }

    public function create()
    {
        $subjects = Subject::all();
        return view('questions.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required',
            'subject_code' => 'required|exists:subjects,code',
            'difficulty' => 'required|in:easy,medium,hard',
            'link_media' => 'nullable|url',
            'tags' => 'required|array',
            'tags.*' => 'required|string',
            'answers' => 'required|array|min:2',
            'answers.*.content' => 'required',
            'answers.*.link_media' => 'nullable|url',
            'correct_answer' => 'required|integer|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Tạo câu hỏi
            $question = Question::create([
                'content' => $request->content,
                'subject_code' => $request->subject_code,
                'difficulty' => $request->difficulty,
                'link_media' => $request->link_media,
            ]);

            // Xử lý tags
            foreach ($request->tags as $tagName) {
                $tag = Tag::firstOrCreate([
                    'name' => $tagName,
                    'subject_code' => $request->subject_code
                ]);
                $question->tags()->attach($tag->id);
            }

            // Tạo các đáp án
            foreach ($request->answers as $key => $answerData) {
                Answer::create([
                    'question_id' => $question->id,
                    'content' => $answerData['content'],
                    'link_media' => $answerData['link_media'] ?? null,
                    'is_correct' => ($key == $request->correct_answer)
                ]);
            }

            DB::commit();
            return redirect()->route('questions.index')->with('success', 'Thêm câu hỏi thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra khi thêm câu hỏi!')->withInput();
        }
    }

    public function edit(Question $question)
    {
        $subjects = Subject::all();
        $tags = Tag::where('subject_code', $question->subject_code)->get();
        return view('questions.edit', compact('question', 'subjects', 'tags'));
    }

    public function update(Request $request, Question $question)
    {
        $request->validate([
            'content' => 'required',
            'subject_code' => 'required|exists:subjects,code',
            'difficulty' => 'required|in:easy,medium,hard',
            'link_media' => 'nullable|url',
            'tags' => 'required|array',
            'tags.*' => 'required|string',
            'answers' => 'required|array|min:2',
            'answers.*.content' => 'required',
            'answers.*.link_media' => 'nullable|url',
            'correct_answer' => 'required|integer|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Cập nhật câu hỏi
            $question->update([
                'content' => $request->content,
                'subject_code' => $request->subject_code,
                'difficulty' => $request->difficulty,
                'link_media' => $request->link_media,
            ]);

            // Cập nhật tags
            $question->tags()->detach(); // Xóa các tags cũ
            foreach ($request->tags as $tagName) {
                $tag = Tag::firstOrCreate([
                    'name' => $tagName,
                    'subject_code' => $request->subject_code
                ]);
                $question->tags()->attach($tag->id);
            }

            // Cập nhật đáp án
            $question->answers()->delete(); // Xóa các đáp án cũ
            foreach ($request->answers as $key => $answerData) {
                Answer::create([
                    'question_id' => $question->id,
                    'content' => $answerData['content'],
                    'link_media' => $answerData['link_media'] ?? null,
                    'is_correct' => ($key == $request->correct_answer)
                ]);
            }

            DB::commit();
            return redirect()->route('questions.index')->with('success', 'Cập nhật câu hỏi thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật câu hỏi!')->withInput();
        }
    }

    public function destroy(Question $question)
    {
        try {
            DB::beginTransaction();
            
            // Xóa các đáp án
            $question->answers()->delete();
            
            // Xóa các liên kết với tags
            $question->tags()->detach();
            
            // Xóa câu hỏi
            $question->delete();
            
            DB::commit();
            return redirect()->route('questions.index')->with('success', 'Xóa câu hỏi thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra khi xóa câu hỏi!');
        }
    }

    public function getTagsBySubject(Request $request)
    {
        $tags = Tag::where('subject_code', $request->subject_code)
            ->get()
            ->map(function($tag) {
                return ['id' => $tag->id, 'text' => $tag->name];
            });
        return response()->json($tags);
    }

    public function export()
    {
        return Excel::download(new QuestionsExport, 'cau_hoi.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new QuestionsImport, $request->file('file'));
            return back()->with('success', 'Import dữ liệu thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi import: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new QuestionsTemplateExport, 'template_cau_hoi.xlsx');
    }

    public function importExportTools()
    {
        return view('questions.tools');
    }
} 