<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Subject;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $query = Question::with(['subject', 'tags']);

        // Tìm kiếm theo nội dung
        if ($search = $request->input('search')) {
            $query->where('content', 'like', "%{$search}%");
        }

        // Lọc theo môn học
        if ($subjectId = $request->input('subject_id')) {
            $query->where('subject_id', $subjectId);
        }

        // Lọc theo độ khó
        if ($level = $request->input('level')) {
            $query->where('level', $level);
        }

        // Lọc theo tag
        if ($tagId = $request->input('tag_id')) {
            $query->whereHas('tags', function($q) use ($tagId) {
                $q->where('tags.id', $tagId);
            });
        }

        $questions = $query->latest()->paginate(10);
        $subjects = Subject::all();
        $tags = Tag::all();
        
        return view('questions.index', compact('questions', 'subjects', 'tags'));
    }

    public function create()
    {
        $subjects = Subject::all();
        $tags = Tag::all();
        return view('questions.create', compact('subjects', 'tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required',
            'subject_id' => 'required|exists:subjects,id',
            'level' => 'required|in:1,2,3',
            'answers' => 'required|array|min:2|max:6',
            'answers.*' => 'required|string',
            'correct_answer' => 'required|integer|min:0|max:5',
            'tags' => 'nullable|array',
            'tags.*' => 'required|string|max:50'
        ]);

        DB::beginTransaction();
        try {
            // Tạo câu hỏi
            $question = Question::create([
                'content' => $validated['content'],
                'subject_id' => $validated['subject_id'],
                'level' => $validated['level']
            ]);

            // Tạo các đáp án
            foreach ($validated['answers'] as $index => $content) {
                $question->answers()->create([
                    'content' => $content,
                    'is_correct' => $index == $validated['correct_answer']
                ]);
            }

            // Xử lý tags
            if (!empty($validated['tags'])) {
                $tagIds = [];
                foreach ($validated['tags'] as $tagName) {
                    // Tìm hoặc tạo tag mới
                    $tag = Tag::firstOrCreate(
                        [
                            'name' => $tagName,
                            'subject_id' => $validated['subject_id']
                        ]
                    );
                    $tagIds[] = $tag->id;
                }
                $question->tags()->attach($tagIds);
            }

            DB::commit();
            return redirect()
                ->route('questions.index')
                ->with('success', 'Đã thêm câu hỏi mới thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra khi thêm câu hỏi');
        }
    }

    public function edit(Question $question)
    {
        $subjects = Subject::all();
        $tags = Tag::all();
        return view('questions.edit', compact('question', 'subjects', 'tags'));
    }

    public function update(Request $request, Question $question)
    {
        $validated = $request->validate([
            'content' => 'required',
            'subject_id' => 'required|exists:subjects,id',
            'level' => 'required|in:1,2,3',
            'answers' => 'required|array|min:2|max:6',
            'answers.*' => 'required|string',
            'correct_answer' => 'required|integer|min:0|max:5',
            'tags' => 'nullable|array',
            'tags.*' => 'required|string|max:50'
        ]);

        DB::beginTransaction();
        try {
            // Cập nhật câu hỏi
            $question->update([
                'content' => $validated['content'],
                'subject_id' => $validated['subject_id'],
                'level' => $validated['level']
            ]);

            // Xóa đáp án cũ và tạo mới
            $question->answers()->delete();
            foreach ($validated['answers'] as $index => $content) {
                $question->answers()->create([
                    'content' => $content,
                    'is_correct' => $index == $validated['correct_answer']
                ]);
            }

            // Xử lý tags
            if (!empty($validated['tags'])) {
                $tagIds = [];
                foreach ($validated['tags'] as $tagName) {
                    $tag = Tag::firstOrCreate(
                        [
                            'name' => $tagName,
                            'subject_id' => $validated['subject_id']
                        ]
                    );
                    $tagIds[] = $tag->id;
                }
                $question->tags()->sync($tagIds);
            } else {
                $question->tags()->detach();
            }

            DB::commit();
            return redirect()
                ->route('questions.index')
                ->with('success', 'Đã cập nhật câu hỏi thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật câu hỏi');
        }
    }

    public function destroy(Question $question)
    {
        $question->delete();
        return redirect()
            ->route('questions.index')
            ->with('success', 'Đã xóa câu hỏi thành công');
    }

    // Thêm method để lấy tags theo môn học
    public function getTagsBySubject($subjectId)
    {
        $tags = Tag::where('subject_id', $subjectId)->get();
        return response()->json($tags);
    }
} 