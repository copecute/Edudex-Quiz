<?php

namespace App\Exports;

use App\Models\Room;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RoomsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Room::with('facility')->get();
    }

    public function headings(): array
    {
        return [
            'Mã phòng thi',
            'Tên phòng thi',
            'Mã cơ sở',
            'Tên cơ sở',
            'Mô tả',
            'Trạng thái'
        ];
    }

    public function map($room): array
    {
        return [
            $room->code,
            $room->name,
            $room->facility->code,
            $room->facility->name,
            $room->description,
            $room->is_active ? 'Hoạt động' : 'Khóa'
        ];
    }
} 