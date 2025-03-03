<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProctorsTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['username1'],
            ['username2']
        ];
    }

    public function headings(): array
    {
        return [
            'username'
        ];
    }
} 