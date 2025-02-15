<?php

namespace App\Http\Controllers;

use App\Models\Major;
use App\Models\Faculty;
use Illuminate\Http\Request;

class MajorController extends Controller
{
    public function index(Request $request)
    {
        $query = Major::with('faculty');

        // Tìm kiếm theo tên hoặc mã
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Lọc theo khoa
        if ($facultyId = $request->input('faculty_id')) {
            $query->where('faculty_id', $facultyId);
        }

        $majors = $query->paginate(10);
        $faculties = Faculty::all();
        
        return view('majors.index', compact('majors', 'faculties'));
    }

    public function create()
    {
        $faculties = Faculty::all();
        return view('majors.create', compact('faculties'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:majors|max:50',
            'name' => 'required|max:255',
            'description' => 'nullable',
            'faculty_id' => 'required|exists:faculties,id'
        ]);

        Major::create($validated);

        return redirect()
            ->route('majors.index')
            ->with('success', 'Đã thêm ngành mới thành công');
    }

    public function edit(Major $major)
    {
        $faculties = Faculty::all();
        return view('majors.edit', compact('major', 'faculties'));
    }

    public function update(Request $request, Major $major)
    {
        $validated = $request->validate([
            'code' => 'required|max:50|unique:majors,code,' . $major->id,
            'name' => 'required|max:255',
            'description' => 'nullable',
            'faculty_id' => 'required|exists:faculties,id'
        ]);

        $major->update($validated);

        return redirect()
            ->route('majors.index')
            ->with('success', 'Đã cập nhật ngành thành công');
    }

    public function destroy(Major $major)
    {
        $major->delete();
        return redirect()
            ->route('majors.index')
            ->with('success', 'Đã xóa ngành thành công');
    }
} 