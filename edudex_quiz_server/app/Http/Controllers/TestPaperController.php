<?php

namespace App\Http\Controllers;

use App\Models\TestPaper;
use App\Models\Subject;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestPaperController extends Controller
{
    public function index(Request $request)
    {
        $query = TestPaper::with(['subject', 'tags']);

        // Tìm kiếm theo tên
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Lọc theo môn học
        if ($subjectId = $request->input('subject_id')) {
            $query->where('subject_id', $subjectId);
        }

        $testPapers = $query->latest()->paginate(10);
        $subjects = Subject::all();

        return view('test_papers.index', compact('testPapers', 'subjects'));
    }

    public function create()
    {
        $subjects = Subject::all();
        return view('test_papers.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'total_questions' => 'required|integer|min:1',
            'subject_id' => 'required|exists:subjects,id',
            'tags' => 'nullable|array',
            'easy_rate' => 'required|numeric|min:0|max:100',
            'medium_rate' => 'required|numeric|min:0|max:100',
            'hard_rate' => 'required|numeric|min:0|max:100',
        ]);

        try {
            DB::beginTransaction();

            $testPaper = TestPaper::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'duration' => $validated['duration'],
                'total_questions' => $validated['total_questions'],
                'subject_id' => $validated['subject_id'],
                'easy_rate' => $validated['easy_rate'],
                'medium_rate' => $validated['medium_rate'],
                'hard_rate' => $validated['hard_rate']
            ]);

            // Nếu có chọn tags thì mới sync
            if (!empty($validated['tags'])) {
                $tagData = [];
                foreach ($validated['tags'] as $tag) {
                    $tagData[$tag['id']] = [
                        'num_questions' => $tag['num_questions'],
                        'easy_rate' => $tag['easy_rate'],
                        'medium_rate' => $tag['medium_rate'],
                        'hard_rate' => $tag['hard_rate']
                    ];
                }
                $testPaper->tags()->sync($tagData);
            }

            DB::commit();
            return redirect()
                ->route('test_papers.index')
                ->with('success', 'Đã tạo đề thi thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function edit(TestPaper $testPaper)
    {
        $subjects = Subject::all();
        $testPaper->load('tags');
        return view('test_papers.edit', compact('testPaper', 'subjects'));
    }

    public function update(Request $request, TestPaper $testPaper)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'total_questions' => 'required|integer|min:1',
            'subject_id' => 'required|exists:subjects,id',
            'tags' => 'required|array',
            'tags.*.id' => 'required|exists:tags,id',
            'tags.*.num_questions' => 'required|integer|min:1',
            'tags.*.easy_rate' => 'required|numeric|min:0|max:100',
            'tags.*.medium_rate' => 'required|numeric|min:0|max:100',
            'tags.*.hard_rate' => 'required|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            $testPaper->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'duration' => $validated['duration'],
                'total_questions' => $validated['total_questions'],
                'subject_id' => $validated['subject_id']
            ]);

            // Cập nhật tags và tỉ lệ
            $tagData = [];
            foreach ($validated['tags'] as $tag) {
                $tagData[$tag['id']] = [
                    'num_questions' => $tag['num_questions'],
                    'easy_rate' => $tag['easy_rate'],
                    'medium_rate' => $tag['medium_rate'],
                    'hard_rate' => $tag['hard_rate']
                ];
            }
            $testPaper->tags()->sync($tagData);

            DB::commit();
            return redirect()
                ->route('test_papers.index')
                ->with('success', 'Đã cập nhật đề thi thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function destroy(TestPaper $testPaper)
    {
        $testPaper->delete();
        return redirect()
            ->route('test_papers.index')
            ->with('success', 'Đã xóa đề thi thành công');
    }

    // API để lấy tags của môn học
    public function getTagsBySubject($subjectId)
    {
        $tags = Tag::where('subject_id', $subjectId)->get();
        return response()->json($tags);
    }
} 