<?php

namespace App\Exports;

use App\Models\Faculty;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FacultiesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Faculty::all();
    }

    public function headings(): array
    {
        return [
            'Mã khoa',
            'Tên khoa',
            'Mô tả'
        ];
    }

    public function map($faculty): array
    {
        return [
            $faculty->code,
            $faculty->name,
            $faculty->description
        ];
    }
} 