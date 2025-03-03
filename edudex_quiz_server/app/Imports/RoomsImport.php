<?php

namespace App\Imports;

use App\Models\Room;
use App\Models\Facility;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class RoomsImport implements ToModel, WithStartRow, WithValidation
{
    public function startRow(): int
    {
        return 6;
    }

    public function model(array $row)
    {
        $facility = Facility::where('code', $row[2])->firstOrFail();

        return new Room([
            'code' => $row[0],  // Cột A
            'name' => $row[1],  // Cột B
            'facility_id' => $facility->id,  // Lấy từ cột C (mã cơ sở)
            'description' => $row[3],  // Cột D
            'capacity' => $row[4],  // Thêm capacity
            'is_active' => $row[5] === 'Hoạt động'  // Cột E
        ]);
    }

    public function rules(): array
    {
        return [
            '0' => 'required|unique:rooms,code',  // Cột A - Mã phòng thi
            '1' => 'required',  // Cột B - Tên phòng thi
            '2' => 'required|exists:facilities,code',  // Cột C - Mã cơ sở
            '3' => 'nullable',  // Cột D - Mô tả
            '4' => 'required|integer|min:1',  // Validation cho capacity
            '5' => 'required|in:Hoạt động,Khóa'  // Cột E - Trạng thái
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.required' => 'Mã phòng thi không được để trống',
            '0.unique' => 'Mã phòng thi đã tồn tại',
            '1.required' => 'Tên phòng thi không được để trống',
            '2.required' => 'Mã cơ sở không được để trống',
            '2.exists' => 'Mã cơ sở không tồn tại trong hệ thống',
            '4.required' => 'Sức chứa không được để trống',
            '4.integer' => 'Sức chứa phải là số nguyên',
            '4.min' => 'Sức chứa phải lớn hơn 0',
            '5.required' => 'Trạng thái không được để trống',
            '5.in' => 'Trạng thái chỉ nhận một trong hai giá trị: Hoạt động hoặc Khóa'
        ];
    }
} 