<?php

namespace App\Exports;

use App\Models\ExamPeriodProctor;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProctorsExport implements FromQuery, WithHeadings, WithMapping
{
    private $examPeriodId;

    public function __construct($examPeriodId)
    {
        $this->examPeriodId = $examPeriodId;
    }

    public function query()
    {
        return ExamPeriodProctor::query()
            ->where('exam_period_id', $this->examPeriodId)
            ->with(['account.accountInfo']);
    }

    public function headings(): array
    {
        return [
            'Username',
            'Họ và tên',
            'Email',
            'Số điện thoại'
        ];
    }

    public function map($proctor): array
    {
        return [
            $proctor->account->username,
            $proctor->account->accountInfo->full_name,
            $proctor->account->email,
            $proctor->account->accountInfo->phone
        ];
    }
} 