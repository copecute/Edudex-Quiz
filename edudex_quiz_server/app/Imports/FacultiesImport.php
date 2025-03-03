<?php

namespace App\Imports;

use App\Models\Faculty;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class FacultiesImport implements ToModel, WithStartRow, WithValidation
{
    public function startRow(): int
    {
        return 6;
    }

    public function model(array $row)
    {
        return new Faculty([
            'code' => trim($row[0]),
            'name' => trim($row[1]),
            'description' => trim($row[2] ?? '')
        ]);
    }

    public function rules(): array
    {
        return [
            '0' => 'required|unique:faculties,code',
            '1' => 'required',
            '2' => 'nullable'
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.required' => 'Mã khoa không được để trống',
            '0.unique' => 'Mã khoa đã tồn tại',
            '1.required' => 'Tên khoa không được để trống'
        ];
    }
} 