<?php

namespace App\Imports;

use App\Models\ExamPeriodRoom;
use App\Models\Room;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;

class ExamPeriodRoomsImport implements ToModel, WithStartRow, WithValidation
{
    protected $examPeriodId;

    public function __construct($examPeriodId)
    {
        $this->examPeriodId = $examPeriodId;
    }

    public function model(array $row)
    {
        $room = Room::where('code', $row[0])->first();

        return new ExamPeriodRoom([
            'exam_period_id' => $this->examPeriodId,
            'room_id' => $room->id
        ]);
    }

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            '0' => [
                'required',
                'exists:rooms,code',
                function($attribute, $value, $fail) {
                    // Kiểm tra xem phòng đã được phân công trong kỳ thi chưa
                    $exists = ExamPeriodRoom::whereHas('room', function($query) use ($value) {
                        $query->where('code', $value);
                    })->where('exam_period_id', $this->examPeriodId)->exists();

                    if ($exists) {
                        $fail('Phòng thi đã được phân công trong kỳ thi này.');
                    }
                }
            ]
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.required' => 'Mã phòng không được để trống',
            '0.exists' => 'Mã phòng không tồn tại trong hệ thống'
        ];
    }
} 