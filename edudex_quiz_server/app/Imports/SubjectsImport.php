<?php

namespace App\Imports;

use App\Models\Subject;
use App\Models\Major;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SubjectsImport implements ToModel, WithStartRow, WithValidation
{
    public function startRow(): int
    {
        return 6;
    }

    public function model(array $row)
    {
        $major = Major::where('code', trim($row[3]))->firstOrFail();
        
        return new Subject([
            'code' => trim($row[0]),
            'name' => trim($row[1]),
            'credits' => (int)trim($row[2]),
            'major_id' => $major->id,
            'description' => trim($row[4] ?? '')
        ]);
    }

    public function rules(): array
    {
        return [
            '0' => 'required|unique:subjects,code',
            '1' => 'required',
            '2' => 'required|integer|min:1',
            '3' => 'required|exists:majors,code',
            '4' => 'nullable'
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.required' => 'Mã môn học không được để trống',
            '0.unique' => 'Mã môn học đã tồn tại',
            '1.required' => 'Tên môn học không được để trống',
            '2.required' => 'Số tín chỉ không được để trống',
            '2.integer' => 'Số tín chỉ phải là số nguyên',
            '2.min' => 'Số tín chỉ phải lớn hơn 0',
            '3.required' => 'Mã ngành không được để trống',
            '3.exists' => 'Mã ngành không tồn tại trong hệ thống'
        ];
    }
} 