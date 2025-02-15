<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Major;
use App\Models\Faculty;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Subject::with(['major.faculty']);

        // Tìm kiếm theo tên hoặc mã
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Lọc theo ngành
        if ($majorId = $request->input('major_id')) {
            $query->where('major_id', $majorId);
        }
        // Lọc theo khoa
        elseif ($facultyId = $request->input('faculty_id')) {
            $query->whereHas('major', function($q) use ($facultyId) {
                $q->where('faculty_id', $facultyId);
            });
        }

        $subjects = $query->paginate(10);
        $faculties = Faculty::all();
        $majors = Major::all();
        
        return view('subjects.index', compact('subjects', 'faculties', 'majors'));
    }

    public function create()
    {
        $faculties = Faculty::with('majors')->get();
        return view('subjects.create', compact('faculties'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:subjects|max:50',
            'name' => 'required|max:255',
            'description' => 'nullable',
            'credits' => 'required|integer|min:1|max:10',
            'major_id' => 'required|exists:majors,id'
        ]);

        Subject::create($validated);

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Đã thêm môn học mới thành công');
    }

    public function edit(Subject $subject)
    {
        $faculties = Faculty::with('majors')->get();
        return view('subjects.edit', compact('subject', 'faculties'));
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'code' => 'required|max:50|unique:subjects,code,' . $subject->id,
            'name' => 'required|max:255',
            'description' => 'nullable',
            'credits' => 'required|integer|min:1|max:10',
            'major_id' => 'required|exists:majors,id'
        ]);

        $subject->update($validated);

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Đã cập nhật môn học thành công');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return redirect()
            ->route('subjects.index')
            ->with('success', 'Đã xóa môn học thành công');
    }
} 