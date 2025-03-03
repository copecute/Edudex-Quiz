<?php

namespace App\Imports;

use App\Models\ExamPeriod;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ExamPeriodsImport implements ToModel, WithStartRow, WithValidation
{
    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        // Chuyển đổi định dạng ngày tháng từ Excel
        $startTime = $this->transformDate($row[2]);
        $endTime = $this->transformDate($row[3]);

        return new ExamPeriod([
            'name' => $row[0],
            'description' => $row[1],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_active' => $row[4] === 'Hoạt động'
        ]);
    }

    public function rules(): array
    {
        return [
            '0' => 'required|string|max:255',
            '1' => 'nullable|string',
            '2' => 'required',
            '3' => 'required',
            '4' => 'required|in:Hoạt động,Khóa'
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.required' => 'Tên kỳ thi không được để trống',
            '0.max' => 'Tên kỳ thi không được vượt quá 255 ký tự',
            '2.required' => 'Thời gian bắt đầu không được để trống',
            '3.required' => 'Thời gian kết thúc không được để trống',
            '4.required' => 'Trạng thái không được để trống',
            '4.in' => 'Trạng thái chỉ nhận một trong hai giá trị: Hoạt động hoặc Khóa'
        ];
    }

    private function transformDate($value)
    {
        try {
            // Nếu là số (định dạng Excel)
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value);
            }
            
            // Nếu là chuỗi (định dạng dd/mm/yyyy H:i)
            return Carbon::createFromFormat('d/m/Y H:i', $value);
        } catch (\Exception $e) {
            return null;
        }
    }
} 