<?php

namespace App\Exports;

use App\Models\Major;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MajorsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Major::with('faculty')->get();
    }

    public function headings(): array
    {
        return [
            'Mã ngành',
            'Tên ngành',
            'Mã khoa',
            'Mô tả'
        ];
    }

    public function map($major): array
    {
        return [
            $major->code,
            $major->name,
            $major->faculty->code,
            $major->description
        ];
    }
} 