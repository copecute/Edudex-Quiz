<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AccountsExport;
use App\Imports\AccountsImport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Exports\AccountsTemplateExport;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $query = Account::with('accountInfo');

        // Tìm kiếm
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('accountInfo', function($q) use ($search) {
                      $q->where('fullName', 'like', "%{$search}%")
                        ->orWhere('phoneNumber', 'like', "%{$search}%");
                  });
            });
        }

        // Lọc theo role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        $accounts = $query->paginate(10);
        return view('accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('accounts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:accounts',
            'email' => 'required|email|unique:accounts',
            'password' => 'required|min:6',
            'role' => 'required|in:0,1,2',
            'fullName' => 'required',
            'birthday' => 'nullable|date',
            'gender' => 'required|boolean',
            'phoneNumber' => 'required',
            'address' => 'required',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $account = Account::create([
                    'username' => $request->username,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => $request->role,
                ]);

                AccountInfo::create([
                    'account_id' => $account->id,
                    'fullName' => $request->fullName,
                    'birthday' => $request->birthday,
                    'gender' => $request->gender,
                    'phoneNumber' => $request->phoneNumber,
                    'address' => $request->address,
                ]);
            });
            return redirect()->route('accounts.index')
                ->with('success', 'Tạo tài khoản thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi tạo tài khoản!');
        }
    }

    public function edit(Account $account)
    {
        return view('accounts.edit', compact('account'));
    }

    public function update(Request $request, Account $account)
    {
        $request->validate([
            'username' => 'required|unique:accounts,username,'.$account->id,
            'email' => 'required|email|unique:accounts,email,'.$account->id,
            'password' => 'nullable|min:6',
            'role' => 'required|in:0,1,2',
            'fullName' => 'required',
            'birthday' => 'nullable|date',
            'gender' => 'required|boolean',
            'phoneNumber' => 'required',
            'address' => 'required',
        ]);

        try {
            DB::transaction(function () use ($request, $account) {
                $account->update([
                    'username' => $request->username,
                    'email' => $request->email,
                    'role' => $request->role,
                ]);

                if ($request->filled('password')) {
                    $account->update(['password' => Hash::make($request->password)]);
                }

                $account->accountInfo->update([
                    'fullName' => $request->fullName,
                    'birthday' => $request->birthday,
                    'gender' => $request->gender,
                    'phoneNumber' => $request->phoneNumber,
                    'address' => $request->address,
                ]);
            });
            return redirect()->route('accounts.index')
                ->with('success', 'Cập nhật tài khoản thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật tài khoản!');
        }
    }

    public function destroy(Account $account)
    {
        try {
            $account->delete();
            return redirect()->route('accounts.index')
                ->with('success', 'Xóa tài khoản thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi xóa tài khoản!');
        }
    }

    public function export()
    {
        return Excel::download(new AccountsExport, 'accounts.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ], [
            'file.required' => 'Vui lòng chọn file để import',
            'file.mimes' => 'File phải có định dạng xlsx hoặc xls'
        ]);

        try {
            Excel::import(new AccountsImport, $request->file('file'));
            return back()->with('success', 'Import dữ liệu thành công!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            
            foreach ($failures as $failure) {
                $errors[] = "Dòng {$failure->row()}: {$failure->errors()[0]}";
            }
            
            return back()
                ->with('error', 'Import thất bại!')
                ->with('import_errors', $errors);
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new AccountsTemplateExport, 'template_tai_khoan.xlsx');
    }

    public function importExportTools()
    {
        return view('accounts.tools');
    }

    public function toggleStatus(Account $account)
    {
        try {
            if (auth()->user()->role !== 2) { // 2 là role của admin
                return back()->with('error', 'Bạn không có quyền thực hiện chức năng này!');
            }

            if ($account->id === auth()->id()) {
                return back()->with('error', 'Bạn không thể khoá tài khoản của chính mình!');
            }

            $account->update([
                'is_active' => !$account->is_active
            ]);

            $message = $account->is_active ? 'Mở khoá tài khoản thành công!' : 'Khoá tài khoản thành công!';
            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi thay đổi trạng thái tài khoản!');
        }
    }
} 