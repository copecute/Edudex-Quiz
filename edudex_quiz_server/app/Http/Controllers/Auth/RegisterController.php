<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:accounts'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:accounts'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'fullName' => ['required', 'string', 'max:255'],
            'birthday' => ['nullable', 'date'],
            'gender' => ['required', 'boolean'],
            'phoneNumber' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request) {
            $account = Account::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 0, // CBCT by default
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

        return redirect()->route('login')
            ->with('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
    }
} 