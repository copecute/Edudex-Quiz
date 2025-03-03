<?php

namespace App\Imports;

use App\Models\Major;
use App\Models\Faculty;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class MajorsImport implements ToModel, WithStartRow, WithValidation
{
    public function startRow(): int
    {
        return 6;
    }

    public function model(array $row)
    {
        $faculty = Faculty::where('code', trim($row[2]))->firstOrFail();
        
        return new Major([
            'code' => trim($row[0]),
            'name' => trim($row[1]),
            'faculty_id' => $faculty->id,
            'description' => trim($row[3] ?? '')
        ]);
    }

    public function rules(): array
    {
        return [
            '0' => 'required|unique:majors,code',
            '1' => 'required',
            '2' => 'required|exists:faculties,code',
            '3' => 'nullable'
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.required' => 'Mã ngành không được để trống',
            '0.unique' => 'Mã ngành đã tồn tại',
            '1.required' => 'Tên ngành không được để trống',
            '2.required' => 'Mã khoa không được để trống',
            '2.exists' => 'Mã khoa không tồn tại trong hệ thống'
        ];
    }
} 