<?php

namespace App\Http\Controllers;

use App\Models\ExamPeriodSubjectStudent;
use App\Models\ExamPeriodSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentsImport;
use App\Exports\StudentsExport;
use App\Exports\StudentsTemplateExport;
use Illuminate\Support\Facades\DB;
use App\Models\ExamPeriod;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ExamPeriodSubjectStudentController extends Controller
{
    public function index(ExamPeriod $examPeriod, ExamPeriodSubject $examPeriodSubject)
    {
        $students = $examPeriodSubject->students()
            ->search(request('search'))
            ->paginate(10);
        
        return view('exam_period_subject_students.index', compact('examPeriod', 'examPeriodSubject', 'students'));
    }

    public function create(ExamPeriod $examPeriod, ExamPeriodSubject $examPeriodSubject)
    {
        return view('exam_period_subject_students.create', compact('examPeriod', 'examPeriodSubject'));
    }

    public function store(Request $request, ExamPeriod $examPeriod, ExamPeriodSubject $examPeriodSubject)
    {
        $validated = $request->validate([
            'student_code' => [
                'required',
                'string',
                'max:50',
                // Kiểm tra mã sinh viên không trùng trong cùng môn thi
                Rule::unique('exam_period_subject_students', 'student_code')
                    ->where('exam_period_subject_id', $examPeriodSubject->id)
            ],
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'birthday' => 'nullable|date',
            'gender' => 'required|boolean',
            'avatar' => 'nullable|image|max:2048', // max 2MB
        ], [
            'student_code.required' => 'Vui lòng nhập mã sinh viên',
            'student_code.max' => 'Mã sinh viên không được vượt quá 50 ký tự',
            'student_code.unique' => 'Mã sinh viên đã tồn tại trong môn thi này',
            'full_name.required' => 'Vui lòng nhập họ tên',
            'full_name.max' => 'Họ tên không được vượt quá 255 ký tự',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự',
            'birthday.date' => 'Ngày sinh không hợp lệ',
            'gender.required' => 'Vui lòng chọn giới tính',
            'avatar.image' => 'File phải là hình ảnh',
            'avatar.max' => 'Kích thước file không được vượt quá 2MB',
        ]);

        try {
            return DB::transaction(function () use ($request, $examPeriod, $examPeriodSubject, $validated) {
                // Upload avatar nếu có
                if ($request->hasFile('avatar')) {
                    $avatarPath = $request->file('avatar')->store('public/students');
                    $validated['avatar'] = Storage::url($avatarPath);
                }

                // Tạo mã exam_code
                $validated['exam_period_subject_id'] = $examPeriodSubject->id;
                Log::info('ExamPeriodSubjectId:', ['id' => $examPeriodSubject->id]);
                $validated['exam_code'] = ExamPeriodSubjectStudent::generateExamCode($examPeriodSubject->id);

                // Tạo thí sinh mới
                ExamPeriodSubjectStudent::create($validated);

                return redirect()
                    ->route('exam-period-subject-students.index', ['examPeriod' => $examPeriod->id, 'examPeriodSubject' => $examPeriodSubject->id])
                    ->with('success', 'Thêm thí sinh thành công!');
            });
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi thêm thí sinh: ' . $e->getMessage());
        }
    }

    public function edit(ExamPeriod $examPeriod, ExamPeriodSubject $examPeriodSubject, ExamPeriodSubjectStudent $student)
    {
        return view('exam_period_subject_students.edit', compact('examPeriod', 'examPeriodSubject', 'student'));
    }

    public function update(Request $request, ExamPeriod $examPeriod, ExamPeriodSubject $examPeriodSubject, ExamPeriodSubjectStudent $student)
    {
        $validated = $request->validate([
            'student_code' => [
                'required',
                'string',
                'max:50',
                // Kiểm tra mã sinh viên không trùng, ngoại trừ bản ghi hiện tại
                Rule::unique('exam_period_subject_students', 'student_code')
                    ->where('exam_period_subject_id', $examPeriodSubject->id)
                    ->ignore($student->id)
            ],
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'birthday' => 'nullable|date',
            'gender' => 'required|boolean',
            'avatar' => 'nullable|image|max:2048', // max 2MB
        ], [
            'student_code.required' => 'Vui lòng nhập mã sinh viên',
            'student_code.max' => 'Mã sinh viên không được vượt quá 50 ký tự',
            'student_code.unique' => 'Mã sinh viên đã tồn tại trong môn thi này',
            'full_name.required' => 'Vui lòng nhập họ tên',
            'full_name.max' => 'Họ tên không được vượt quá 255 ký tự',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự',
            'birthday.date' => 'Ngày sinh không hợp lệ',
            'gender.required' => 'Vui lòng chọn giới tính',
            'avatar.image' => 'File phải là hình ảnh',
            'avatar.max' => 'Kích thước file không được vượt quá 2MB',
        ]);

        try {
            // Thêm exam_period_subject_id vào dữ liệu cập nhật
            $validated['exam_period_subject_id'] = $examPeriodSubject->id;

            // Xử lý upload avatar mới
            if ($request->hasFile('avatar')) {
                // Xóa avatar cũ
                if ($student->avatar) {
                    Storage::delete(str_replace('/storage', 'public', $student->avatar));
                }
                
                $path = $request->file('avatar')->store('public/students');
                $validated['avatar'] = Storage::url($path);
            }

            $student->update($validated);
            
            return redirect()
                ->route('exam-period-subject-students.index', [
                    'examPeriod' => $examPeriod->id, 
                    'examPeriodSubject' => $examPeriodSubject->id
                ])
                ->with('success', 'Cập nhật thí sinh thành công!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi cập nhật thí sinh: ' . $e->getMessage());
        }
    }

    public function destroy(ExamPeriod $examPeriod, ExamPeriodSubject $examPeriodSubject, ExamPeriodSubjectStudent $student)
    {
        try {
            if ($student->avatar) {
                Storage::disk('public')->delete('avatars/' . $student->avatar);
            }
            $student->delete();
            return redirect()
                ->route('exam-period-subject-students.index', ['examPeriod' => $examPeriod->id, 'examPeriodSubject' => $examPeriodSubject->id])
                ->with('success', 'Xóa thí sinh thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi xóa thí sinh!');
        }
    }

    public function importExportTools(ExamPeriod $examPeriod, ExamPeriodSubject $examPeriodSubject)
    {
        return view('exam_period_subject_students.tools', compact('examPeriod', 'examPeriodSubject'));
    }

    public function import(Request $request, ExamPeriod $examPeriod, ExamPeriodSubject $examPeriodSubject)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new StudentsImport($examPeriodSubject->id), $request->file('file'));
            return back()->with('success', 'Nhập dữ liệu thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function export(ExamPeriod $examPeriod, ExamPeriodSubject $examPeriodSubject)
    {
        return Excel::download(
            new StudentsExport($examPeriodSubject->id), 
            'danh-sach-thi-sinh-' . $examPeriodSubject->subject->name . '.xlsx'
        );
    }

    public function downloadTemplate(ExamPeriod $examPeriod, ExamPeriodSubject $examPeriodSubject)
    {
        return Excel::download(new StudentsTemplateExport, 'mau-nhap-thi-sinh.xlsx');
    }
} 