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

    private $examPeriodId;
    private $subjectId;
    private $row = [
        'Mã sinh viên (*)',
        'Họ và tên (*)',
        'Số điện thoại',
        'Địa chỉ',
        'Ngày sinh (YYYY-MM-DD)',
        'Giới tính (1: Nam, 0: Nữ) (*)',
    ];

    public function __construct($examPeriodId, $subjectId)
    {
        $this->examPeriodId = $examPeriodId;
        $this->subjectId = $subjectId;
    }

    public function model(array $row)
    {
        $examCode = ExamPeriodSubjectStudent::generateExamCode($this->subjectId);

        return new ExamPeriodSubjectStudent([
            'exam_period_id' => $this->examPeriodId,
            'exam_period_subject_id' => $this->subjectId,
            'exam_code' => $examCode,
            'student_code' => trim($row[0]),
            'full_name' => trim($row[1]),
            'phone' => trim($row[2] ?? ''),
            'address' => trim($row[3] ?? ''),
            'birthday' => trim($row[4] ?? null),
            'gender' => (bool)trim($row[5])
        ]);
    }

    public function startRow(): int
    {
        return 6;
    }

    public function rules(): array
    {
        return [
            '0' => 'required',
            '1' => 'required',
            '2' => 'nullable',
            '3' => 'nullable',
            '4' => 'nullable|date',
            '5' => 'required|boolean',
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.required' => 'Mã sinh viên không được để trống',
            '1.required' => 'Họ tên không được để trống',
            '2.max' => 'Số điện thoại không được vượt quá 20 ký tự',
            '3.max' => 'Địa chỉ không được vượt quá 255 ký tự',
            '4.date' => 'Ngày sinh không hợp lệ',
            '5.required' => 'Giới tính không được để trống',
            '5.boolean' => 'Giới tính phải là 0 hoặc 1',
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