<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    public function index(Request $request)
    {
        $query = Faculty::with('majors');

        // Tìm kiếm theo tên hoặc mã
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $faculties = $query->paginate(10);
        return view('faculties.index', compact('faculties'));
    }

    public function create()
    {
        return view('faculties.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:faculties|max:50',
            'name' => 'required|max:255',
            'description' => 'nullable'
        ]);

        Faculty::create($validated);

        return redirect()
            ->route('faculties.index')
            ->with('success', 'Đã thêm khoa mới thành công');
    }

    public function edit(Faculty $faculty)
    {
        return view('faculties.edit', compact('faculty'));
    }

    public function update(Request $request, Faculty $faculty)
    {
        $validated = $request->validate([
            'code' => 'required|max:50|unique:faculties,code,' . $faculty->id,
            'name' => 'required|max:255',
            'description' => 'nullable'
        ]);

        $faculty->update($validated);

        return redirect()
            ->route('faculties.index')
            ->with('success', 'Đã cập nhật khoa thành công');
    }

    public function destroy(Faculty $faculty)
    {
        $faculty->delete();
        return redirect()
            ->route('faculties.index')
            ->with('success', 'Đã xóa khoa thành công');
    }
} 