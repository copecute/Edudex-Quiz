<?php

namespace App\Exports;

use App\Models\Account;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AccountsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Account::with('accountInfo')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tên đăng nhập',
            'Email',
            'Vai trò',
            'Họ tên',
            'Ngày sinh',
            'Giới tính',
            'Số điện thoại',
            'Địa chỉ',
        ];
    }

    public function map($account): array
    {
        return [
            $account->id,
            $account->username,
            $account->email,
            $this->getRoleName($account->role),
            $account->accountInfo->fullName,
            $account->accountInfo->birthday,
            $account->accountInfo->gender ? 'Nam' : 'Nữ',
            $account->accountInfo->phoneNumber,
            $account->accountInfo->address,
        ];
    }

    private function getRoleName($role)
    {
        return match($role) {
            0 => 'CBCT',
            1 => 'Giáo viên',
            2 => 'Admin',
            default => 'Unknown'
        };
    }
} 