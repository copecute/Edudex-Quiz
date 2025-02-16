<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Major;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class StudentController extends Controller
{
    private function handleAvatarUpload($request, $student)
    {
        try {
            if (!$request->hasFile('avatar')) {
                return;
            }

            // Xóa avatar cũ nếu có
            if ($student->avatar) {
                Storage::delete('public/upload/avatar/students/' . $student->avatar);
            }

            $image = $request->file('avatar');
            $filename = $student->code . '.jpg';

            // Tạo các thư mục nếu chưa tồn tại
            $path = public_path('upload/avatar/students');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            // Kiểm tra quyền ghi
            if (!is_writable($path)) {
                throw new \Exception("Không có quyền ghi vào thư mục: " . $path);
            }

            // Xử lý và lưu ảnh
            $img = Image::make($image->getRealPath());
            $img->save($path . '/' . $filename, 80, 'jpg');

            // Cập nhật tên file vào database
            $student->update(['avatar' => $filename]);

        } catch (\Exception $e) {
            \Log::error('Upload avatar error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function index(Request $request)
    {
        $query = Student::with('majors');

        // Tìm kiếm theo từ khóa
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Lọc theo trạng thái
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Lọc theo giới tính
        if ($request->has('gender') && $request->gender != '') {
            $query->where('gender', $request->gender);
        }

        // Lọc theo ngành học
        if ($request->has('major_id') && $request->major_id) {
            $query->whereHas('majors', function($q) use ($request) {
                $q->where('majors.id', $request->major_id);
            });
        }

        $students = $query->latest()->paginate(10)->withQueryString();
        $majors = Major::all();

        return view('students.index', compact('students', 'majors'));
    }

    public function create()
    {
        $majors = Major::all();
        return view('students.create', compact('majors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:students,code',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'birthday' => 'nullable|date',
            'gender' => 'required|boolean',
            'status' => 'required|boolean',
            'majors' => 'required|array|min:1',
            'majors.*' => 'exists:majors,id',
            'main_major' => 'required|exists:majors,id',
            'avatar' => 'nullable|image|max:2048' // Max 2MB
        ]);

        $student = Student::create($validated);

        // Upload avatar nếu có
        $this->handleAvatarUpload($request, $student);

        // Gán các ngành học
        foreach ($validated['majors'] as $majorId) {
            $student->majors()->attach($majorId, [
                'is_main' => $majorId == $validated['main_major']
            ]);
        }

        return redirect()->route('students.index')
            ->with('success', 'Thêm thí sinh thành công');
    }

    public function edit(Student $student)
    {
        $majors = Major::all();
        return view('students.edit', compact('student', 'majors'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'code' => ['required', Rule::unique('students')->ignore($student)],
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('students')->ignore($student)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'birthday' => 'nullable|date',
            'gender' => 'required|boolean',
            'status' => 'required|boolean',
            'majors' => 'required|array|min:1',
            'majors.*' => 'exists:majors,id',
            'main_major' => 'required|exists:majors,id',
            'avatar' => 'nullable|image|max:2048' // Max 2MB
        ]);

        $student->update($validated);

        // Upload avatar nếu có
        $this->handleAvatarUpload($request, $student);

        // Cập nhật lại các ngành học
        $student->majors()->detach();
        foreach ($validated['majors'] as $majorId) {
            $student->majors()->attach($majorId, [
                'is_main' => $majorId == $validated['main_major']
            ]);
        }

        return redirect()->route('students.index')
            ->with('success', 'Cập nhật thí sinh thành công');
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return redirect()->route('students.index')
            ->with('success', 'Xóa thí sinh thành công');
    }
} 