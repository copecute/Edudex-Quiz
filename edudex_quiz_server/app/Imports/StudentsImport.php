<?php

namespace App\Imports;

use App\Models\ExamPeriodSubjectStudent;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Validation\Rule;

class StudentsImport implements ToModel, WithStartRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    protected $examPeriodSubjectId;
    private $row = [
        'Mã sinh viên (*)',
        'Họ và tên (*)',
        'Số điện thoại',
        'Địa chỉ',
        'Ngày sinh (YYYY-MM-DD)',
        'Giới tính (1: Nam, 0: Nữ) (*)',
    ];

    public function __construct($examPeriodSubjectId)
    {
        $this->examPeriodSubjectId = $examPeriodSubjectId;
    }

    public function model(array $row)
    {
        return new ExamPeriodSubjectStudent([
            'exam_period_subject_id' => $this->examPeriodSubjectId,
            'student_code' => $row[0],
            'full_name' => $row[1],
            'phone' => $row[2] ?? null,
            'address' => $row[3] ?? null,
            'birthday' => $this->transformDate($row[4]),
            'gender' => $row[5],
            'exam_code' => ExamPeriodSubjectStudent::generateExamCode($this->examPeriodSubjectId)
        ]);
    }

    public function startRow(): int
    {
        return 6;
    }

    public function rules(): array
    {
        return [
            '0' => [
                'required',
                'string',
                'max:50',
                // Thêm validation unique cho mã sinh viên
                Rule::unique('exam_period_subject_students', 'student_code')
                    ->where('exam_period_subject_id', $this->examPeriodSubjectId)
            ],
            '1' => 'required|string|max:255',
            '2' => 'nullable|string|max:20',
            '3' => 'nullable|string',
            '4' => 'nullable',
            '5' => 'required|in:0,1',
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.required' => 'Mã sinh viên là bắt buộc',
            '0.max' => 'Mã sinh viên không được vượt quá 50 ký tự',
            '0.unique' => 'Mã sinh viên đã tồn tại trong môn thi này',
            '1.required' => 'Họ và tên là bắt buộc',
            '1.max' => 'Họ và tên không được vượt quá 255 ký tự',
            '2.max' => 'Số điện thoại không được vượt quá 20 ký tự',
            '5.required' => 'Giới tính là bắt buộc',
            '5.in' => 'Giới tính phải là 0 (Nữ) hoặc 1 (Nam)',
        ];
    }

    private function transformDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return date('Y-m-d', strtotime($value));
        } catch (\Exception $e) {
            return null;
        }
    }
} 