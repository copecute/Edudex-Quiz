<?php

namespace App\Imports;

use App\Models\ExamPeriodProctor;
use App\Models\Account;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProctorsImport implements ToModel, WithHeadingRow, WithValidation
{
    private $examPeriodId;

    public function __construct($examPeriodId)
    {
        $this->examPeriodId = $examPeriodId;
    }

    public function model(array $row)
    {
        $account = Account::where('username', $row['username'])->first();
        
        if (!$account) {
            throw new \Exception("Không tìm thấy tài khoản với username: {$row['username']}");
        }

        return new ExamPeriodProctor([
            'exam_period_id' => $this->examPeriodId,
            'account_id' => $account->id
        ]);
    }

    public function rules(): array
    {
        return [
            'username' => 'required|exists:accounts,username'
        ];
    }

    public function customValidationMessages()
    {
        return [
            'username.required' => 'Username không được để trống',
            'username.exists' => 'Username không tồn tại trong hệ thống'
        ];
    }
} 