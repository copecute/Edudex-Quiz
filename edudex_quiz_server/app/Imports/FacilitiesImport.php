<?php

namespace App\Imports;

use App\Models\Facility;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class FacilitiesImport implements ToModel, WithStartRow, WithValidation
{
    public function startRow(): int
    {
        return 6;
    }

    public function model(array $row)
    {
        return new Facility([
            'code' => $row[0],  // Cột A
            'name' => $row[1],  // Cột B
            'address' => $row[2],  // Cột C
            'description' => $row[3],  // Cột D
            'is_active' => $row[4] === 'Hoạt động'  // Cột E
        ]);
    }

    public function rules(): array
    {
        return [
            '0' => 'required|unique:facilities,code',  // Cột A - Mã cơ sở
            '1' => 'required',  // Cột B - Tên cơ sở
            '2' => 'required',  // Cột C - Địa chỉ
            '3' => 'nullable',  // Cột D - Mô tả
            '4' => 'required|in:Hoạt động,Khóa'  // Cột E - Trạng thái
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.required' => 'Mã cơ sở không được để trống',
            '0.unique' => 'Mã cơ sở đã tồn tại',
            '1.required' => 'Tên cơ sở không được để trống',
            '2.required' => 'Địa chỉ không được để trống',
            '4.required' => 'Trạng thái không được để trống',
            '4.in' => 'Trạng thái chỉ nhận một trong hai giá trị: Hoạt động hoặc Khóa'
        ];
    }
} 