<?php

namespace App\Imports;

use App\Models\Account;
use App\Models\AccountInfo;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountsImport implements ToModel, WithStartRow, WithValidation
{
    public function startRow(): int
    {
        return 6; // Bắt đầu từ dòng 6
    }

    public function model(array $row)
    {
        // Tạo tài khoản mới
        $account = Account::create([
            'username' => trim($row[0]), // Cột A: Tên đăng nhập
            'email' => trim($row[1]),    // Cột B: Email
            'password' => Hash::make('123456'), // Mật khẩu mặc định
            'role' => $this->getRoleId(trim($row[2])), // Cột C: Vai trò
            'is_active' => trim($row[8]) === 'Hoạt động', // Cột I: Trạng thái
        ]);

        // Tạo thông tin tài khoản
        AccountInfo::create([
            'account_id' => $account->id,
            'fullName' => trim($row[3]), // Cột D: Họ tên
            'birthday' => $this->parseDate(trim($row[4])), // Cột E: Ngày sinh
            'gender' => strtolower(trim($row[5])) === 'nam' ? 1 : 0, // Cột F: Giới tính
            'phoneNumber' => trim($row[6]), // Cột G: Số điện thoại
            'address' => trim($row[7]), // Cột H: Địa chỉ
        ]);
        
        return $account;
    }

    public function rules(): array
    {
        return [
            '0' => 'required|unique:accounts,username', // Cột A - Tên đăng nhập
            '1' => 'required|email|unique:accounts,email', // Cột B - Email
            '2' => 'required', // Cột C - Vai trò
            '3' => 'required', // Cột D - Họ tên
            '4' => 'nullable|date', // Cột E - Ngày sinh
            '5' => 'required|in:Nam,Nữ', // Cột F - Giới tính
            '6' => 'required', // Cột G - Số điện thoại
            '7' => 'required', // Cột H - Địa chỉ
            '8' => 'required|in:Hoạt động,Khóa', // Cột I - Trạng thái
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.required' => 'Tên đăng nhập không được để trống',
            '0.unique' => 'Tên đăng nhập đã tồn tại',
            '1.required' => 'Email không được để trống',
            '1.email' => 'Email không hợp lệ',
            '1.unique' => 'Email đã tồn tại',
            '2.required' => 'Vai trò không được để trống',
            '3.required' => 'Họ tên không được để trống',
            '4.date' => 'Ngày sinh không hợp lệ',
            '5.required' => 'Giới tính không được để trống',
            '5.in' => 'Giới tính chỉ nhận một trong hai giá trị: Nam hoặc Nữ',
            '6.required' => 'Số điện thoại không được để trống',
            '7.required' => 'Địa chỉ không được để trống',
            '8.required' => 'Trạng thái không được để trống',
            '8.in' => 'Trạng thái chỉ nhận một trong hai giá trị: Hoạt động hoặc Khóa',
        ];
    }

    private function getRoleId($roleName)
    {
        return match(strtolower($roleName)) {
            'Cán bộ coi thi', 'cán bộ coi thi' => 0,
            'Giáo viên', 'Giáo viên' => 1,
            'Admin', 'admin' => 2,
            default => 0
        };
    }

    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        // Nếu là số (định dạng Excel)
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
        }

        try {
            // Thử parse các định dạng phổ biến
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            // Nếu không parse được thì trả về null
            return null;
        }
    }
}