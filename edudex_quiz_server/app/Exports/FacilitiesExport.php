<?php

namespace App\Exports;

use App\Models\Facility;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FacilitiesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Facility::all();
    }

    public function headings(): array
    {
        return [
            'Mã cơ sở',
            'Tên cơ sở',
            'Địa chỉ',
            'Mô tả',
            'Trạng thái'
        ];
    }

    public function map($facility): array
    {
        return [
            $facility->code,
            $facility->name,
            $facility->address,
            $facility->description,
            $facility->is_active ? 'Hoạt động' : 'Khóa'
        ];
    }
} 